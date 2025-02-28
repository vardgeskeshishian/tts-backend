<?php

namespace App\Orchid\Screens\SearchCustom;

use App\Orchid\Layouts\Search\Coefficients\CoefficientMTCLayout;
use App\Orchid\Layouts\Search\Coefficients\CoefficientMTELayout;
use App\Orchid\Layouts\Search\Coefficients\CoefficientPeriodDemandLayout;
use App\Orchid\Layouts\Search\Coefficients\CoefficientPeriodNewLayout;
use App\Orchid\Layouts\Search\Coefficients\CoefficientWEMCLayout;
use App\Orchid\Layouts\Search\Coefficients\CoefficientWNLayout;
use App\Orchid\Layouts\Search\Coefficients\CoefficientWTMCLayout;
use App\Orchid\Layouts\Search\Coefficients\CoefficientWTrendFreeLayout;
use App\Orchid\Layouts\Search\Coefficients\CoefficientWTrendSubsLayout;
use App\Orchid\Layouts\Search\Coefficients\CoefficientWordsLayout;
use App\Models\Coefficient;
use App\Jobs\UpdateSFXCoefficientAllJobs;
use App\Jobs\UpdateTrackCoefficientAllJobs;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class CoefficientsSearchCustomScreen extends Screen
{
    /**
     * @return array
     */
    public function query(): array
    {
        return Coefficient::where('is_template', false)
            ->get()->mapWithKeys(function($item) {
                return [$item->short_name => $item->coefficient];
            })->toArray();
    }

    /**
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Edit Coefficients';
    }

    /**
     * @return string|null
     */
    public function description(): ?string
    {
        return 'Changes related to search coefficients.';
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
        return [
            Button::make(__('Save'))
                ->icon('bs.check-circle')
                ->method('save'),
        ];
    }

    /**
     * @return iterable
     */
    public function layout(): iterable
    {
        return [
            Layout::block([
                CoefficientMTELayout::class
            ])->title('Max Tag Exact'),

            Layout::block([
                CoefficientMTCLayout::class
            ])->title('Max Tag Count'),

            Layout::block([
                CoefficientPeriodNewLayout::class
            ])->title('Period of novelty'),

            Layout::block([
                CoefficientPeriodDemandLayout::class
            ])->title('Period of demand'),

            Layout::block([
                CoefficientWEMCLayout::class
            ])->title('Weight EMC'),

            Layout::block([
                CoefficientWTMCLayout::class
            ])->title('Weight TMC'),

            Layout::block([
                CoefficientWNLayout::class
            ])->title('Weight N'),

            Layout::block([
                CoefficientWTrendFreeLayout::class
            ])->title('Weight Trend Free'),

            Layout::block([
                CoefficientWTrendSubsLayout::class
            ])->title('Weight Trend Subs'),

            Layout::block([
                CoefficientWordsLayout::class
            ])->title('Words')
        ];
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function save(Request $request): RedirectResponse
    {
        Coefficient::where('short_name', 'mte')->update(['coefficient' => $request->input('mte')]);
        Coefficient::where('short_name', 'mtc')->update(['coefficient' => $request->input('mtc')]);
        Coefficient::where('short_name', 'period_new')->update(['coefficient' => $request->input('period_new')]);
        Coefficient::where('short_name', 'period_demand')->update(['coefficient' => $request->input('period_demand')]);
        Coefficient::where('short_name', 'w_emc')->update(['coefficient' => $request->input('w_emc')]);
        Coefficient::where('short_name', 'w_tmc')->update(['coefficient' => $request->input('w_tmc')]);
        Coefficient::where('short_name', 'w_n')->update(['coefficient' => $request->input('w_n')]);
        Coefficient::where('short_name', 'words')->update(['coefficient' => $request->input('words')]);
        Coefficient::where('short_name', 'free_coefficient')->update(['coefficient' => $request->input('free_coefficient')]);
        Coefficient::where('short_name', 'subs_coefficient')->update(['coefficient' => $request->input('subs_coefficient')]);

        Toast::info(__('Coefficients was saved'));

        UpdateTrackCoefficientAllJobs::dispatch();
        UpdateSFXCoefficientAllJobs::dispatch();

        return redirect()->route('platform.systems.search.content');
    }
}