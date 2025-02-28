<?php

namespace App\Orchid\Screens\Paddle;

use App\Orchid\Layouts\Paddle\PaddleApiKeyListLayout;
use App\Models\PaddleApiKey;
use Orchid\Screen\Screen;

class PaddleApiKeyListScreen extends Screen
{
    /**
     * @return array
     */
    public function query(): array
    {
        return [
            'keys' => PaddleApiKey::get()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'List Paddle Api Key';
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
     * @return iterable
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * @return iterable
     */
    public function layout(): iterable
    {
        return [
            PaddleApiKeyListLayout::class
        ];
    }
}