<?php

namespace App\Services;

use App\Constants\Env;
use App\Constants\ErrorCodes;
use App\Http\Resources\Api\OrderResource;
use App\Models\License;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Promocode;
use App\Models\SFX\SFXPack;
use App\Models\SFX\SFXTrack;
use App\Models\Track;
use App\Models\TrackPrice;
use App\Models\User;
use App\Models\VideoEffects\VideoEffect;
use App\Services\DTO\TransactionDTO;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Ramsey\Uuid\Uuid;

class OrderService
{
    use ValidatesRequests;

    /**
     * @var AnalyticsService
     */
    private $analyticsService;
    /**
     * @var PaddleService
     */
    private $checkoutService;
    /**
     * @var VideoEffect
     */
    private VideoEffect $item;
    private string $itemType;

    public function __construct(
        AnalyticsService $analyticsService,
        PaddleService    $checkoutService
    )
    {
        $this->analyticsService = $analyticsService;
        $this->checkoutService = $checkoutService;
    }

    public function assignOrder($sessionId, $userId)
    {
        $newOrder = $this->findExistingOrder($sessionId);

        if (!$newOrder) {
            return;
        }

        $oldOrder = $this->findExistingOrder();

        if ($oldOrder === null) {
            $oldOrder = $newOrder->replicate();

            logs('telegram-debug')->info("assigning order", [
                'session_id' => $sessionId,
                'order_id' => $oldOrder->id ?? null,
            ]);
        }

        $oldOrder->user_id = $userId;
        $oldOrder->session_id = $sessionId;
        $oldOrder->save();

        foreach ($newOrder->items as $item) {
            $item->order_id = $oldOrder->id;
            $item->save();
        }

        $newOrder->delete();
    }

    /**
     * @param null $sessionId
     *
     * @return Order|false|null
     * @throws Exception
     */
    public function findExistingOrder($sessionId = null)
    {
        if (!auth()->check() && !$sessionId) {
            return false;
        }

        $data = [
            'user_id' => auth()->id(),
            'type' => Env::ORDER_TYPE_FULL,
            'status' => Env::STATUS_NEW,
        ];

        if ($sessionId) {
            $data['session_id'] = $sessionId;

            unset($data['user_id']);

            return Order::where($data)->latest()->first();
        }

        $order = Order::where($data)->latest()->first();

        /**
         * @var $user User
         */
        $user = auth()->user();

        $lastFinished = $user->finishedOrders->last();

        if (!$order) {
            return $this->createOrder(Env::ORDER_TYPE_FULL);
        }

        if (($lastFinished && ($lastFinished->updated_at < $order->updated_at)) || $order->items->count() > 0) {
            return $order;
        }

        if ($lastFinished && ($lastFinished->updated_at > $order->updated_at)) {
            return $this->createOrder(Env::ORDER_TYPE_FULL);
        }

        return $order;
    }

    /**
     * if we have guest user, who is adding items to order
     *
     * @param string $type
     *
     * @return mixed
     * @throws Exception
     */
    protected function createOrder(string $type)
    {
        $orderData = [
            'user_id' => auth()->id(),
            'type' => $type,
            'status' => Env::STATUS_NEW,
        ];

        if (!auth()->check()) {
            $orderData['session_id'] = request()->hasHeader('session-id')
                ? request()->header('session-id')
                : (string)Uuid::uuid4();

            unset($orderData['user_id']);
        }

        return Order::create($orderData)->refresh();
    }

    /**
     * @param Track $track
     *
     * @return OrderResource
     * @throws ValidationException
     * @throws Exception
     */
    public function fast(Track $track)
    {
        $this->validate(request(), [
            'license_id' => 'required',
        ]);

        $this->clearUserNotFinishedFastOrders();
        $order = $this->createOrder(Env::TYPE_FAST);

        $this->addOrderItem($order, $track);

        /**
         * @var $order Order
         */
        $order = $order->refresh();

        return $this->checkoutService->checkout($order);
    }

    /**
     * removing previous fast orders that were created, but not finished
     * cause there should be only one for user
     *
     * @return mixed
     */
    protected function clearUserNotFinishedFastOrders()
    {
        return Order::where([
            'user_id' => auth()->id(),
            'type' => Env::TYPE_FAST,
            'status' => Env::STATUS_NEW,
        ])->delete();
    }

