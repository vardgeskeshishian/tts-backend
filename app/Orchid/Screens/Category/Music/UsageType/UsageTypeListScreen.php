<?php

namespace App\Orchid\Screens\Category\Music\UsageType;

use App\Models\Tags\Type;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Screen;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Link;
use Orchid\Support\Facades\Toast;
use App\Orchid\Layouts\Category\Music\UsageType\UsageTypeListLayout;

class UsageTypeListScreen extends Screen
{
    /**
     * @return array
     */
    public function query(): array
    {
        return [
            'types' => Type::filters()
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
        return 'Category Usage Type Management';
    }

    /**
     * @return string|null
     */
    public function description(): ?string
    {
        return 'A comprehensive list of all usage type.';
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
                ->route('platform.systems.category', ['category' => 'usage-type']),

            Link::make(__('Add'))
                ->icon('bs.plus-circle')
                ->route('platform.systems.category.music.usage-type.create'),
        ];
    }

    /**
     * @return iterable
     */
    public function layout(): iterable
    {
        return [
            UsageTypeListLayout::class
        ];
    }

    public function remove(Request $request): void
    {
        Type::findOrFail($request->get('id'))->delete();

        Toast::info(__('Type was removed'));
    }
}
