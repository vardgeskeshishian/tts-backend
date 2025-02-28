<?php


namespace App\Services\SearchStrategies;

use App\Models\Coefficient;
use App\Models\UserDownloads;
use cijic\phpMorphy\Morphy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

abstract class AbstractSearch
{
    protected string $resourseClass;
    protected string $query = '';
    protected $coefficients;
    protected int $perPage = 10;

    public function __construct(
        protected Morphy $morphy
    )
    {}

    protected function getSplitWords(string $wordsException = null): array
    {
        if ($wordsException) {
            $wordsException = explode(', ', Str::lower($wordsException));

            $this->query = str_replace($wordsException, '', Str::lower($this->query));
        }

        $words = mb_eregi_replace( '[^a-zA-Z]', ' ', trim($this->query));

        $partsOfSpeechText = $this->morphy->getPartOfSpeech(explode(' ', Str::upper($words)));
        foreach ($partsOfSpeechText as $key => $arrayPart)
        {
            if (is_array($arrayPart)) {
                $arrayPart = array_diff($arrayPart, ['PREP', 'ARTICLE', 'CONJ']);
                if (count($arrayPart) == 0)
                    $words = str_replace(' '.Str::lower($key).' ', ' ', $words);
            }
        }

        $splitWords = [];
        $text = explode(' ', $words);

        foreach ($text as $word) {
            if ($word === '')
                continue;

            $len = strlen($word);

            if ($len <= 4) {
                $splitWords[] = $word;
            }

            if ($len >= 5 && $len < 7) {
                $splitWords[] = substr($word, 0, 4);
            }

            if ($len >= 7) {
                $splitWords[] = substr($word, 0, 5);
            }
        }

        return $splitWords;
    }

    /**
     * @return mixed
     */
    public function getCoefficients(): mixed
    {
        return Cache::remember('search:coefficients', Carbon::now()->addSeconds(5), function () {
            return Coefficient::select(['short_name', 'coefficient'])
                ->get()
                ->mapWithKeys(fn($i) => [$i->short_name => $i->coefficient]);
        });
    }

