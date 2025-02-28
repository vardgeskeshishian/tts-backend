<?php

namespace App\Observers;

use App\Exceptions\OrderLifeNoUserException;
use App\Exceptions\OrderLifeOrderNotFullException;
use OrderLifeServiceFacade;
use App\Models\Order;
use App\Models\OrderItem;

class OrderItemObserver
{
    /**
     * Handle the OrderItem "created" event.
     *
     * @param OrderItem $orderItem
     *
     * @return void
     */
    public function created(OrderItem $orderItem)
    {
        try {
            OrderLifeServiceFacade::setOrder($orderItem->order)->sendUpdate();
        } catch (OrderLifeOrderNotFullException | OrderLifeNoUserException $e) {
        }
    }

    /**
     * Handle the OrderItem "updated" event.
     *
     * @param OrderItem $orderItem
     *
     * @return void
     */
    public function updated(OrderItem $orderItem)
    {
        $order = Order::find($orderItem->getOriginal('order_id'));

        try {
            OrderLifeServiceFacade::setOrder($order)->sendUpdate();
        } catch (OrderLifeOrderNotFullException | OrderLifeNoUserException $e) {
        }
    }
}
