<?php

namespace App\Orchid\Screens\SearchCustom;

use App\Exceptions\EmptySearchResult;
use App\Filters\TrackFilter;
use App\Filters\VideoEffectFilter;
use App\Filters\SFXTrackFilter;
use App\Orchid\Layouts\Search\SearchResultCustomLayout;
use App\Orchid\Listeners\Search\SearchQueryListener;
use App\Services\SearchStrategies\TrackSearch;
use App\Services\SearchStrategies\VideoEffectSearch;
use App\Services\SearchStrategies\SoundEffectSearch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Spatie\ResponseCache\Facades\ResponseCache;

class SearchCustomScreen extends Screen
{
    /**
     * @param Request $request
     * @return array
     * @throws EmptySearchResult
     */
    public function query(Request $request): array
    {
        $results = null;

        $sort = $request['sort'] ?? 'trending';
        $sort = $sort == 'new' ? 'created_at' : $sort;

        if($request->has('typeContent'))
        {
            switch ($request->input('typeContent')) {
                case 'track':
                    $filter = new TrackFilter($request);
                    $requestArray = $filter->getRequest();

                    $categories = [
                        'genre',
                        'mood',
                        'instrument',
                        'usageType',
                        'tag'
                    ];

                    $q = $request->input('q') ?? null;
                    $q_categories = '';
                    foreach ($categories as $category)
                    {
                        if (array_key_exists($category, $requestArray))
                            $q_categories .= $requestArray[$category];
                    }

                    if ($q_categories !== '')
                        $q .= $q_categories;

                    $trackSearch = resolve(TrackSearch::class);
                    $results = $trackSearch->searchCustomApi(
                        filter: $filter,
                        q: $q,
                        sort: $sort,
                        is_admin: true
                    );
                    break;
                case 'template':
                    $filter = new VideoEffectFilter($request);
                    $requestArray = $filter->getRequest();

                    $categories = [
                        'applications',
                        'application',
                        'plugins',
                        'resolutions',
                        'category',
                        'tag'
                    ];

                    $q = $request->input('q') ?? null;
                    $q_categories = '';
                    foreach ($categories as $category)
                    {
                        if (array_key_exists($category, $requestArray))
                            $q_categories .= $requestArray[$category];
                    }

                    if ($q_categories !== '')
                        $q .= $q_categories;

                    $videoEffectSearch = resolve(VideoEffectSearch::class);
                    $results = $videoEffectSearch->searchCustomApi(
                        filter: $filter,
                        q: $q,
                        sort: $sort,
                        is_admin: true
                    );
                    break;
                case 'sfx':
                    $filter = new SFXTrackFilter($request);
                    $requestArray = $filter->getRequest();

                    $categories = [
                        'category',
                        'tag'
                    ];

                    $q = $request->input('q') ?? null;
                    $q_categories = '';
                    foreach ($categories as $category)
                    {
                        if (array_key_exists($category, $requestArray))
                            $q_categories .= $requestArray[$category];
                    }

                    if ($q_categories !== '')
                        $q .= $q_categories;

                    $soundEffectSearch = resolve(SoundEffectSearch::class);
                    $results = $soundEffectSearch->searchCustomApi(
                        filter: $filter,
                        q: $q,
                        sort: $sort,
                        is_admin: true
                    );
                    break;
            }

            $results = new LengthAwarePaginator(
                $results['data'],
                $results['total'],
                $results['per_page'],
                $results['current_page'],
                [
                    'path' => Paginator::resolveCurrentPath(),
                    'pageName' => 'page',
                    'query' => request()->all(),
                ]
            );
        }

        return [
            'results' => $results
        ];
    }

    /**
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Search';
    }

    /**
     * @return string|null
     */
    public function description(): ?string
    {
        return '';
    }

    /**
     * @return iterable|null
     */
    public function permission(): ?iterable
    {
        return [
            'platform.systems.users',
        ];
    }

    /**
     * @return iterable
     */
    public function commandBar(): iterable
    {
        return [
            Link::make(__('Edit Coefficients'))
                ->icon('bs.pencil')
                ->route('platform.systems.edit.coefficients'),

            Link::make(__('Edit Coefficients Template'))
                ->icon('bs.pencil')
                ->route('platform.systems.edit.coefficients.template'),

            Button::make(__('Cache Clear'))
                ->icon('bs.trash3-fill')
                ->novalidate()
                ->method('clear'),
        ];
    }

    /**
     * @return iterable
     */
    public function layout(): iterable
    {
        $typeCategorySee = null;

        if(request()->has('typeContent')) {
            $typeCategorySee = [
                'track' => request()->input('typeContent') == 'track',
                'template' => request()->input('typeContent') == 'template',
                'sfx' => request()->input('typeContent') == 'sfx',
            ];
        }

        return [
            new SearchQueryListener(
                $typeCategorySee,
            ),

            Layout::block([
                SearchResultCustomLayout::class
            ])->vertical()
        ];
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function search(Request $request): RedirectResponse
    {

        $q = $request->input('q');

        return redirect()->route('platform.systems.search.content', [
            'q' => $q,
            'typeContent' => $request->input('typeContent'),
            'author' => $request->input('author'),
            'genre' => $request->input('genre'),
            'mood' => $request->input('mood'),
            'instrument' => $request->input('instrument'),
            'usageType' => $request->input('usageType'),
            'applications' => !is_null($request->input('applications')) ?
                implode(' ', $request->input('applications')) : null,
            'templateCategory' => $request->input('templateCategory'),
            'plugins' => $request->input('plugins'),
            'resolutions' => $request->input('resolutions'),
            'sfxCategory' => $request->input('sfxCategory'),
            'tag' => $request->input('tag'),
            'bpmMin' => $request->input('bpmMin'),
            'bpmMax' => $request->input('bpmMax'),
            'durationMix' => $request->input('durationMix'),
            'durationMax' => $request->input('durationMax'),
            'onlyPremium' => $request->input('onlyPremium'),
            'sort' => $request->input('sort'),
        ]);
    }

    /**
     * @return RedirectResponse
     */
    public function clear(): RedirectResponse
    {
        ResponseCache::clear();
        Cache::clear();
        Toast::info(__('Cache cleared'));
        return redirect()->back()->withInput();
    }
}