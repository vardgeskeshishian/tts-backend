<?php

namespace App\Traits;

use App\Models\Tags\CuratorPick;
use App\Models\Tags\Tag;
use App\Models\Tags\Mood;
use App\Models\Tags\Type;
use App\Models\Tags\Genre;
use App\Models\SFX\SFXTag;
use Illuminate\Support\Str;
use App\Models\Tags\Tagging;
use App\Models\Tags\Instrument;
use App\Models\SFX\SFXCategory;
use App\Services\TaggingService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

trait HasTags
{
    protected $tagTypes = [
        'tags' => Tag::class,
        'moods' => Mood::class,
        'genres' => Genre::class,
        'types' => Type::class,
        'instruments' => Instrument::class,
        'curator_picks' => CuratorPick::class,
        TaggingService::SFX_CATEGORY => SFXCategory::class,
        TaggingService::SFX_TAG => SFXTag::class,
    ];

    protected static function bootHasTags()
    {
        self::saved(function (Model $model) {
            self::onTagsChange($model);
        });
        self::created(function (Model $model) {
            self::onTagsChange($model);
        });
        self::updated(function (Model $model) {
            self::onTagsChange($model);
        });
    }

    /**
     * @param Model $model
     */
    public static function onTagsChange(Model $model)
    {
        $key = Str::slug($model->getMorphClass()) . ":tags:" . $model->id;

        Cache::forget($key);
    }


    public function getTagsOfType(string $type)
    {
        if (!in_array($type, $this->availableTagTypes)) {
            return [];
        }

        return $this->getAllTags()[$type];
    }

    /**
     * @return array
     */
    public function getAllTags()
    {
        $key = Str::slug($this->getMorphClass()) . ":tags:" . $this->id;

        return Cache::rememberForever($key, function () {
            $tags = [];

            foreach ($this->tagTypes as $key => $classString) {
                if (!in_array($key, $this->availableTagTypes)) {
                    continue;
                }

                $tags[$key] = $this->findTags($classString)->get()->toArray();
            }

            return $tags;
        });
    }

    public function getSlugTags()
    {
        $tags = [];

        foreach ($this->tagTypes as $key => $classString) {
            if (!in_array($key, $this->availableTagTypes)) {
                continue;
            }

            $tags[$key] = $this->findTags($classString)->get()->map(function ($tag) {
                return Str::lower($tag->name);
            })->toArray();
        }

        return $tags;
    }

    public function genres()
    {
        return $this->findTags($this->tagTypes['genres']);
    }

    public function moods()
    {
        return $this->findTags($this->tagTypes['moods']);
    }

    public function instruments()
    {
        return $this->findTags($this->tagTypes['instruments']);
    }

    public function types()
    {
        return $this->findTags($this->tagTypes['types']);
    }

    public function tags()
    {
        return $this->findTags($this->tagTypes['tags']);
    }

    public function curatorPicks()
    {
        return $this->findTags($this->tagTypes['curator_picks']);
    }

    public function sfxCategories()
    {
        return $this->findTags($this->tagTypes[TaggingService::SFX_CATEGORY]);
    }

    public function sfxTags()
    {
        return $this->findTags($this->tagTypes[TaggingService::SFX_TAG]);
    }

    /**
     * @param string $type
     *
     * @return mixed
     */
    public function findTags(string $type)
    {
        return $this
            ->hasManyThrough($type, Tagging::class, 'object_id', 'id', null, 'tag_id')
            ->where('object_type', static::class)
            ->where('tag_type', $type);
    }
}
