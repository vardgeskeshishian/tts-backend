<?php

namespace App\Enums;

use App\Events\UploadBulk\SFXUploadEvent;
use App\Events\UploadBulk\TrackUploadEvent;
use App\Events\UploadBulk\VideoEffectUploadEvent;
use App\Http\Resources\Any\SFX\TrackResource as SFXTrackResource;
use App\Http\Resources\Api\TrackResource;
use App\Http\Resources\VideoEffectResource;
use App\Models\Track;
use App\Models\VideoEffects\VideoEffect;
use App\Models\SFX\SFXTrack;
use GeneaLabs\LaravelModelCaching\CachedBuilder;
use Illuminate\Database\Eloquent\Builder;

enum TypeContentEnum: string
{
    case TRACK = 'music';

    case VIDEO_EFFECT = 'templates';

    case SFX = 'sfx';

    /**
     * @return Builder|CachedBuilder
     */
    public function getQuery(): Builder|CachedBuilder
    {
        return match ($this) {
            self::TRACK => Track::query(),
            self::VIDEO_EFFECT => VideoEffect::query(),
            self::SFX => SFXTrack::query(),
        };
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        return match ($this) {
            self::TRACK => Track::class,
            self::VIDEO_EFFECT => VideoEffect::class,
            self::SFX => SFXTrack::class,
        };
    }

    /**
     * @param string $class
     * @return TypeContentEnum
     */
    public static function getTypeContent(string $class): TypeContentEnum
    {
        return match ($class)
        {
            'track', 'music', Track::class => self::TRACK,
            'template', 'templates', VideoEffect::class => self::VIDEO_EFFECT,
            'sfx', SFXTrack::class => self::SFX,
        };
    }

    /**
     * @param string $class
     * @return string
     */
    public static function getUploadEvent(string $class): string
    {
        return match ($class)
        {
            'track', 'music', Track::class => TrackUploadEvent::class,
            'template', 'templates', VideoEffect::class => VideoEffectUploadEvent::class,
            'sfx', SFXTrack::class => SFXUploadEvent::class,
        };
    }

    /**
     * @param $content
     * @param string $class
     * @return VideoEffectResource|TrackResource|SFXTrackResource
     */
    public static function getResourseContent($content, string $class): VideoEffectResource|TrackResource|SFXTrackResource
    {
        return match ($class)
        {
            Track::class => new TrackResource($content),
            VideoEffect::class => new VideoEffectResource($content),
            SFXTrack::class => new SFXTrackResource($content),
        };
    }

    /**
     * @return string
     */
    public function getNameTypeFolder(): string
    {
        return match ($this)
        {
            self::TRACK => 'tracks',
            self::VIDEO_EFFECT => 'videoEffects',
            self::SFX => 'sfxs',
        };
    }

    /**
     * @return int
     */
    public function typeIdContent(): int
    {
        return match ($this)
        {
            self::TRACK, self::SFX => 3,
            self::VIDEO_EFFECT => 4,
        };
    }
}