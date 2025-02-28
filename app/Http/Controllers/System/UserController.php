<?php


namespace App\Http\Controllers\System;

use App\Exceptions\OrderLifeNoUserException;
use App\Exceptions\OrderLifeOrderNotFullException;
use OrderLifeServiceFacade;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use App\Constants\Env;
use App\Models\License;
use App\Models\OrderItem;
use App\Models\Libs\Role;
use App\Exports\UsersExport;
use App\Constants\FinancesEnv;
use App\Models\Partner\Partner;
use App\Models\Finance\Balance;
use App\Models\UserSubscription;
use App\Services\UserRoleService;
use App\Constants\SubscriptionEnv;
use App\Models\Authors\AuthorProfile;
use App\Http\Controllers\Api\ApiController;
use App\Services\System\Users\ActionsService;

class UserController extends ApiController
{
    public function listView()
    {
        $q = request('q');
        $users = User::with('finishedOrders')
            ->when($q, fn ($query) => $query->where('email', 'like', "%$q%")->orWhere('id', $q))
            ->paginate();

        $ordersInfo = [];

        foreach ($users->items() as $user) {
            $userId = $user->id;

            $data = [
                'user_id' => $userId,
                'type' => Env::ORDER_TYPE_FULL,
                'status' => Env::STATUS_NEW,
            ];

            $cartLength = 0;
            $orderLifeDiff = 0;

            $cart = Order::where($data)->whereHas('items')->latest()->first();
            if ($cart) {
                $cartLength = $cart->items->count();

                try {
                    $orderLifeDiff = OrderLifeServiceFacade::setOrder($cart)->getOrderLifeDiff();
                } catch (OrderLifeNoUserException | OrderLifeOrderNotFullException $e) {
                }
            }


            $ordersInfo[$userId] = [
                'cart_length' => $cartLength,
                'orders_length' => $user->finishedOrders->count(),
                'cart_life' => $orderLifeDiff,
            ];
        }

        return view('admin.users.list', [
            'users' => $users,
            'ordersInfo' => $ordersInfo,
        ]);
    }

    public function profileView(ActionsService $actionsService, $userId)
    {
        $user = User::find($userId);

        $data = [
            'user_id' => $userId,
            'type' => Env::ORDER_TYPE_FULL,
            'status' => Env::STATUS_NEW,
        ];

        $cart = Order::where($data)->whereHas('items')->latest()->first();

        $orders = Order::where([
            'user_id' => $userId,
            'status' => Env::STATUS_FINISHED,
        ])->with('items')->get();

        $ordersList = collect();

        foreach ($orders as $order) {
            /**
             * @var $item OrderItem
             */
            foreach ($order->items as $item) {
                if (!$item->track) {
                    continue;
                }

                $ordersList->push([
                    'order_id' => $order->id,
                    'date' => $item->updated_at,
                    'license_number' => $item->license_number,
                    'type' => $item->license->type,
                    'track' => [
                        'name' => optional($item->track)->full_name,
                    ],
                ]);
            }
        }

        $ordersList = $ordersList->sortByDesc('date');

        $profiles = AuthorProfile::all();

        return view('admin.users.profile', [
            'user' => $user,
            'authorProfiles' => $user->isAuthor() ? $user->getAuthor()->profiles : collect(),
            'cart' => $cart,
            'downloads' => $ordersList,
            'profiles' => $profiles,
            'actions' => $actionsService->getActions(),
        ]);
    }

    public function confirm($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return redirect()->back();
        }

        $user->confirmed = true;
        $user->save();

        return redirect()->back();
    }

    public function delete($userId)
    {
        User::destroy($userId);

        return redirect()->back();
    }

    public function exportUsersCSV()
    {
        return (new UsersExport());
    }

    public function partner($userId)
    {
        $pp_account = request()->get('paypal_account');

        Partner::updateOrCreate([
            'user_id' => $userId,
        ], [
            'user_id' => $userId,
            'paypal_account' => $pp_account,
            'status' => Partner::STATUS_NEW,
        ]);

        return redirect()->back();
    }

    public function setNewPassword(User $user)
    {
        $user->password = bcrypt(request()->input('password'));
        $user->save();

        return redirect()->back();
    }

    public function changeSubscription(User $user)
    {
        $license = License::find(request()->get('license_id'));
        UserSubscription::updateOrCreate([
            'user_id' => $user->id,
        ], [
            'user_id' => $user->id,
            'license_id' => $license->id,
            'plan_id' => $license->recurrent->first()->id,
            'expiring_at' => Carbon::now()->addMonth(),
            'status' => SubscriptionEnv::STATUS_ACTIVE,
        ]);

        return redirect()->back();
    }

    public function apiChangePaymentSettings(User $user)
    {
        $paypal = request()->input('paypal');
        $payoneer = request()->input('payoneer');

        $user->paypal_account = $paypal ?: null;
        $user->payoneer_account = $payoneer ?: null;
        $user->save();

        Balance::where('date', Carbon::now()->format(FinancesEnv::BALANCE_DATE_FORMAT))
            ->where('status', 'awaiting')
            ->where('user_id', $user->id)
            ->update([
                'payment_type' => $paypal ? 'paypal' : 'payoneer',
                'payment_email' => $paypal ?: $payoneer,
            ]);

        return redirect()->back();
    }

    public function apiSwitchPartnerStatus(User $user)
    {
        $partner = Partner::whereUserId($user->id)->first();

        if (!$partner) {
            return redirect()->back();
        }

        $status = $partner->status;

        $newStatus = match ($status) {
            Partner::STATUS_ACTIVATED => Partner::STATUS_DEACTIVATED,
            default => Partner::STATUS_ACTIVATED,
        };

        $partner->status = $newStatus;
        $partner->save();

        return redirect()->back();
    }

    public function apiDeactivateAuthor(UserRoleService $roleService, User $user)
    {
        if (!$user->isAuthor()) {
            return redirect()->back();
        }

        $roleService->assignRoleToUser($user, Role::ROLE_USER);

        return redirect()->back();
    }
}
