<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Payments;

use Orchid\Screen\TD;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Color;

class PaymentsListLayout extends Table
{
    /**
     * @var string
     */
    public $target = 'payouts';

    /**
     * @return TD[]
     */
    public function columns(): array
    {
        return [
            TD::make('date', __('Balance date'))
                ->sort(),

            TD::make('user.email', __('Email')),

            TD::make('user.payout_email', __('Payment Email'))
                ->sort(),

            TD::make('author_type', __('Author Type'))
                ->render(function ($item) {
                    $authors = $item->user?->authors;

                    if (!is_null($authors))
                    {
                        $isTrack = $authors->where('is_track', 1)->first() ? 'Music' : '';
                        $isVideo = $authors->where('is_video', 1)->first() ? 'Template' : '';

                        if (!empty($isTrack) && !empty($isVideo)) {
                            return $isTrack . ', ' . $isVideo;
                        } else {
                            return $isTrack . $isVideo;
                        }
                    }

                    return 'Unknown';
                }),

            TD::make('unpaid', __('Summ Unpaids'))
                ->sort(),

            TD::make('audio_downloads', __('Audio Downloads'))
                ->sort(),

            TD::make('video_downloads', __('Video Downloads'))
                ->sort(),

            TD::make('single', __('MAE'))
                ->sort(),

            TD::make('subs', __('MSE'))
                ->sort(),

            TD::make('author_balance', __('MTotal'))
                ->sort(),

            TD::make(__('Actions'))
                ->render(function ($payout) {
                    $buttonText = $payout->status == 'awaiting' ? 'Complete' : 'Awaiting';

                    return Button::make($buttonText)
                        ->method('completePayoutById')
                        ->parameters(['balances' => $payout->id, 'status' => $payout->status == 'awaiting' ? 'complete' : 'awaiting'])
                        ->novalidate()
                        ->type($payout->status == 'awaiting' ? Color::SUCCESS : Color::DANGER)
                        ->withoutConfirmation();
                }),

            TD::make('status', __('Status')),
        ];
    }
}
