<?php

namespace App\Services\SearchStrategies;

use App\Exceptions\EmptySearchResult;
use App\Models\SFX\SFXTrack;
use App\Http\Resources\Any\SFX\TrackSfxSearchResource;
use App\Filters\SFXTrackFilter;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

class SoundEffectSearch extends AbstractSearch
{
    protected string $resourseClass = TrackSfxSearchResource::class;

    /**
     * @param SFXTrackFilter $filter
     * @param string|null $q
     * @param string $sort
     * @param bool $is_admin
     * @return array
     * @throws EmptySearchResult
     */
    public function searchCustomApi(
        SFXTrackFilter $filter,
        ?string $q = null,
        string $sort = 'trending',
        bool $is_admin = false,
    ): array
    {
        if (($q || $is_admin) && $sort == 'trending')
        {
            $sfx = SFXTrack::filterCategories($filter);

            if(!$sfx->count() && !$is_admin){
                throw new EmptySearchResult('Empty result search', 404);
            }
            $sfx = $sfx->filter($filter);
            info($sfx->count());

            $this->preprocessing(
                $splitWords,
                $coefficients,
                $sfx,
                $countDownloadsAll,
                SFXTrack::class,
                $sort, $q
            );

            if ($q) {
                $tracksIds = collect(DB::select("select track_elastics.track_id
                from track_elastics join sfx_tracks on track_elastics.track_id = sfx_tracks.id
                where MATCH(track_elastics.text) AGAINST(?) > 0 AND track_elastics.track_type = ?
                AND sfx_tracks.deleted_at is null",
                    [implode(' ', $splitWords), SFXTrack::class]))->pluck('track_id');
                $sfx = $sfx->whereIn('id', $tracksIds);
            }

            $sfx = $sfx->get()->map(
                function ($sfx) use (
                    $splitWords,
                    $coefficients,
                    $countDownloadsAll,
                    $is_admin,
                    $sort,
                    $q
                ) {
                    $countDownloadsFree = $sfx->user_downloads_free_count;
                    $countDownloadsSubs = $sfx->user_downloads_subs_count;
                    $text = $sfx->mix?->text ?? '';

                    if ($sort == 'trending') {
                        $sfx->calculateCoefficient($text, $splitWords, $countDownloadsFree,
                            $countDownloadsSubs, $countDownloadsAll, $coefficients->toArray());
                    }

                    if (!$is_admin) {
                        $sfx->setAttribute('count_downloads_free', $countDownloadsFree);
                        $sfx->setAttribute('count_downloads_subs', $countDownloadsSubs);
                    }

                    $sfx->setAttribute('product_type', 'sfx_track');
                    $sfx->setAttribute('downloads', $sort == 'trending' ?
                        $countDownloadsFree + $countDownloadsFree : $sfx->user_downloads_count);
                    $sfx->setAttribute('downloads_sum_by_period', $countDownloadsAll);
                    $sfx->setAttribute('cloud', $text);

                    return $sfx;
                });

            return $this->newSearchPaginator($sfx, $sort, $is_admin);
        } else {
            return $this->getDataWithout($filter, $sort);
        }
    }

    /**
     * @param SFXTrackFilter $filter
     * @param string $sort
     * @return array
     * @throws EmptySearchResult
     */
    private function getDataWithout(SFXTrackFilter $filter, string $sort): array
    {
        $page = request('page') ?: 1;
        $perPage = request('perpage', $this->perPage);

        $sfxs = SFXTrack::filterCategories($filter);
        if($sfxs->count() == 0){
            throw new EmptySearchResult('Empty result search', 404);
        }

        $sfxs = $sfxs->filter($filter)
            ->join('sfx_coefficcients', 'sfx_tracks.id', '=', 'sfx_coefficcients.sfx_id')
            ->select('sfx_tracks.*', 'sfx_coefficcients.'.$sort)
            ->orderByDesc('sfx_coefficcients.'.$sort)
            ->orderByDesc('sfx_coefficcients.created_at')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        $countTracks = SFXTrack::filter($filter)->count();
        $lastPage = ceil($countTracks / $perPage);
        $path = Paginator::resolveCurrentPath();

        $query = Paginator::resolveQueryString();
        array_key_exists('page', $query) ? : $query['page'] = 1;
        $query = implode('&', array_map(function ($key, $value) {
            return $key.'='.$value;
        }, array_keys($query), $query));

        $firstPageUrl = str_replace(['&page='.$page, '?page='.$page], ['&page=1', '?page=1'], $path.'?'.$query);
        $lastPageUrl = str_replace(['&page='.$page, '?page='.$page], ['&page='.$lastPage, '?page='.$lastPage], $path.'?'.$query);
        $nextPageUrl = $page == $lastPage ? null :
            str_replace(['&page='.$page, '?page='.$page], ['&page='.($page + 1), '?page='.($page + 1)], $path.'?'.$query);
        $prevPageUrl = $page == 1 ? null :
            str_replace(['&page='.$page, '?page='.$page], ['&page='.($page - 1), '?page='.($page - 1)], $path.'?'.$query);
        $currentPageUrl = $path.'?'.$query;

        return [
            'current_page' => (int)$page,
            'data' => TrackSfxSearchResource::collection($sfxs),
            'first_page_url' => $firstPageUrl,
            'from' => 1,
            'last_page' => $lastPage,
            'last_page_url' => $lastPageUrl,
            'next_page_url' => $nextPageUrl,
            'path' => $currentPageUrl,
            'per_page' => $perPage,
            'prev_page_url' => $prevPageUrl,
            'total' => $countTracks
        ];
    }
}
