<?php

namespace App\Orchid\Layouts\Payments;

use Orchid\Screen\Fields\Label;
use Orchid\Screen\Layouts\Rows;

class InfoCalculateLayout extends Rows
{
    public function fields(): iterable
    {
        return [
            Label::make('day_prev_calculate.updated_at')
                ->title('Last calculate'),

            Label::make('coefficients.full_earnings')
                ->title('Full Earnings'),

            Label::make('coefficients.сlassic_earnings')
                ->title('Classic Earnings'),

            Label::make('coefficients.billing_earnings')
                ->title('Billing Earnings'),

            Label::make('coefficients.total_downloads')
                ->title('Total Downloads'),

            Label::make('coefficients.audio_downloads')
                ->title('Audio Downloads'),

            Label::make('coefficients.video_downloads')
                ->title('Video Downloads'),

            Label::make('coefficients.cost_per_audio')
                ->title('Cost Per Audio'),

            Label::make('coefficients.cost_per_video')
                ->title('Cost Per Video'),

            Label::make('coefficients.prev_fee')
                ->title('Fee'),

            Label::make('coefficients.prev_wmusic')
                ->title('WMusic'),

            Label::make('coefficients.prev_wvideo')
                ->title('WVideo'),

            Label::make('coefficients.prev_wex')
                ->title('WEx'),

            Label::make('coefficients.prev_wnoex')
                ->title('WNoEx'),
        ];
    }
}