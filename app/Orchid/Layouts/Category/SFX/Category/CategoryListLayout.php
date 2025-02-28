<?php

namespace App\Orchid\Layouts\Category\SFX\Category;

use App\Models\SFX\SFXCategory;
use App\Models\Structure\TemplateMeta;
use Illuminate\Support\Str;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Components\Cells\DateTimeSplit;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class CategoryListLayout extends Table
{
    public $target = 'sfxCategories';

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
                ->render(function (SFXCategory $category) {
                    $templateMeta = TemplateMeta::where('type', SFXCategory::class)->first();
                    return is_null($category->h1) ?
                        str_replace('%Category_Name%', $category->name,
                            str_replace('%category_name%', Str::lower($category->name), $templateMeta?->h1)) :
                        $category->h1;
                }),

            TD::make('description', __('Description'))
                ->sort()
                ->cantHide()
                ->width('150px')
                ->render(function (SFXCategory $category) {
                    $templateMeta = TemplateMeta::where('type', SFXCategory::class)->first();
                    return is_null($category->description) ?
                        str_replace('%Category_Name%', $category->name,
                            str_replace('%category_name%', Str::lower($category->name), $templateMeta?->description)) :
                        $category->description;
                }),

            TD::make('metaTitle', __('Meta-title'))
                ->sort()
                ->cantHide()->width('150px')
                ->render(function (SFXCategory $category) {
                    $templateMeta = TemplateMeta::where('type', SFXCategory::class)->first();
                    return is_null($category->metaTitle) ?
                        str_replace('%Category_Name%', $category->name, $templateMeta?->metaTitle) :
                        $category->metaTitle;
                }),

            TD::make('metaDescription', __('Meta-description'))
                ->sort()
                ->cantHide()->width('150px')
                ->render(function (SFXCategory $category) {
                    $templateMeta = TemplateMeta::where('type', SFXCategory::class)->first();
                    return is_null($category->metaDescription) ?
                        str_replace('%Category_Name%', $category->name, $templateMeta?->metaDescription) :
                        $category->metaDescription;
                }),

            TD::make('Priority', __('Priority'))
                ->sort()
                ->cantHide()
                ->render(function (SFXCategory $category) {
                    return $category->priority ?? null;
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
                ->render(fn (SFXCategory $category) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([

                        Link::make(__('Edit'))
                            ->route('platform.systems.category.sfx.sfxCategory.edit', $category->id)
                            ->icon('bs.pencil'),

                        Button::make(__('Delete'))
                            ->icon('bs.trash3')
                            ->method('remove', [
                                'id' => $category->id,
                            ]),
                    ])),
        ];
    }
}
