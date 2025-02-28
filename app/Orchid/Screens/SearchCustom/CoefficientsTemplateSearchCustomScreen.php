<?php

namespace App\Orchid\Screens\SearchCustom;

use App\Orchid\Layouts\Search\Coefficients\Template\CoefficientMTCLayout;
use App\Orchid\Layouts\Search\Coefficients\Template\CoefficientMTELayout;
use App\Orchid\Layouts\Search\Coefficients\Template\CoefficientPeriodDemandLayout;
use App\Orchid\Layouts\Search\Coefficients\Template\CoefficientPeriodNewLayout;
use App\Orchid\Layouts\Search\Coefficients\Template\CoefficientWEMCLayout;
use App\Orchid\Layouts\Search\Coefficients\Template\CoefficientWNLayout;
use App\Orchid\Layouts\Search\Coefficients\Template\CoefficientWTMCLayout;
use App\Orchid\Layouts\Search\Coefficients\Template\CoefficientWTrendFreeLayout;
use App\Orchid\Layouts\Search\Coefficients\Template\CoefficientWTrendSubsLayout;
use App\Orchid\Layouts\Search\Coefficients\Template\CoefficientWordsLayout;
use App\Models\Coefficient;
use App\Jobs\UpdateVideoCoefficientAllJobs;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class CoefficientsTemplateSearchCustomScreen extends Screen
{
    /**
     * @return array
     */
    public function query(): array
    {
        return Coefficient::where('is_template', true)
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
        Coefficient::where('short_name', 'mte_video')->update(['coefficient' => $request->input('mte_video')]);
        Coefficient::where('short_name', 'mtc_video')->update(['coefficient' => $request->input('mtc_video')]);
        Coefficient::where('short_name', 'period_new_video')->update(['coefficient' => $request->input('period_new_video')]);
        Coefficient::where('short_name', 'period_demand_video')->update(['coefficient' => $request->input('period_demand_video')]);
        Coefficient::where('short_name', 'w_emc_video')->update(['coefficient' => $request->input('w_emc_video')]);
        Coefficient::where('short_name', 'w_tmc_video')->update(['coefficient' => $request->input('w_tmc_video')]);
        Coefficient::where('short_name', 'w_n_video')->update(['coefficient' => $request->input('w_n_video')]);
        Coefficient::where('short_name', 'free_coefficient_video')->update(['coefficient' => $request->input('free_coefficient_video')]);
        Coefficient::where('short_name', 'subs_coefficient_video')->update(['coefficient' => $request->input('subs_coefficient_video')]);
        Coefficient::where('short_name', 'words_video')->update(['coefficient' => $request->input('words_video')]);

        Toast::info(__('Coefficients was saved'));

        UpdateVideoCoefficientAllJobs::dispatch();

        return redirect()->route('platform.systems.search.content');
    }
}