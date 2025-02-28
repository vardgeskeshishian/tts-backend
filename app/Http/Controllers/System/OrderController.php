<?php


namespace App\Http\Controllers\System;

use App\Models\User;
use App\Models\Order;
use App\Models\License;
use App\Models\OrderItem;
use App\Services\OrderService;
use App\Constants\Env;
use App\Constants\LicenseConstants;
use App\Http\Controllers\Api\ApiController;

class OrderController extends ApiController
{
    public function listView()
    {
        $q = request('q');

        if ($q) {
            $orders = Order::with('life')->where(function ($query) use ($q) {
                return $query->where('id', $q)->orWhereHas('user', function ($query) use ($q) {
                    return $query->where('email', 'like', '%' . $q . '%');
                });
            })->paginate();
        } else {
            $orders = Order::with('life')->paginate();
        }

        return view('admin.orders.list', [
            'orders' => $orders,
        ]);
    }

    public function orderView($order)
    {
        $order = Order::withTrashed()->find($order);
        $items = $order->items;

        $licenses = License::where([
            'payment_type' => LicenseConstants::STANDARD_LICENSE,
        ])->get();

        if (request()->has('asJson')) {
            return response()->json($order->load('items', 'user')->makeVisible(['items']));
        }

        return view('admin.orders.order', [
            'order' => $order,
            'items' => $items,
            'licenses' => $licenses,
        ]);
    }

    public function annulOrder()
    {
        $orderId = null;

        if (request()->has('order_item_id')) {
            $orderItemId = request('order_item_id');
            $item = OrderItem::find($orderItemId);
            $item->order_id = null;
            $item->save();
        } else {
            $orderId = request('order_id');

            Order::destroy($orderId);
        }

        return redirect()->back();
    }

    public function assignOrder()
    {
        $orderId = request('order_id');
        $order = Order::withTrashed()->find($orderId);

        if (!$order || $order->deleted_at) {
            return redirect()->back()->withErrors('errors', !$order ? "Order {$orderId} not found" : "Order {$orderId} is deleted");
        }

        $email = request('user_email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            return redirect()->back()->withErrors([
                "User {$email} not found",
            ]);
        }

        $order->user_id = $user->id;
        $order->session_id = null;
        $order->status = Env::STATUS_FINISHED;
        $order->save();

        return redirect('/system/misc/orders/' . $order->id);
    }

    public function addTracksToOrder()
    {
        $orderId = request('order_id');
        $itemId = request('track_id');
        $licensId = request('license_id');

        /**
         * @var $service OrderService
         */
        $service = resolve(OrderService::class);

        $order = Order::find($orderId);
        if (!$order) {
            return redirect()->back();
        }

        $service->createOrderItem($order, $itemId, $licensId);

        return redirect()->back();
    }
}
