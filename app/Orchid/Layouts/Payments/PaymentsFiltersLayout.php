<?php

namespace App\Orchid\Layouts\Payments;

use App\Orchid\Filters\Payments\DateFilter;
use App\Orchid\Filters\Payments\PaymentEmailFilter;
use App\Orchid\Filters\Payments\StatusFilter;
use App\Orchid\Filters\Payments\EmailFilter;
use App\Orchid\Filters\Payments\AuthorAudioFilter;
use App\Orchid\Filters\Payments\AuthorVideoFilter;
use Orchid\Filters\Filter;
use Orchid\Screen\Layouts\Selection;

class PaymentsFiltersLayout extends Selection
{
    /**
     * @return string[]|Filter[]
     */
    public function filters(): array
    {
        return [
            DateFilter::class,
            PaymentEmailFilter::class,
            StatusFilter::class,
            EmailFilter::class,
            AuthorAudioFilter::class,
            AuthorVideoFilter::class
        ];
    }
}