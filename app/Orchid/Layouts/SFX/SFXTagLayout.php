<?php

namespace App\Orchid\Layouts\SFX;

use App\Models\SFX\SFXCategory;
use App\Models\SFX\SFXTag;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class SFXTagLayout extends Rows
{
    public function fields(): iterable
    {
        return [
            Select::make('sfx.sfxCategories.')
                ->fromModel(SFXCategory::class, 'name')
                ->multiple()
                ->title(__('Category')),

            Select::make('sfx.sfxTags.')
                ->fromModel(SFXTag::class, 'name')
                ->multiple()
                ->title(__('Tag')),
        ];
    }
}