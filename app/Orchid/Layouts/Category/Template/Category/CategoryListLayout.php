<?php

namespace App\Orchid\Layouts\Category\Template\Category;

use App\Models\Structure\TemplateMeta;
use App\Models\VideoEffects\VideoEffectCategory;
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
    public $target = 'videoEffectCategories';

    public function columns(): iterable
    {
        return [
            TD::make('name', __('Name'))
                ->filter(Input::make())
                ->sort()
                ->cantHide()->width('175px'),

            TD::make('h1', __('H1'))
                ->sort()
                ->cantHide()->width('175px')
                ->render(function (VideoEffectCategory $category) {
                    $templateMeta = TemplateMeta::where('type', VideoEffectCategory::class)->first();
                    return is_null($category->h1) ?
                        str_replace('%Category_Name%', $category->name,
                            str_replace('%category_name%', Str::lower($category->name), $templateMeta?->h1)) :
                        $category->h1;
                }),

            TD::make('description', __('Description'))
                ->sort()
                ->cantHide()->width('175px')
                ->render(function (VideoEffectCategory $category) {
                    $templateMeta = TemplateMeta::where('type', VideoEffectCategory::class)->first();
                    return is_null($category->description) ?
                        str_replace('%Category_Name%', $category->name, $templateMeta?->description) :
                        $category->description;
                }),

            TD::make('metaTitle', __('Meta-title'))
                ->sort()
                ->cantHide()->width('175px')
                ->render(function (VideoEffectCategory $category) {
                    $templateMeta = TemplateMeta::where('type', VideoEffectCategory::class)->first();
                    return is_null($category->metaTitle) ?
                        str_replace('%Category_Name%', $category->name, $templateMeta?->metaTitle) :
                        $category->metaTitle;
                }),

            TD::make('metaDescription', __('Meta-description'))
                ->sort()
                ->cantHide()->width('175px')
                ->render(function (VideoEffectCategory $category) {
                    $templateMeta = TemplateMeta::where('type', VideoEffectCategory::class)->first();
                    return is_null($category->metaDescription) ?
                        str_replace('%Category_Name%', $category->name, $templateMeta?->metaDescription) :
                        $category->metaDescription;
                }),

            TD::make('Priority', __('Priority'))
                ->sort()
                ->cantHide()
                ->render(function (VideoEffectCategory $category) {
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
                ->render(fn (VideoEffectCategory $category) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([

                        Link::make(__('Edit'))
                            ->route('platform.systems.category.template.category.edit', $category->id)
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
