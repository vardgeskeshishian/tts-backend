<?php

namespace App\Orchid\Filters\Payments;

use App\Models\Finance\Balance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Select;

class StatusFilter extends Filter
{
    /**
     * The displayable name of the filter.
     *
     * @return string
     */
    public function name(): string
    {
        return __('Status');
    }

    /**
     * The array of matched parameters.
     *
     * @return array
     */
    public function parameters(): array
    {
        return ['status'];
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
        return $builder->where('status',$this->request->get('status'));
    }

    /**
     * Get the display fields.
     */
    public function display(): array
    {
        $options = Balance::select(DB::raw('DISTINCT(status) AS status'))
            ->orderByDesc('status')->pluck('status', 'status')->toArray();

        return [
            Select::make('status')
                ->options($options)
                ->empty()
                ->value($this->request->get('status'))
                ->title(__('Status')),
        ];
    }
}