    /**
     * @param Order $order
     *
     * @param Track $track
     *
     * @return OrderResource
     * @throws Exception
     */
    public function addOrderItem(Order $order, Track $track)
    {
        if (!$order) {
            throw new Exception("[OrderService]: Order is null", 500);
        }

        if (!$track || $track->id === 0) {
            throw new ModelNotFoundException("[OrderService]: Track not found", 404);
        }

        $orderItem = $this->createOrderItem($order, $track->id, request('license_id'));

        $this->analyticsService->sendAddToCart($orderItem);

        return (new OrderResource($order->fresh()))->additional($this->buildAdditionalOrderItemInformation($order->promocodeObject, $orderItem));
    }

    /**
     * creates order item; simple as that.
     *
     * @param Order $order
     * @param int $trackId
     * @param int $licenseId
     *
     * @return OrderItem
     * @throws Exception
     */
    public function createOrderItem(Order $order, int $trackId, $licenseId)
    {
        $track = Track::where('id', $trackId)->first();

        /**
         * @var $license License
         */
        $license = License::where('id', $licenseId)->first();

        if (!$license) {
            throw new ModelNotFoundException("[OrderService]: license was not found");
        }

        $prices = $track->prices;
        $localPrice = $prices->where('license_id', $licenseId)->first();

        if ($license->info instanceof Collection) {
            $price = 0;
        } else {
            $price = $license->info->price;
            $discount = $license->info->discount;

            if ($discount && $discount > 0) {
                $price = $discount;
            }

            if ($localPrice) {
                $price = $localPrice->price;
            }
        }

        return OrderItem::create([
            'order_id' => $order->id,
            'track_id' => $trackId,
            'license_id' => $licenseId,
            'track_copy' => "[]",
            'license_copy' => "[]",
            'price' => $price,
            'item_type' => Env::ITEM_TYPE_TRACKS,
            'item_id' => $trackId,
        ]);
    }

    private function buildAdditionalOrderItemInformation(?Promocode $promoCode, OrderItem $item): array
    {
        $dto = new TransactionDTO(request());
        $price = $promoCode ? $promoCode->returnPriceWithDiscountForOrderItem($item) : $item->price;
        $price = $dto->calculateCleanPrice($price);

        return [
            'id' => $item->id,
            'name' => $item->getAnalyticsName(),
            'price' => $price,
            'coupon' => $promoCode->code ?? '',
            'quantity' => 1,
        ];
    }

    /**
     * @param Model|SFXPack|SFXTrack $entity
     * @param License $license
     *
     * @return OrderResource
     * @throws Exception
     */
    public function fastForSFX(Model $entity, License $license)
    {
        $this->clearUserNotFinishedFastOrders();
        $order = $this->createOrder(Env::TYPE_FAST);

        $this->addSFXOrderItem($order, $entity, $license);

        /**
         * @var $order Order
         */
        $order = $order->refresh();

        return $this->checkoutService->checkout($order);
    }

    /**
     * @param Order $order
     * @param Model|SFXTrack|SFXPack $entity
     * @param License $license
     *
     * @return OrderResource
     * @throws Exception
     */
    public function addSFXOrderItem(Order $order, Model $entity, License $license)
    {
        if (!$order) {
            throw new Exception("[OrderService]: Order is null", 500);
        }

        if ($entity->id === 0 || !in_array($entity->getMorphClass(), [SFXPack::class, SFXTrack::class])) {
            throw new ModelNotFoundException("[OrderService]: {$entity->getMorphClass()} not found", 404);
        }

        $orderItem = $this->createOrderSFXItem($order, $entity, $license);

        $this->analyticsService->sendAddToCart($orderItem);

        return (new OrderResource($order))->additional($this->buildAdditionalOrderItemInformation($order->promocodeObject, $orderItem));
    }

    /**
     * @param Order $order
     * @param Model|SFXPack|SFXTrack $item
     * @param License $license
     *
     * @return OrderItem|Model
     * @throws Exception
     */
    public function createOrderSFXItem(Order $order, Model $item, License $license)
    {
        return OrderItem::create([
            'order_id' => $order->id,
            'license_id' => $license->id,
            'track_copy' => "[]",
            'license_copy' => "[]",
            'price' => $item instanceof SFXTrack ? $license->sfx->price : $license->sfx->pack_price,
            'item_type' => $item instanceof SFXTrack ? Env::ITEM_TYPE_EFFECTS : Env::ITEM_TYPE_PACKS,
            'item_id' => $item->id,
        ]);
    }

