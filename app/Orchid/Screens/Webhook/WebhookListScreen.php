<?php

namespace App\Orchid\Screens\Webhook;

use App\Models\Paddle\Webhook\Webhook;
use App\Orchid\Layouts\Webhooks\WebhookListLayout;
use Orchid\Screen\Screen;

class WebhookListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'webhooks' => Webhook::orderByDesc('id')
                ->paginate(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Webhooks List';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'Complete list of all webhooks.';
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

        ];
    }

    /**
     * @return iterable
     */
    public function layout(): iterable
    {
        return [
            WebhookListLayout::class
        ];
    }
}