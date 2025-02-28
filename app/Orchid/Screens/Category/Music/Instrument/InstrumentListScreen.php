<?php

namespace App\Orchid\Screens\Category\Music\Instrument;

use Illuminate\Support\Facades\DB;
use Orchid\Screen\Screen;
use Illuminate\Http\Request;
use App\Models\Tags\Instrument;
use Orchid\Screen\Actions\Link;
use Orchid\Support\Facades\Toast;
use App\Orchid\Layouts\Category\Music\Instrument\InstrumentListLayout;

class InstrumentListScreen extends Screen
{
    /**
     * @return array
     */
    public function query(): array
    {
        return [
            'instruments' => Instrument::filters()
                ->orderBy(DB::raw('ISNULL(priority), priority'), 'ASC')
                ->orderBy('priority')
                ->orderBy('id')
                ->paginate(),
        ];
    }

    /**
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Category Instrument Management';
    }

    /**
     * @return string|null
     */
    public function description(): ?string
    {
        return 'A comprehensive list of all instrument.';
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
     * @return iterable
     */
    public function commandBar(): iterable
    {
        return [
            Link::make(__('Edit Template Meta'))
                ->icon('bs.pencil')
                ->route('platform.systems.category', ['category' => 'instrument']),

            Link::make(__('Add'))
                ->icon('bs.plus-circle')
                ->route('platform.systems.category.music.instrument.create'),
        ];
    }

    /**
     * @return iterable
     */
    public function layout(): iterable
    {
        return [
            InstrumentListLayout::class
        ];
    }

    public function remove(Request $request): void
    {
        Instrument::findOrFail($request->get('id'))->delete();

        Toast::info(__('Instrument was removed'));
    }
}