    /**
     * @param Track $track
     *
     * @return OrderResource
     * @throws ValidationException
     * @throws Exception
     */
    public function full(Track $track)
    {
        $this->validate(request(), [
            'license_id' => 'required',
        ]);

        $order = $this->findOrCreateFullOrder();

        return $this->addOrderItem($order, $track);
    }

    /**
     * @return Order
     * @throws Exception
     */
    public function findOrCreateFullOrder()
    {
        $sessionId = null;

        if (request()->hasHeader('session-id')) {
            $sessionId = request()->header('session-id');
        }

        $order = $this->findExistingOrder($sessionId);

        if ($order) {
            if (!$order->user_id && auth()->check()) {
                $order->user_id = auth()->id();
                $order->session_id = "";
                $order->save();
            }

            return $order;
        }

        $order = $this->createOrder(Env::ORDER_TYPE_FULL);

        if (!$order) {
            throw new Exception("[OrderService]: Can't create order", ErrorCodes::CANT_CREATE_ORDER);
        }

        return $order;
    }

    /**
     * Returns cart (latest new full order)
     *
     * @return OrderResource|array
     */
    public function cart()
    {
        $user = auth()->user();

        $sessionId = null;

        if (!$user && request()->hasHeader('session-id')) {
            $sessionId = request()->header('session-id');
        }

        if (!$user && !$sessionId) {
            return [];
        }

        $data = [
            'user_id' => optional($user)->id,
            'type' => Env::ORDER_TYPE_FULL,
            'status' => Env::STATUS_NEW,
        ];

        if ($sessionId) {
            $data['session_id'] = $sessionId;

            unset($data['user_id']);
        }

        $order = Order::where($data)->latest()->first();

        if (!$order) {
            return [];
        }

        if (!$user) {
            return new OrderResource($order);
        }

        $lastFinished = $user->finishedOrders->last();

        if (($lastFinished && ($lastFinished->updated_at < $order->updated_at)) || $order->items->count() > 0) {
            return new OrderResource($order);
        }

        if ($lastFinished && ($lastFinished->updated_at > $order->updated_at)) {
            return [];
        }

        return new OrderResource($order);
    }

    /**
     * @return OrderResource
     * @throws Exception
     */
    public function finish()
    {
        $orderId = request('order_id');

        $order = Order::find($orderId);

        if (!$order) {
            throw new ModelNotFoundException("[OrderService]: No order was found");
        }

        if (!auth()->check() || $order->user_id !== auth()->id()) {
            throw new Exception("[OrderService]: Order is not yours", 400);
        }

        return new OrderResource($order);
    }

    public function removeOrderItem()
    {
        $orderId = request('order_id');
        $orderItemId = request('order_item_id');

        $order = Order::find($orderId);

        $this->checkOrder($order);

        $orderItem = OrderItem::find($orderItemId);

        if (!$orderItem) {
            throw new ModelNotFoundException("[OrderService]: item not found", 404);
        }

        $this->checkOrderItem($orderItem, $order);

        $orderItem->order_id = null;
        $orderItem->save();

        // update order status
        if ($order->refresh()->items->count() === 0 && auth()->check()) {
            $promocodeObject = $order->promocodeObject;

            $order->promocode = null;
            $order->save();

            if ($promocodeObject) {
                $promocodeObject->uses_left += 1;
                $promocodeObject->save();
            }
        }

        $this->analyticsService->sendRemoveFromCart($orderItem);

        return (new OrderResource($order))->additional($this->buildAdditionalOrderItemInformation($order->promocodeObject, $orderItem));
    }

    /**
     * @param Order $order
     *
     * @throws Exception
     */
    protected function checkOrder(Order $order)
    {
        if (!$order) {
            throw new ModelNotFoundException("[OrderService]: Order not found", 404);
        }

        if (request()->hasHeader('session-id') && !auth()->check() && $order->session_id !== request()->header('session-id')) {
            throw new Exception("[OrderService]: Order is not yours", 400);
        }

        if (auth()->check() && $order->user_id !== auth()->id()) {
            throw new Exception("[OrderService]: Order is not yours", 400);
        }

        if ($order->status === Env::STATUS_FINISHED) {
            throw new Exception("[OrderService]: Order is finished: can't update", 400);
        }
    }

