<?php

namespace App\Observers;

use App\Constants\Env;
use App\Exceptions\OrderLifeNoUserException;
use App\Exceptions\OrderLifeOrderNotFullException;
use OrderLifeServiceFacade;
use App\Models\Order;
use App\Services\LicenseNumberService;
use Exception;

class OrderObserver
{
    /**
     * Handle the order "created" event.
     *
     * @param Order $order
     * @return void
     */
    public function created(Order $order)
    {
        try {
            OrderLifeServiceFacade::setOrder($order)->create()->sendUpdate();
        } catch (OrderLifeOrderNotFullException | OrderLifeNoUserException $e) {
        }
    }

    /**
     * Handle the order "updated" event.
     *
     * @param Order $order
     * @return void
     * @throws Exception
     */
    public function updated(Order $order): void
    {
        if ($order->status === Env::STATUS_FINISHED
            && $order->isDirty('status')
            && $order->getOriginal('status') !== Env::STATUS_FINISHED) {
            /**
             * @var $numberService LicenseNumberService
             */
            $numberService = resolve(LicenseNumberService::class);

            foreach ($order->items as $item) {
                if ($item->license_number) {
                    continue;
                }

                $item->incrementSales();
                $item->license_number = $numberService->generate($item->license);
                $item->save();
            }
        }

        try {
            OrderLifeServiceFacade::setOrder($order)->sendUpdate();
        } catch (OrderLifeOrderNotFullException | OrderLifeNoUserException $e) {
        }
    }

    /**
     * Handle the order "deleted" event.
     *
     * @param Order $order
     * @return void
     */
    public function deleted(Order $order)
    {
        try {
            OrderLifeServiceFacade::setOrder($order)->delete()->sendUpdate();
        } catch (OrderLifeOrderNotFullException | OrderLifeNoUserException $e) {
        }
    }
}
