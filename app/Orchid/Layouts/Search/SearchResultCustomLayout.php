<?php

namespace App\Orchid\Layouts\Search;

use Orchid\Screen\Components\Cells\DateTimeSplit;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class SearchResultCustomLayout extends Table
{
    public $target = 'results';

    public function columns(): iterable
    {
        return [
            TD::make('id', __('ID'))
                ->cantHide(),

            TD::make('name', __('Product Name'))
                ->cantHide(),

            TD::make('product_type', __('Product Type'))
                ->cantHide(),

            TD::make('created_at', __('Created'))
                ->usingComponent(DateTimeSplit::class)
                ->align(TD::ALIGN_RIGHT),

            TD::make('downloads', __('Downloads By Period')),

            TD::make('count_downloads_free', __('Free Downloads By Period')),

            TD::make('count_downloads_subs', __('Subs Downloads By Period')),

            TD::make('downloads_sum_by_period', __('Downloads Sum By Period')),

            TD::make('cloud', __('Cloud'))
                ->width('300px'),

            TD::make('emc', __('EMC')),

            TD::make('tmc', __('TMC')),

            TD::make('n', __('N')),

            TD::make('w_trend_free', __('Trend Free')),

            TD::make('w_trend_subs', __('Trend Subs')),

            TD::make('w_emc', __('wEMC * EMC')),

            TD::make('w_tmc', __('wTMC * TMC')),

            TD::make('w_n', __('wN * N')),

            TD::make('w_trend_free', __('FREE_COEFFICIENT * TREND_FREE')),

            TD::make('w_trend_subs', __('SUBS_COEFFICIENT * TREND_SUBS')),

            TD::make('trending', __('Rating')),
        ];
    }
}