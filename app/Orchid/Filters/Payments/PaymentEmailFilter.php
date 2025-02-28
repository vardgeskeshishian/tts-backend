<?php

namespace App\Orchid\Filters\Payments;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Input;

class PaymentEmailFilter extends Filter
{
    /**
     * The displayable name of the filter.
     *
     * @return string
     */
    public function name(): string
    {
        return __('Payment Email');
    }

    /**
     * The array of matched parameters.
     *
     * @return array
     */
    public function parameters(): array
    {
        return ['payment_email'];
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
        return $builder->where('payment_email', 'like', '%'.$this->request->get('payment_email').'%');
    }

    /**
     * Get the display fields.
     */
    public function display(): array
    {
        return [
            Input::make('payment_email')
                ->type('text')
                ->value($this->request->get('payment_email'))
                ->title(__('Payment Email')),
        ];
    }
}