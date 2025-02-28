<?php

namespace App\Orchid\Filters\Payments;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\CheckBox;

class AuthorVideoFilter extends Filter
{
    /**
     * The displayable name of the filter.
     *
     * @return string
     */
    public function name(): string
    {
        return __('Author Video');
    }

    /**
     * The array of matched parameters.
     *
     * @return array
     */
    public function parameters(): array
    {
        return ['author_video'];
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
                $query->where('is_video', $this->request->get('author_video'));
            });
        });
    }

    /**
     * Get the display fields.
     */
    public function display(): array
    {
        return [
            CheckBox::make('author_video')
                ->title('Author Video')
                ->sendTrueOrFalse(),
        ];
    }
}