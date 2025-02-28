<?php

namespace App\Services;

use App\Models\Tags\CuratorPick;
use App\Models\Tags\Genre;
use App\Models\Tags\Instrument;
use App\Models\Tags\Mood;
use App\Models\Tags\Type;
use App\Models\Structure\TemplateMeta;
use App\Models\VideoEffects\VideoEffectApplication;
use App\Models\VideoEffects\VideoEffectCategory;
use App\Models\SFX\SFXCategory;
use App\Http\Resources\Any\AbstractTagResource;
use App\Http\Resources\VideoEffectApplicationResource;
use App\Http\Resources\VideoEffectCategoryResource;

class CategoryService
{
    public function __construct()
    {
        $this->templates = TemplateMeta::get();
    }

    public function query(string $class)
    {
        return match($class) {
            CuratorPick::class => CuratorPick::query(),
            Genre::class => Genre::query(),
            Mood::class => Mood::query(),
            Instrument::class => Instrument::query(),
            Type::class => Type::query(),
            VideoEffectCategory::class => VideoEffectCategory::query(),
            VideoEffectApplication::class => VideoEffectApplication::query(),
            SFXCategory::class => SFXCategory::query()
        };
    }

    public function getResource(string $class, $item)
    {
        return match($class) {
            CuratorPick::class => new AbstractTagResource($item, $this->templates->where('type', CuratorPick::class)->values()->first()),
            Genre::class => new AbstractTagResource($item, $this->templates->where('type', Genre::class)->values()->first()),
            Mood::class => new AbstractTagResource($item, $this->templates->where('type', Mood::class)->values()->first()),
            Instrument::class => new AbstractTagResource($item, $this->templates->where('type', Instrument::class)->values()->first()),
            Type::class => new AbstractTagResource($item, $this->templates->where('type', Type::class)->values()->first()),
            VideoEffectCategory::class => new VideoEffectCategoryResource($item, $this->templates->where('type', VideoEffectCategory::class)->values()->first()),
            VideoEffectApplication::class => new VideoEffectApplicationResource($item, $this->templates->where('type', VideoEffectApplication::class)->values()->first()),
            SFXCategory::class => new AbstractTagResource($item, $this->templates->where('type', SFXCategory::class)->values()->first())
        };
    }

    public function getTypeCategory(string $type)
    {
        return match($type)
        {
            'music' => 'music',
            'video' => 'templates',
            'sfx' => 'sfx'
        };
    }

    public function getSlugCategory($class)
    {
        return match($class) {
            CuratorPick::class => 'curator-picks',
            Genre::class => 'genres',
            Mood::class => 'moods',
            Instrument::class => 'instruments',
            Type::class => 'usage-types',
            VideoEffectCategory::class => 'categories',
            VideoEffectApplication::class => 'applications',
            SFXCategory::class => 'categories'
        };
    }
}