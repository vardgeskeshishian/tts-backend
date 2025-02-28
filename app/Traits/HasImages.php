<?php

namespace App\Traits;

use Carbon\Carbon;
use App\Models\Images;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

trait HasImages
{
    protected $types = [
        'background',
        'thumbnail',
        'icon',
    ];

    protected static function bootHasImages()
    {
        self::saved(function (Model $model) {
            static::onImagesChange($model);
        });
        self::created(function (Model $model) {
            static::onImagesChange($model);
        });
        self::updated(function (Model $model) {
            static::onImagesChange($model);
        });
    }

    /**
     * @param Model $model
     */
    public static function onImagesChange(Model $model)
    {
    }

    public function getImages($type = null)
    {
        $key = Str::slug($this->getMorphClass()) . ":images:" . $this->id;

        $imgs = $type ? $this->{$type} : $this->images;

        if (!$imgs || count($imgs) === 0) {
            return [];
        }

        return Cache::remember(
            $key,
            Carbon::now()->addHour(),
            fn () => $imgs->mapWithKeys(fn ($i) => [$i->type_key => $i->url])
        );
    }

    public function getSingleImageOfType($type)
    {
        $images = $this->getImages($type);

        return $images[$type] ?? null;
    }

    public function images(string $ofType = null)
    {
        $query = $this->hasMany(Images::class, 'type_id')
            ->where('type', $this->getMorphClass());

        if ($ofType) {
            $query = $query->where('type_key', $ofType);
        }

        return $query;
    }

    public function background()
    {
        return $this->images('background');
    }

    public function thumbnail()
    {
        return $this->images('thumbnail');
    }
}