    /**
     * @param OrderItem $orderItem
     * @param Order $order
     *
     * @throws Exception
     */
    protected function checkOrderItem(OrderItem $orderItem, Order $order)
    {
        if (!$orderItem) {
            throw new ModelNotFoundException("[OrderService]: Order Item not found", 404);
        }

        if ($orderItem->order_id !== $order->id) {
            throw new Exception("[OrderService]: Item doesn't belongs to this order", 400);
        }
    }

    /**
     * Changing order item and return Order
     *
     * @throws Exception
     */
    public function changeOrderItemLicense()
    {
        $orderId = request('order_id');
        $orderItemId = request('order_item_id');
        $licenseId = request('license_id');

        $order = Order::find($orderId);

        $this->checkOrder($order);

        $orderItem = OrderItem::find($orderItemId);

        $this->checkOrderItem($orderItem, $order);

        $license = License::find($licenseId);

        $this->checkLicense($license);

        $trackPrice = TrackPrice::where([
            'track_id' => $orderItem->track_id,
            'license_id' => $license->id,
        ])->first();

        $price = $license->info->price;
        $discount = $license->info->discount;

        if ($discount && $discount > 0) {
            $price = $discount;
        }

        if ($trackPrice) {
            $price = $trackPrice->price;
        }

        if ($orderItem->item_type === Env::ITEM_TYPE_PACKS) {
            $price = $license->info->pack_price;
        }

        $orderItem->price = $price;
        $orderItem->license_id = $license->id;
        $orderItem->save();

        return new OrderResource($order);
    }

    protected function checkLicense(License $license)
    {
        if (!$license) {
            throw new ModelNotFoundException("[OrderService]: License not found", 404);
        }
    }

    public function findLatestFast()
    {
        $data = [
            'user_id' => auth()->id(),
            'status' => Env::STATUS_NEW,
            'type' => Env::TYPE_FAST,
        ];

        $order = Order::where($data)->first();

        if (!$order) {
            return [];
        }

        return new OrderResource($order);
    }

    public function assignPromocodeToOrder(Promocode $promocode, Order $order)
    {
        $info = [
            'promocode_info' => [
                'data' => [
                    'promocode' => $promocode->code,
                    'was_applied' => false,
                    'message' => '',
                    'prices' => [],
                    'total' => [],
                ],
            ],
        ];

        if ($order->promocode && $order->promocode_object) {
            $info['promocode_info']['data']['message'] = "The promocode {$promocode->code} can't be applied. Promocode {$order->promocode} is in use.";

            return $info;
        }

        if ($promocode->uses_left === 0 && $promocode->uses_allowed > 0) {
            $info['promocode_info']['data']['message'] = "The promocode {$promocode->code} can't be applied. No uses left.";

            return $info;
        }

        if ($promocode->expiring_at !== "0000-00-00 00:00:00" && $promocode->expiring_at <= Carbon::now()) {
            $info['promocode_info']['data']['message'] = "The promocode {$promocode->code} can't be applied. Expired.";

            return $info;
        }

        $promocode->uses_left -= 1;
        $order->promocode = $promocode->code;

        $order->save();
        $order->refresh();
        $promocode->save();

        return new OrderResource($order);
    }

    public function setItem(VideoEffect $videoEffect, string $itemType)
    {
        $this->item = $videoEffect;
        $this->itemType = $itemType;

        return $this;
    }

    public function addItem()
    {
        $order = $this->findOrCreateFullOrder();

        $license = License::find(request()->get('license_id'));

        $price = $this->calculateItemPrice();

        $orderItem = $order->items()->create([
            'license_id' => $license->id,
            'track_copy' => "[]",
            'license_copy' => "[]",
            'price' => $price,
            'item_type' => $this->itemType,
            'item_id' => $this->item->id,
        ]);

        $this->analyticsService->sendAddToCart($orderItem);

        return (new OrderResource($order->fresh()))
            ->additional($this->buildAdditionalOrderItemInformation($order->promocodeObject, $orderItem));
    }


    protected function calculateItemPrice($priceType = null)
    {
        $priceType = $priceType ?? request('price_type');

        if ($this->itemType == Env::ITEM_TYPE_VIDEO_EFFECTS) {
            $priceType = sprintf("price_%s", $priceType);
            return $this->item[$priceType];
        }
    }
}
