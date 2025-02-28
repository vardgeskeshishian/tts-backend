<?php

namespace App\Services\SearchStrategies;

use App\Exceptions\EmptySearchResult;
use App\Filters\VideoEffectFilter;
use App\Http\Resources\VideoEffectSearchResource;
use App\Models\VideoEffects\VideoEffect;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

class VideoEffectSearch extends AbstractSearch
{
    protected string $resourseClass = VideoEffectSearchResource::class;

    /**
     * @param VideoEffectFilter $filter
     * @param string|null $q
     * @param string $sort
     * @param bool $is_admin
     * @return array
     * @throws EmptySearchResult
     */
    public function searchCustomApi(
        VideoEffectFilter $filter,
        ?string $q = null,
        string $sort = 'trending',
        bool $is_admin = false,
    ): array
    {
        if (($q || $is_admin) && $sort == 'trending')
        {
            $videoEffects = VideoEffect::filter($filter);

            if(!$videoEffects->count() && !$is_admin){
                throw new EmptySearchResult('Empty result search', 404);
            }
            info($videoEffects->count());

            $this->preprocessing(
                $splitWords,
                $coefficients,
                $videoEffects,
                $countDownloadsAll,
                VideoEffect::class,
                $sort, $q
            );

            if ($q)
            {
                $videoEffectsIds = collect(DB::select("select track_elastics.track_id
                    from track_elastics join video_effects on track_elastics.track_id = video_effects.id
                    where MATCH(track_elastics.text) AGAINST(?) > 0 AND track_elastics.track_type = ?
                    AND video_effects.deleted_at is null",
                    [implode(' ', $splitWords), VideoEffect::class]))->pluck('track_id');

                $videoEffects = $videoEffects->whereIn('id', $videoEffectsIds);
            }

            $videoEffects = $videoEffects->get()->map(
                function ($videoEffect) use (
                    $splitWords,
                    $coefficients,
                    $countDownloadsAll,
                    $is_admin,
                    $sort,
                    $q
                ) {
                    $countDownloadsFree = $videoEffect->user_downloads_free_count;
                    $countDownloadsSubs = $videoEffect->user_downloads_subs_count;
                    $text = $videoEffect->mix?->text ?? '';

                    if ($sort == 'trending') {
                        $videoEffect->calculateCoefficient($text, $splitWords, $countDownloadsFree,
                            $countDownloadsSubs, $countDownloadsAll, $coefficients->toArray());
                    }

                    if (!$is_admin) {
                        $videoEffect->setAttribute('count_downloads_free', $countDownloadsFree);
                        $videoEffect->setAttribute('count_downloads_subs', $countDownloadsSubs);
                    }

                    $videoEffect->setAttribute('product_type', 'video_effects');
                    $videoEffect->setAttribute('downloads', $sort == 'trending' ?
                        $countDownloadsFree + $countDownloadsFree : $videoEffect->user_downloads_count);
                    $videoEffect->setAttribute('downloads_sum_by_period', $countDownloadsAll);
                    $videoEffect->setAttribute('cloud', $text);

                    return $videoEffect;
                });

            return $this->newSearchPaginator($videoEffects, $sort, $is_admin);
        } else {
            return $this->getDataWithout($filter, $sort);
        }
    }

    /**
     * @param VideoEffectFilter $filter
     * @param string $sort
     * @return array
     * @throws EmptySearchResult
     */
    private function getDataWithout(VideoEffectFilter $filter, string $sort): array
    {
        $page = request('page') ?: 1;
        $perPage = request('perpage', $this->perPage);

        $videoEffects = VideoEffect::filter($filter)
            ->join('video_coefficcients', 'video_effects.id', '=', 'video_coefficcients.video_effect_id')
            ->select('video_effects.*', 'video_coefficcients.'.$sort)
            ->orderByDesc('video_coefficcients.'.$sort)
            ->orderByDesc('video_coefficcients.created_at')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        $countTracks = VideoEffect::filter($filter)->count();

        if($countTracks == 0){
            throw new EmptySearchResult('Empty result search', 404);
        }

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
            'data' => VideoEffectSearchResource::collection($videoEffects),
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
