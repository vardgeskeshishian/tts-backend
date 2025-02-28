<?php

namespace App\Http\Controllers\Api\Authorized;

use App\Http\Controllers\Api\AuthorizedController;
use App\Models\Order;
use App\Services\PaddleService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;

/**
 * @group Order Management
 *
 * Class OrderController
 * @package App\Http\Controllers\Api\Authorized
 */
class OrderController extends AuthorizedController
{
    /**
     * @var OrderService
     */
    private $service;
    /**
     * @var PaddleService
     */
    private $checkoutService;

    public function __construct(
        OrderService $service,
        PaddleService $checkoutService
    ) {
        parent::__construct();

        $this->service = $service;
        $this->checkoutService = $checkoutService;
    }

    public function checkout(Order $order)
    {
        return $this->success($this->checkoutService->checkout($order));
    }

    /**
     * @return JsonResponse
     */
    public function getLatestFast()
    {
        return $this->wrapCall(OrderService::class, 'findLatestFast');
    }

    /**
     * Finish order (change status to finished)
     *
     * @bodyParam order_id required
     * @bodyParam order_hash string Dunno what to do with this yet
     *
     * @return JsonResponse
     */
    public function finish()
    {
        return $this->wrapCall($this->service, 'finish', request());
    }
}
