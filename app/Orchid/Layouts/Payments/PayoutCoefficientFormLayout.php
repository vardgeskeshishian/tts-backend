<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Payments;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;
use App\Models\PayoutCoefficient;
use Orchid\Screen\Actions\Button;

class PayoutCoefficientFormLayout extends Rows
{
    /**
     * @return array
     */
    public function fields(): array
    {
        return [
            Input::make('coefficients.fee')
                ->title('Fee'),

            Input::make('coefficients.wmusic')
                ->title('wMusic'),

            Input::make('coefficients.wvideo')
                ->title('wVideo'),

            Input::make('coefficients.wex')
                ->title('Ex'),

            Input::make('coefficients.wnoex')
                ->title('NoEx'),
        ];
    }
}
