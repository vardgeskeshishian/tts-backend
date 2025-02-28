<?php

namespace App\Orchid\Layouts\Category\Music\Instrument;

use App\Models\Structure\TemplateMeta;
use App\Models\Tags\Instrument;
use Illuminate\Support\Str;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Components\Cells\DateTimeSplit;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class InstrumentListLayout extends Table
{
    public $target = 'instruments';

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
                ->render(function (Instrument $instrument) {
                    $templateMeta = TemplateMeta::where('type', Instrument::class)->first();
                    return is_null($instrument->h1) ?
                        str_replace('%Category_Name%', $instrument->name,
                            str_replace('%category_name%', Str::lower($instrument->name), $templateMeta?->h1)) :
                        $instrument->h1;
                }),

            TD::make('description', __('Description'))
                ->sort()
                ->cantHide()
                ->width('150px')
                ->render(function (Instrument $instrument) {
                    $templateMeta = TemplateMeta::where('type', Instrument::class)->first();
                    return is_null($instrument->description) ?
                        str_replace('%Category_Name%', $instrument->name,
                            str_replace('%category_name%', Str::lower($instrument->name), $templateMeta?->description)) :
                        $instrument->description;
                }),

            TD::make('metaTitle', __('Meta-title'))
                ->sort()
                ->cantHide()
                ->width('150px')
                ->render(function (Instrument $instrument) {
                    $templateMeta = TemplateMeta::where('type', Instrument::class)->first();
                    return is_null($instrument->metaTitle) ?
                        str_replace('%Category_Name%', $instrument->name, $templateMeta?->metaTitle) :
                        $instrument->metaTitle;
                }),

            TD::make('metaDescription', __('Meta-description'))
                ->sort()
                ->cantHide()
                ->width('150px')
                ->render(function (Instrument $instrument) {
                    $templateMeta = TemplateMeta::where('type', Instrument::class)->first();
                    return is_null($instrument->metaDescription) ?
                        str_replace('%Category_Name%', $instrument->name, $templateMeta?->metaDescription) :
                        $instrument->metaDescription;
                }),

            TD::make('Priority', __('Priority'))
                ->sort()
                ->cantHide()
                ->render(function (Instrument $instrument) {
                    return $instrument->priority ?? null;
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
                ->render(fn (Instrument $instrument) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([

                        Link::make(__('Edit'))
                            ->route('platform.systems.category.music.instrument.edit', $instrument->id)
                            ->icon('bs.pencil'),

                        Button::make(__('Delete'))
                            ->icon('bs.trash3')
                            ->method('remove', [
                                'id' => $instrument->id,
                            ]),
                    ])),
        ];
    }
}
