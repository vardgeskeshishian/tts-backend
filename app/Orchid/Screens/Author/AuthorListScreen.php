<?php

namespace App\Orchid\Screens\Author;

use Illuminate\Http\Request;
use Orchid\Screen\Action;
use Orchid\Screen\Layout;
use Orchid\Screen\Screen;
use Orchid\Screen\Actions\Link;
use App\Models\Authors\AuthorProfile;
use App\Orchid\Layouts\Authors\AuthorListLayout;
use Orchid\Support\Facades\Toast;

class AuthorListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): array
    {
        return [
            'authors' => AuthorProfile::filters()->orderBy('id')
                ->paginate(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Author Management';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'Complete list of all authors';
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
            Link::make(__('Add'))
                ->icon('bs.plus-circle')
                ->route('platform.systems.authors.create'),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return string[]|Layout[]
     */
    public function layout(): iterable
    {
        return [
            AuthorListLayout::class,
        ];
    }

    public function remove(Request $request): void
    {
        AuthorProfile::findOrFail($request->get('id'))->delete();

        Toast::info(__('Author was removed'));
    }
}
