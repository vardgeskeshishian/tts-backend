<?php

namespace App\Orchid\Layouts\VideoEffect;

use AllowDynamicProperties;
use App\Models\VideoEffects\VideoEffectTag;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

#[AllowDynamicProperties]
class VideoEffectTagLayout extends Rows
{
    public function __construct()
    {
        $this->videoEffectTag = Cache::remember('tags:video-effect-tags', Carbon::now()->addDay(), function () {
            return VideoEffectTag::pluck('name', 'id')->toArray();
        });
    }

    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Select::make('videoTags')
                ->options($this->videoEffectTag)
                ->multiple()
                ->title(__('Tags')),
        ];
    }
}