<?php

namespace App\Orchid\Layouts\Category\Music\Mood;

use App\Models\Structure\TemplateMeta;
use App\Models\Tags\Mood;
use Illuminate\Support\Str;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Components\Cells\DateTimeSplit;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class MoodListLayout extends Table
{
    public $target = 'moods';

    public function columns(): iterable
    {
        return [
            TD::make('name', __('Name'))
                ->filter(Input::make())
                ->sort()
                ->cantHide()
                ->width('150px'),

            TD::make('h1', __('H1'))
                ->sort()
                ->cantHide()
                ->width('150px')
                ->render(function (Mood $mood) {
                    $templateMeta = TemplateMeta::where('type', Mood::class)->first();
                    return is_null($mood->h1) ?
                        str_replace('%Category_Name%', $mood->name,
                            str_replace('%category_name%', Str::lower($mood->name), $templateMeta?->h1)) :
                        $mood->h1;
                }),

            TD::make('description', __('Description'))
                ->sort()
                ->cantHide()
                ->width('150px')
                ->render(function (Mood $mood) {
                    $templateMeta = TemplateMeta::where('type', Mood::class)->first();
                    return is_null($mood->description) ?
                        str_replace('%Category_Name%', $mood->name,
                            str_replace('%category_name%', Str::lower($mood->name), $templateMeta?->description)) :
                        $mood->description;
                }),

            TD::make('metaTitle', __('Meta-title'))
                ->sort()
                ->cantHide()
                ->width('150px')
                ->render(function (Mood $mood) {
                    $templateMeta = TemplateMeta::where('type', Mood::class)->first();
                    return is_null($mood->metaTitle) ?
                        str_replace('%Category_Name%', $mood->name, $templateMeta?->metaTitle) :
                        $mood->metaTitle;
                }),

            TD::make('metaDescription', __('Meta-description'))
                ->sort()
                ->cantHide()
                ->width('150px')
                ->render(function (Mood $mood) {
                    $templateMeta = TemplateMeta::where('type', Mood::class)->first();
                    return is_null($mood->metaDescription) ?
                        str_replace('%Category_Name%', $mood->name, $templateMeta?->metaDescription) :
                        $mood->metaDescription;
                }),

            TD::make('Priority', __('Priority'))
                ->sort()
                ->cantHide()
                ->render(function (Mood $mood) {
                    return $mood->priority ?? null;
                }),

            TD::make('created_at', __('Created'))
                ->usingComponent(DateTimeSplit::class)
                ->align(TD::ALIGN_RIGHT)
                ->defaultHidden()
                ->sort(),

            TD::make('updated_at', __('Last edit'))
                ->usingComponent(DateTimeSplit::class)
                ->align(TD::ALIGN_RIGHT)
                ->sort(),

            TD::make(__('Actions'))
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn (Mood $mood) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([

                        Link::make(__('Edit'))
                            ->route('platform.systems.category.music.mood.edit', $mood->id)
                            ->icon('bs.pencil'),

                        Button::make(__('Delete'))
                            ->icon('bs.trash3')
                            ->method('remove', [
                                'id' => $mood->id,
                            ]),
                    ])),
        ];
    }
}
