<?php


namespace App\Services\System\Users;

use App\Services\System\Users\Handlers\CartActionsHandler;
use App\Services\System\Users\Handlers\OrdersActionHandler;
use App\Services\System\Users\Handlers\PasswordActionHandler;
use App\Services\System\Users\Handlers\GeneralActionsHandler;
use App\Services\System\Users\Handlers\SubscriptionActionHandler;

class ActionsService
{
    const ACTIONS = [
        'general' => GeneralActionsHandler::class,
        'password' => PasswordActionHandler::class,
        'subscription' => SubscriptionActionHandler::class,
        'orders' => OrdersActionHandler::class,
        'cart' => CartActionsHandler::class,
    ];

    public function getActions()
    {
        return array_keys(self::ACTIONS);
    }
}
