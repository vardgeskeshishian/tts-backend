<?php

namespace App\Orchid\Screens\Paddle;

use App\Orchid\Layouts\Paddle\PaddleApiKeyKeyLayout;
use App\Orchid\Layouts\Paddle\PaddleApiKeyVendorIdLayout;
use App\Models\PaddleApiKey;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class PaddleApiKeyEditScreen extends Screen
{
    private $type;

    /**
     * @param string $type
     * @return array
     */
    public function query(string $type): array
    {
        $this->type = $type;
        return [
            'key' => PaddleApiKey::where('type_key', $type)->first()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Edit Paddle Api Key';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return '';
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
                PaddleApiKeyKeyLayout::class
            ]),

            Layout::block([
                PaddleApiKeyVendorIdLayout::class
            ])->canSee($this->type == 'classic'),
        ];
    }

    /**
     * @param string $type
     * @param Request $request
     * @return RedirectResponse
     */
    public function save(string $type, Request $request): RedirectResponse
    {
        PaddleApiKey::where('type_key', $type)->update($request->get('key'));

        Toast::info(__('Paddle Api Key was saved'));

        return redirect()->route('platform.systems.paddle');
    }
}