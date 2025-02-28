<?php

namespace App\Orchid\Filters\Payments;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\CheckBox;

class AuthorAudioFilter extends Filter
{
    /**
     * The displayable name of the filter.
     *
     * @return string
     */
    public function name(): string
    {
        return __('Author Audio');
    }

    /**
     * The array of matched parameters.
     *
     * @return array
     */
    public function parameters(): array
    {
        return ['author_audio'];
    }

    /**
     * Apply to a given Eloquent query builder.
     *
     * @param Builder $builder
     *
     * @return Builder
     */
    public function run(Builder $builder): Builder
    {
        return $builder->whereHas('user', function ($query) {
            $query->whereHas('authors', function ($query) {
                $query->where('is_track', $this->request->get('author_audio'));
            });
        });
    }

    /**
     * Get the display fields.
     */
    public function display(): array
    {
        return [
            CheckBox::make('author_audio')
                ->title('Author Audio')
                ->sendTrueOrFalse(),
        ];
    }
}