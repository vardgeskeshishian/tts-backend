<?php

namespace App\Orchid\Layouts\Category\Music\Genre;

use Illuminate\Support\Str;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\TD;
use App\Models\Tags\Genre;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use App\Models\Structure\TemplateMeta;
use Orchid\Screen\Components\Cells\DateTimeSplit;

class GenreListLayout extends Table
{
    public $target = 'genres';

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
                ->render(function (Genre $genre) {
                    $templateMeta = TemplateMeta::where('type', Genre::class)->first();
                    return is_null($genre->h1) ?
                        str_replace('%Category_Name%', $genre->name,
                            str_replace('%category_name%', Str::lower($genre->name), $templateMeta?->h1)) :
                        $genre->h1;
                }),

            TD::make('description', __('Description'))
                ->sort()
                ->cantHide()
                ->width('150px')
                ->render(function (Genre $genre) {
                    $templateMeta = TemplateMeta::where('type', Genre::class)->first();
                    return is_null($genre->description) ?
                        str_replace('%Category_Name%', $genre->name,
                            str_replace('%category_name%', Str::lower($genre->name), $templateMeta?->description)) :
                        $genre->description;
                }),

            TD::make('metaTitle', __('Meta-title'))
                ->sort()
                ->cantHide()
                ->width('150px')
                ->render(function (Genre $genre) {
                    $templateMeta = TemplateMeta::where('type', Genre::class)->first();
                    return is_null($genre->metaTitle) ?
                        str_replace('%Category_Name%', $genre->name, $templateMeta?->metaTitle) :
                        $genre->metaTitle;
                }),

            TD::make('metaDescription', __('Meta-description'))
                ->sort()
                ->cantHide()
                ->width('150px')
                ->render(function (Genre $genre) {
                    $templateMeta = TemplateMeta::where('type', Genre::class)->first();
                    return is_null($genre->metaDescription) ?
                        str_replace('%Category_Name%', $genre->name, $templateMeta?->metaDescription) :
                        $genre->metaDescription;
                }),

            TD::make('Priority', __('Priority'))
                ->sort()
                ->cantHide()
                ->render(function (Genre $genre) {
                    return $genre->priority ?? null;
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
                ->render(fn (Genre $genre) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([

                        Link::make(__('Edit'))
                            ->route('platform.systems.category.music.genre.edit', $genre->id)
                            ->icon('bs.pencil'),

                        Button::make(__('Delete'))
                            ->icon('bs.trash3')
                            ->method('remove', [
                                'id' => $genre->id,
                            ]),
                    ])),
        ];
    }
}
