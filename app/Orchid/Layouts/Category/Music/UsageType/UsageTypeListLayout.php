<?php

namespace App\Orchid\Layouts\Category\Music\UsageType;

use App\Models\Structure\TemplateMeta;
use App\Models\Tags\Type;
use Illuminate\Support\Str;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Components\Cells\DateTimeSplit;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class UsageTypeListLayout extends Table
{
    public $target = 'types';

    public function columns(): iterable
    {
        return [
            TD::make('name', __('Name'))
                ->filter(Input::make())
                ->sort()
                ->cantHide()->width('150px'),

            TD::make('h1', __('H1'))
                ->sort()
                ->cantHide()
                ->width('150px')
                ->render(function (Type $type) {
                    $templateMeta = TemplateMeta::where('type', Type::class)->first();
                    return is_null($type->h1) ?
                        str_replace('%Category_Name%', $type->name,
                            str_replace('%category_name%', Str::lower($type->name), $templateMeta?->h1)) :
                        $type->h1;
                }),

            TD::make('description', __('Description'))
                ->sort()
                ->cantHide()
                ->width('150px')
                ->render(function (Type $type) {
                    $templateMeta = TemplateMeta::where('type', Type::class)->first();
                    return is_null($type->description) ?
                        str_replace('%Category_Name%', $type->name,
                            str_replace('%category_name%', Str::lower($type->name), $templateMeta?->description)) :
                        $type->description;
                }),

            TD::make('metaTitle', __('Meta-title'))
                ->sort()
                ->cantHide()->width('150px')
                ->render(function (Type $type) {
                    $templateMeta = TemplateMeta::where('type', Type::class)->first();
                    return is_null($type->metaTitle) ?
                        str_replace('%Category_Name%', $type->name, $templateMeta?->metaTitle) :
                        $type->metaTitle;
                }),

            TD::make('metaDescription', __('Meta-description'))
                ->sort()
                ->cantHide()->width('150px')
                ->render(function (Type $type) {
                    $templateMeta = TemplateMeta::where('type', Type::class)->first();
                    return is_null($type->metaDescription) ?
                        str_replace('%Category_Name%', $type->name, $templateMeta?->metaDescription) :
                        $type->metaDescription;
                }),

            TD::make('Priority', __('Priority'))
                ->sort()
                ->cantHide()
                ->render(function (Type $type) {
                    return $type->priority ?? null;
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
                ->render(fn (Type $type) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([

                        Link::make(__('Edit'))
                            ->route('platform.systems.category.music.usage-type.edit', $type->id)
                            ->icon('bs.pencil'),

                        Button::make(__('Delete'))
                            ->icon('bs.trash3')
                            ->method('remove', [
                                'id' => $type->id,
                            ]),
                    ])),
        ];
    }
}
