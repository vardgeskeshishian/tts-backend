<?php


namespace App\Contracts;

use App\Models\Order;

interface OrderLifeServiceContract
{
    public const MINIMUM_ORDER_LIFE_FOR_UPDATE = 1;
    public const MAXIMUM_ORDER_LIFE_FOR_DELETE = 90 * 24;

    public function setOrder(Order $order): self;
    public function create(): self;
    public function delete(): self;
    public function sendUpdate(): self;

    public function getOrderLifeDiff(): int;
}