    /**
     * @param $splitWords
     * @param $coefficients
     * @param $builder
     * @param $countDownloadsAll
     * @param $class
     * @param string $sort
     * @param string|null $q
     * @return void
     */
    protected function preprocessing(
        &$splitWords,
        &$coefficients,
        &$builder,
        &$countDownloadsAll,
        $class,
        string $sort = 'trending',
        ?string $q = null,
    ): void
    {
        if($q) {
            $this->query = strtolower($q);
        }

        $coefficients = $this->getCoefficients();

        $splitWords = $this->getSplitWords($coefficients['words']);

        if ($sort == 'trending') {
            $builder = $builder->withCount(['userDownloads as user_downloads_free_count' => function($query) use ($coefficients) {
                $query->where('created_at', '>=', Carbon::now()->subDays($coefficients['period_demand']))
                    ->where('license_id', 5)
                    ->where(function($query) {
                        $query->orWhereNotNull('billing_product_id')
                            ->orWhere(function ($query) {
                                $query->whereNull('billing_product_id')->where('type', '!=', 'preview-download');
                            });
                    })->whereHas('user', function ($query) {
                        $query->where('email', 'not like', 'paulcarvine%')
                            ->where('email', 'not like', 'x-guitar%')
                            ->where('email', 'not like', 'aleksnc%')
                            ->where('email', 'not like', '45rock%')
                            ->where('email', 'not like', 'domosy%')
                            ->where('email', 'not like', 'pavelyu%')
                            ->where('email', 'not like', 'tdostu%')
                            ->where('email', 'not like', 'notbeforeant%')
                            ->where('email', 'not like', 'lobanov%');
                    });
            }])->withCount(['userDownloads as user_downloads_subs_count' => function($query) use ($coefficients) {
                $query->where('created_at', '>=', Carbon::now()->subDays($coefficients['period_demand']))
                    ->whereIn('license_id', [12, 13])
                    ->where(function($query) {
                        $query->orWhereNotNull('billing_product_id')
                            ->orWhere(function ($query) {
                                $query->whereNull('billing_product_id')->where('type', '!=', 'preview-download');
                            });
                    })->whereHas('user', function ($query) {
                        $query->where('email', 'not like', 'paulcarvine%')
                            ->where('email', 'not like', 'x-guitar%')
                            ->where('email', 'not like', 'aleksnc%')
                            ->where('email', 'not like', '45rock%')
                            ->where('email', 'not like', 'domosy%')
                            ->where('email', 'not like', 'pavelyu%')
                            ->where('email', 'not like', 'tdostu%')
                            ->where('email', 'not like', 'notbeforeant%')
                            ->where('email', 'not like', 'lobanov%');
                    });
            }]);

            $countDownloadsAll = UserDownloads::where('created_at', '>=', Carbon::now()->subDays($coefficients['period_demand']))
                ->where('class', $class)->where(function($query) {
                    $query->orWhereNotNull('billing_product_id')
                        ->orWhere(function ($query) {
                            $query->whereNull('billing_product_id')->where('type', '!=', 'preview-download');
                        });
                })->whereHas('user', function ($query) {
                    $query->where('email', 'not like', 'paulcarvine%')
                        ->where('email', 'not like', 'x-guitar%')
                        ->where('email', 'not like', 'aleksnc%')
                        ->where('email', 'not like', '45rock%')
                        ->where('email', 'not like', 'domosy%')
                        ->where('email', 'not like', 'pavelyu%')
                        ->where('email', 'not like', 'tdostu%')
                        ->where('email', 'not like', 'notbeforeant%')
                        ->where('email', 'not like', 'lobanov%');
                })->count();
        } else {
            $builder = $builder->withCount('userDownloads');

            $countDownloadsAll = UserDownloads::where('class', $class)->where(function($query) {
                    $query->orWhereNotNull('billing_product_id')
                        ->orWhere(function ($query) {
                            $query->whereNull('billing_product_id')->where('type', '!=', 'preview-download');
                        });
                })->whereHas('user', function ($query) {
                    $query->where('email', 'not like', 'paulcarvine%')
                        ->where('email', 'not like', 'x-guitar%')
                        ->where('email', 'not like', 'aleksnc%')
                        ->where('email', 'not like', '45rock%')
                        ->where('email', 'not like', 'domosy%')
                        ->where('email', 'not like', 'pavelyu%')
                        ->where('email', 'not like', 'tdostu%')
                        ->where('email', 'not like', 'notbeforeant%')
                        ->where('email', 'not like', 'lobanov%');
                })->count();
        }
    }

    /**
     * @param \Illuminate\Support\Collection $items
     * @param string $sort
     * @param bool $is_admin
     * @return array
     */
    protected function newSearchPaginator(
        \Illuminate\Support\Collection $items,
        string $sort = 'trending',
        bool $is_admin = false
    ): array
    {
        if ($sort !== 'created_at' )
        {
            $collectionGreaterZero = $items->filter(function ($value) use ($sort) {
                return $value->{$sort} > 0;
            });
            $collectionEqualsZero = $items->diff($collectionGreaterZero);
            $sortCollection = $collectionGreaterZero->sortByDesc([$sort, 'created_at'])
                ->merge($collectionEqualsZero);
        } else {
            $sortCollection = $items->sortByDesc($sort);
        }

        $page = request('page') ?: 1;
        $perPage = request('perpage', $this->perPage);
        $lastPage = ceil($items->count() / $perPage);
        $forPage = $sortCollection->forPage($page, $perPage)->values();
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
            'data' => $is_admin ? $forPage : $forPage->map(function($track) {
                return new $this->resourseClass($track);
            }),
            'first_page_url' => $firstPageUrl,
            'from' => 1,
            'last_page' => $lastPage,
            'last_page_url' => $lastPageUrl,
            'next_page_url' => $nextPageUrl,
            'path' => $currentPageUrl,
            'per_page' => $perPage,
            'prev_page_url' => $prevPageUrl,
            'total' => $items->count()
        ];
    }
}
