<?php

namespace App\Orchid\Layouts\Category\Template\Tag;

use App\Models\Structure\TemplateMeta;
use App\Models\VideoEffects\VideoEffectTag;
use Illuminate\Support\Str;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Components\Cells\DateTimeSplit;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class TagListLayout extends Table
{
    public $target = 'videoEffectTags';

    public function columns(): iterable
    {
        return [
            TD::make('name', __('Name'))
                ->filter(Input::make())
                ->sort()
                ->cantHide()->width('125px'),

            TD::make('position', __('Position'))
                ->sort()
                ->cantHide()->width('125px'),

            TD::make('h1', __('H1'))
                ->sort()
                ->cantHide()->width('125px')
                ->render(function (VideoEffectTag $tag) {
                    $templateMeta = TemplateMeta::where('type', VideoEffectTag::class)->first();
                    return is_null($tag->h1) ?
                        str_replace('%Category_Name%', $tag->name,
                            str_replace('%category_name%', Str::lower($tag->name), $templateMeta?->h1)) :
                        $tag->h1;
                }),

            TD::make('description', __('Description'))
                ->sort()
                ->cantHide()->width('125px')
                ->render(function (VideoEffectTag $tag) {
                    $templateMeta = TemplateMeta::where('type', VideoEffectTag::class)->first();
                    return is_null($tag->description) ?
                        str_replace('%Category_Name%', $tag->name,
                            str_replace('%category_name%', Str::lower($tag->name), $templateMeta?->description)) :
                        $tag->description;
                }),

            TD::make('metaTitle', __('Meta-title'))
                ->sort()
                ->cantHide()->width('125px')
                ->render(function (VideoEffectTag $tag) {
                    $templateMeta = TemplateMeta::where('type', VideoEffectTag::class)->first();
                    return is_null($tag->metaTitle) ?
                        str_replace('%Category_Name%', $tag->name, $templateMeta?->metaTitle) :
                        $tag->metaTitle;
                }),

            TD::make('metaDescription', __('Meta-description'))
                ->sort()
                ->cantHide()->width('125px')
                ->render(function (VideoEffectTag $tag) {
                    $templateMeta = TemplateMeta::where('type', VideoEffectTag::class)->first();
                    return is_null($tag->metaDescription) ?
                        str_replace('%Category_Name%', $tag->name, $templateMeta?->metaDescription) :
                        $tag->metaDescription;
                }),

            TD::make('Priority', __('Priority'))
                ->sort()
                ->cantHide()
                ->render(function (VideoEffectTag $tag) {
                    return $tag->priority ?? null;
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
                ->render(fn (VideoEffectTag $tag) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([

                        Link::make(__('Edit'))
                            ->route('platform.systems.category.template.templateTag.edit', $tag->id)
                            ->icon('bs.pencil'),

                        Button::make(__('Delete'))
                            ->icon('bs.trash3')
                            ->method('remove', [
                                'id' => $tag->id,
                            ]),
                    ])),
        ];
    }
}
