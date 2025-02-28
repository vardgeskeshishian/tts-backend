<?php

namespace App\Orchid\Screens\Payments;

use App\Jobs\BalanceCalculateJobs;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Layout;
use Orchid\Screen\Screen;
use Illuminate\Http\Request;
use App\Models\Finance\Balance;
use App\Models\PayoutCoefficient;
use App\Orchid\Layouts\Payments\PaymentsFiltersLayout;
use App\Orchid\Layouts\Payments\PaymentsListLayout;
use App\Orchid\Layouts\Payments\PayoutCoefficientFormLayout;
use App\Orchid\Layouts\Payments\InfoCalculateLayout;
use Orchid\Support\Color;
use Orchid\Support\Facades\Toast;

class PaymentsListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): array
    {
        // Return the result as an array
        return [
            'payouts' => Balance::filters(PaymentsFiltersLayout::class)->defaultSort('id', 'desc')
                ->with('user.authors')->paginate(100), // Assuming $payouts is a collection
            'coefficients' => PayoutCoefficient::pluck('value', 'name'),
            'day_prev_calculate' => PayoutCoefficient::where('name', '=', 'day_prev_calculate')->first()
        ];
    }
    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Payments Management';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'Complete list of all authors with payments';
    }

    /**
     * @return iterable|null
     */
    public function permission(): ?iterable
    {
        return [
            'platform.systems.users',
        ];
    }

    /**
     * The screen's action buttons.
     *
     * @return Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * The screen's layout elements.
     *
     * @return string[]|Layout[]
     */
    public function layout(): iterable
    {
        return [
            PaymentsFiltersLayout::class,
            PaymentsListLayout::class,
            \Orchid\Support\Facades\Layout::block([
                PayoutCoefficientFormLayout::class,
            ])->commands([
                Button::make('Update')
                    ->method('updatePayoutCoefficients')
                    ->type(Color::PRIMARY)
                    ->icon('bs.arrow-clockwise')
                    ->novalidate(),

                Button::make('Calculate')
                    ->method('calculate')
                    ->type(Color::SECONDARY)
                    ->icon('bs.calculator-fill')
                    ->novalidate()
            ]),
            \Orchid\Support\Facades\Layout::block([
                InfoCalculateLayout::class,
            ]),
        ];
    }

    /**
     * Set author's payout status complete 
     */
    public function completePayoutById(Request $request)
    {
        $balanceId = $request->input('balances');
        $status = $request->input('status');

        $balance = Balance::find($balanceId);

        if ($balance) {
            $balance->update(['status' => $status, 'confirmed_at' => Carbon::now()]);
        }
    }

    public function updatePayoutCoefficients(Request $request): RedirectResponse
    {
        $coefficients = [
            'fee' => $request->input('coefficients.fee'),
            'wmusic' => $request->input('coefficients.wmusic'),
            'wvideo' => $request->input('coefficients.wvideo'),
            'wex' => $request->input('coefficients.wex'),
            'wnoex' => $request->input('coefficients.wnoex'),
        ];

        foreach ($coefficients as $name => $value) {
            PayoutCoefficient::where('name', $name)->update(['value' => $value]);
        }

        return redirect()->back()->with('success', 'Payout coefficients updated successfully.');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function calculate(Request $request): RedirectResponse
    {
        BalanceCalculateJobs::dispatch();
        Toast::info(__('The recalculation process has started. Please wait 1 minute.'));
        return redirect()->route('platform.systems.payments');
    }
}
