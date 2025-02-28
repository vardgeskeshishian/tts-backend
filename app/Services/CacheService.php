<?php


namespace App\Services;

use App\Constants\CacheEnv;
use App\Contracts\CacheServiceContract;
use App\Models\License;
use App\Services\Cache\CacheServiceMapper;
use Cache;

class CacheService implements CacheServiceContract
{
    private CacheServiceMapper $mapper;

    /**
     * @param CacheServiceMapper $mapper
     */
    public function __construct(CacheServiceMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    public function getFreeLicenses()
    {
        if (Cache::has(CacheEnv::CACHE_LICENSES_NON_FREE_KEY)) {
            return Cache::get(CacheEnv::CACHE_LICENSES_NON_FREE_KEY);
        }

        return $this->warmUpCache(CacheEnv::CACHE_LICENSES_NON_FREE_KEY);
    }

    public function getUsdRate()
    {
        if (Cache::has(CacheEnv::CACHE_USD_RATE_KEY)) {
            return Cache::get(CacheEnv::CACHE_USD_RATE_KEY);
        }

        return $this->warmUpCache(CacheEnv::CACHE_USD_RATE_KEY);
    }

    public function isOnlineKassaEnabled(): bool
    {
        return !Cache::has(CacheEnv::DISABLE_ONLINE_KASSA);
    }

    public function warmUpCache(string $cacheKey)
    {
        Cache::forget($cacheKey);

        switch ($cacheKey) {
            case CacheEnv::CACHE_LICENSES_NON_FREE_KEY:
                $value = License::with('standard')->whereHas('standard', function ($q) {
                    $q->where('price', '!=', 0);
                })->where('payment_type', 'standard')->get();

                break;
            case CacheEnv::CACHE_USD_RATE_KEY:
                $rates = json_decode(file_get_contents('https://www.cbr-xml-daily.ru/daily_json.js'));
                $value = $rates->Valute->USD->Value;
                break;
        }

        Cache::forever($cacheKey, $value);

        return $value;
    }

    public function getModel($model, $modelId)
    {
        return Cache::remember($this->mapper->buildKey($model, $modelId), 15, fn () => resolve($model)::find($modelId));
    }

    public function getModelsById($model, $ids)
    {
        $key = implode("-", $ids);

        return Cache::remember(
            $this->mapper->buildKey($model, $key),
            15,
            fn () => resolve($model)::findMany($ids)
        );
    }

    public function getModelByName($model, $name)
    {
        $name = $this->prepareValue($name);
        return Cache::remember(
            $this->mapper->buildKey($model, $name),
            15,
            fn () => resolve($model)::where('name', $name)->first()
        );
    }

    public function getModelsByName($model, $names)
    {
        $names = $this->prepareValue($names);
        $key = implode("-", $names);

        return Cache::remember(
            $this->mapper->buildKey($model, $key),
            15,
            fn () => resolve($model)::whereIn('name', $names)->get()
        );
    }


    public function getModelsByKey($model, $key, $names)
    {
        $names = $this->prepareValue($names);
        $cacheKey = $key . implode("-", $names);

        return Cache::remember(
            $this->mapper->buildKey($model, $cacheKey),
            15,
            fn () => resolve($model)::whereIn($key, $names)->get()
        );
    }

    private function prepareValue($value)
    {
        if (!is_array($value)) {
            return trim($value);
        }

        return array_map(fn ($item) => trim($item), $value);
    }
}
