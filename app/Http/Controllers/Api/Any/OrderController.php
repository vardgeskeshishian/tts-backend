<?php

namespace App\Http\Controllers\Api\Any;

use App\Http\Controllers\Api\ApiController;
use App\Models\Promocode;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;

/**
 * @group Order Management
 *
 * Class OrderController
 * @package App\Http\Controllers\Api\Authorized
 */
class OrderController extends ApiController
{
    /**
     * @var OrderService
     */
    private $service;

    public function __construct(OrderService $service)
    {
        parent::__construct();

        $this->service = $service;
    }

    /**
     * Change order item license
     *
     * @bodyParam order_id int
     * @bodyParam order_item_id int
     *
     * @return JsonResponse
     */
    public function changeItemLicense()
    {
        return $this->wrapCall($this->service, 'changeOrderItemLicense');
    }

    /**
     * Remove order item
     *
     * @bodyParam order_id int
     * @bodyParam order_item_id int
     *
     * @return JsonResponse
     */
    public function removeItem()
    {
        return $this->wrapCall($this->service, 'removeOrderItem');
    }

    public function paddleWebhook()
    {
        logs('telegram-debug')->debug('paddle-webhook', request()->all());

        return $this->success([
            'success' => true,
        ]);
    }

    public function assignPromocode()
    {
        $code = request('code');
        $orderId = request('order_id');

        $promocode = Promocode::where('code', $code)->first();
        $order = Order::where('id', $orderId)->first();

        if (!$promocode) {
            return $this->error('promocode not found', 'promocode not found');
        }

        if (!$order) {
            return $this->error('order not found', 'order not found');
        }

        return $this->wrapCall($this->service, 'assignPromocodeToOrder', $promocode, $order);
    }
}
