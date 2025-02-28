<?php

namespace App\Services\SearchStrategies;

use App\Exceptions\EmptySearchResult;
use App\Filters\TrackFilter;
use App\Http\Resources\Any\Collection\TrackSearchResource;
use App\Models\Track;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

class TrackSearch extends AbstractSearch
{
    protected string $resourseClass = TrackSearchResource::class;

    /**
     * @param TrackFilter $filter
     * @param string|null $q
     * @param string $sort
     * @param bool $is_admin
     * @return array
     * @throws EmptySearchResult
     */
    public function searchCustomApi(
        TrackFilter $filter,
        ?string $q = null,
        string $sort = 'trending',
        bool $is_admin = false,
    ): array
    {
        if (($q || $is_admin) && $sort == 'trending') {
            $tracks = Track::filterCategories($filter)->where('hidden', false);

            if($tracks->count() == 0 && !$is_admin){
                throw new EmptySearchResult('Empty result search', 404);
            }
            $tracks = $tracks->filter($filter);
            info($tracks->count());

            $this->preprocessing(
                $splitWords,
                $coefficients,
                $tracks,
                $countDownloadsAll,
                Track::class,
                $sort, $q
            );

            if ($q) {
                $tracksIds = collect(DB::select("select track_elastics.track_id
                from track_elastics join tracks on track_elastics.track_id = tracks.id
                where MATCH(track_elastics.text) AGAINST(?) > 0 AND track_elastics.track_type = ?
                AND tracks.deleted_at is null",
                    [implode(' ', $splitWords), Track::class]))->pluck('track_id');

                $tracks = $tracks->whereIn('id', $tracksIds);
            }

            $tracks = $tracks->get()->map(
                function ($track) use (
                    $splitWords,
                    $coefficients,
                    $countDownloadsAll,
                    $is_admin,
                    $sort,
                    $q
                ) {
                    $countDownloadsFree = $track->user_downloads_free_count;
                    $countDownloadsSubs = $track->user_downloads_subs_count;
                    $text = $track->mix?->text ?? '';

                    if ($sort == 'trending') {
                        $track->calculateCoefficient($text, $splitWords, $countDownloadsFree, $countDownloadsSubs,
                            $countDownloadsAll, $coefficients->toArray());
                    }

                    $track->setAttribute('count_downloads_free', $countDownloadsFree);
                    $track->setAttribute('count_downloads_subs', $countDownloadsSubs);

                    $track->setAttribute('product_type', 'track');
                    $track->setAttribute('downloads', $sort == 'trending' ?
                        $countDownloadsFree + $countDownloadsSubs : $track->user_downloads_count);
                    $track->setAttribute('downloads_sum_by_period', $countDownloadsAll);
                    $track->setAttribute('cloud', $text);
                    return $track;
                });

            return $this->newSearchPaginator($tracks, $sort, $is_admin);
        } else {
            return $this->getDataWithout($filter, $sort);
        }
    }

    /**
     * @param TrackFilter $filter
     * @param string $sort
     * @return array
     * @throws EmptySearchResult
     */
    private function getDataWithout(TrackFilter $filter, string $sort): array
    {
        $page = request('page') ?: 1;
        $perPage = request('perpage', $this->perPage);

        $tracks = Track::filterCategories($filter)->where('hidden', false);
        if($tracks->count() == 0){
            throw new EmptySearchResult('Empty result search', 404);
        }

        $tracks = $tracks->filter($filter)
            ->where('hidden', false)
            ->join('tracks_coefficients', 'tracks.id', '=', 'tracks_coefficients.track_id')
            ->select(['tracks.*', 'tracks_coefficients.'.$sort])
            ->orderByDesc('tracks_coefficients.'.$sort)
            ->orderByDesc('tracks_coefficients.created_at')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        info($tracks->count());

        $countTracks = Track::filterCategories($filter)
            ->filter($filter)->where('hidden', false)->count();

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
            'data' => TrackSearchResource::collection($tracks),
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
