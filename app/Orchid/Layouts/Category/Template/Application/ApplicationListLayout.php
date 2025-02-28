<?php

namespace App\Orchid\Layouts\Category\Template\Application;

use App\Models\Structure\TemplateMeta;
use App\Models\VideoEffects\VideoEffectApplication;
use Illuminate\Support\Str;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Components\Cells\DateTimeSplit;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class ApplicationListLayout extends Table
{
    public $target = 'videoEffectApplications';

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
                ->render(function (VideoEffectApplication $application) {
                    $templateMeta = TemplateMeta::where('type', VideoEffectApplication::class)->first();
                    return is_null($application->h1) ?
                        str_replace('%Category_Name%', $application->name,
                            str_replace('%category_name%', Str::lower($application->name), $templateMeta?->h1)) :
                        $application->h1;
                }),

            TD::make('description', __('Description'))
                ->sort()
                ->cantHide()->width('175px')
                ->render(function (VideoEffectApplication $application) {
                    $templateMeta = TemplateMeta::where('type', VideoEffectApplication::class)->first();
                    return is_null($application->description) ?
                        str_replace('%Category_Name%', $application->name,
                            str_replace('%category_name%', Str::lower($application->name), $templateMeta?->description)) :
                        $application->description;
                }),

            TD::make('metaTitle', __('Meta-title'))
                ->sort()
                ->cantHide()->width('175px')
                ->render(function (VideoEffectApplication $application) {
                    $templateMeta = TemplateMeta::where('type', VideoEffectApplication::class)->first();
                    return is_null($application->metaTitle) ?
                        str_replace('%Category_Name%', $application->name, $templateMeta?->metaTitle) :
                        $application->metaTitle;
                }),

            TD::make('metaDescription', __('Meta-description'))
                ->sort()
                ->cantHide()->width('175px')
                ->render(function (VideoEffectApplication $application) {
                    $templateMeta = TemplateMeta::where('type', VideoEffectApplication::class)->first();
                    return is_null($application->metaDescription) ?
                        str_replace('%Category_Name%', $application->name, $templateMeta?->metaDescription) :
                        $application->metaDescription;
                }),

            TD::make('Priority', __('Priority'))
                ->sort()
                ->cantHide()
                ->render(function (VideoEffectApplication $application) {
                    return $application->priority ?? null;
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
                ->render(fn (VideoEffectApplication $application) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([

                        Link::make(__('Edit'))
                            ->route('platform.systems.category.template.application.edit', $application->id)
                            ->icon('bs.pencil'),

                        Button::make(__('Delete'))
                            ->icon('bs.trash3')
                            ->method('remove', [
                                'id' => $application->id,
                            ]),
                    ])),
        ];
    }
}
