<?php

namespace App\Orchid\Filters\Payments;

use App\Models\Finance\Balance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Select;

class DateFilter extends Filter
{
    /**
     * The displayable name of the filter.
     *
     * @return string
     */
    public function name(): string
    {
        return __('Date');
    }

    /**
     * The array of matched parameters.
     *
     * @return array
     */
    public function parameters(): array
    {
        return ['date'];
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
        return $builder->where('date',$this->request->get('date'));
    }

    /**
     * Get the display fields.
     */
    public function display(): array
    {
        $options = Balance::select(DB::raw('DISTINCT(date) AS date'))
            ->orderByDesc('date')->pluck('date', 'date')->toArray();

        return [
            Select::make('date')
                ->options($options)
                ->empty()
                ->value($this->request->get('date'))
                ->title(__('Date')),
        ];
    }
}
