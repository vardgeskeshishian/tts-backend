<?php

namespace App\Orchid\Layouts\Category\Music\CuratorPick;

use App\Models\Structure\TemplateMeta;
use App\Models\Tags\CuratorPick;
use Illuminate\Support\Str;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Components\Cells\DateTimeSplit;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class CuratorPickListLayout extends Table
{
    public $target = 'curator_picks';

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
                ->render(function (CuratorPick $curator_pick) {
                    $templateMeta = TemplateMeta::where('type', CuratorPick::class)->first();
                    return is_null($curator_pick->h1) ?
                        str_replace('%Category_Name%', $curator_pick->name,
                            str_replace('%category_name%', Str::lower($curator_pick->name), $templateMeta?->h1)) :
                        $curator_pick->h1;
                }),

            TD::make('description', __('Description'))
                ->sort()
                ->cantHide()
                ->width('150px')
                ->render(function (CuratorPick $curator_pick) {
                    $templateMeta = TemplateMeta::where('type', CuratorPick::class)->first();
                    return is_null($curator_pick->description) ?
                        str_replace('%Category_Name%', $curator_pick->name,
                            str_replace('%category_name%', Str::lower($curator_pick->name), $templateMeta?->description)) :
                        $curator_pick->description;
                }),

            TD::make('metaTitle', __('Meta-title'))
                ->sort()
                ->cantHide()
                ->width('150px')
                ->render(function (CuratorPick $curator_pick) {
                    $templateMeta = TemplateMeta::where('type', CuratorPick::class)->first();
                    return is_null($curator_pick->metaTitle) ?
                        str_replace('%Category_Name%', $curator_pick->name, $templateMeta?->metaTitle) :
                        $curator_pick->metaTitle;
                }),

            TD::make('metaDescription', __('Meta-description'))
                ->sort()
                ->cantHide()
                ->width('150px')
                ->render(function (CuratorPick $curator_pick) {
                    $templateMeta = TemplateMeta::where('type', CuratorPick::class)->first();
                    return is_null($curator_pick->metaDescription) ?
                        str_replace('%Category_Name%', $curator_pick->name, $templateMeta?->metaDescription) :
                        $curator_pick->metaDescription;
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
                ->render(fn (CuratorPick $curator_pick) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([

                        Link::make(__('Edit'))
                            ->route('platform.systems.category.music.curator-pick.edit', $curator_pick->id)
                            ->icon('bs.pencil'),

                        Button::make(__('Delete'))
                            ->icon('bs.trash3')
                            ->method('remove', [
                                'id' => $curator_pick->id,
                            ]),
                    ])),
        ];
    }
}