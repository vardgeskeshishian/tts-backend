<?php

namespace App\Orchid\Screens\Pages;

use App\Orchid\Layouts\Pages\PageListLayout;
use App\Models\Structure\Page;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Toast;

class PageListScreen extends Screen
{
    /**
     * @return array
     */
    public function query(): array
    {
        return [
            'pages' => Page::filters()->paginate()
        ];
    }

    /**
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Pages Management';
    }

    /**
     * @return string|null
     */
    public function description(): ?string
    {
        return 'A comprehensive list of all pages.';
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
            Link::make(__('Add'))
                ->icon('bs.plus-circle')
                ->route('platform.systems.pages.create'),
        ];
    }

    /**
     * @return iterable
     */
    public function layout(): iterable
    {
        return [
            PageListLayout::class
        ];
    }

    /**
     * @param Request $request
     * @return void
     */
    public function remove(Request $request): void
    {
        Page::findOrFail($request->get('id'))->delete();

        Toast::info(__('Page was removed'));
    }
}