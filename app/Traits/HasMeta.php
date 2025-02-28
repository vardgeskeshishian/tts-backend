<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Repositories\MetaRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

trait HasMeta
{
    protected static function bootHasMeta()
    {
        self::created(function (Model $model) {
            self::onMetaChange($model);
        });
        self::updated(function (Model $model) {
            self::onMetaChange($model);
        });
    }

    public static function onMetaChange(Model $model)
    {
        $key = Str::slug($model->getMorphClass()) . ":meta:" . $model->id;

        Cache::forget($key);
    }

    public function getMeta()
    {
        /**
         * @var $metaRepository MetaRepository
         */
        $metaRepository = resolve(MetaRepository::class);

        $key = Str::slug($this->getMorphClass()) . ":meta:" . $this->id;

        return Cache::remember($key, Carbon::now()->addHour(), function () use ($metaRepository) {
            return $metaRepository->getForEntity($this->getMorphClass(), $this->id);
        });
    }
}
