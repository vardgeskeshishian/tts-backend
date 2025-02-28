<?php


namespace App\Http\Controllers\System;

use Illuminate\Http\Request;
use App\Models\UserSubscription;
use App\Http\Controllers\Api\ApiController;

class SubscriptionController extends ApiController
{
    public function getListView(Request $request)
    {
        $totals = [
            'total' => UserSubscription::all()->count(),
            'active' => UserSubscription::where('status', 'active')->count(),
            'past_due' => UserSubscription::where('status', 'past_due')->count(),
            'deleted' => UserSubscription::where('status', 'deleted')->count()
        ];

        $subscriptions = UserSubscription::when($request->has('only'), fn ($q) => $q->where('status', $request->get('only')))
            ->with('user', 'license', 'plan', 'history')
            ->paginate();

        return view('admin.subscriptions.index', [
            'totals' => $totals,
            'list' => $subscriptions,
        ]);
    }
}
