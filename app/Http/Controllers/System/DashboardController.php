<?php

namespace App\Http\Controllers\System;

use App\Models\SyncStatistic;
use App\Http\Controllers\Api\ApiController;

class DashboardController extends ApiController
{
    public function index()
    {
        $syncStatistic = SyncStatistic::first();

        return view('admin.dashboard', compact('syncStatistic'));
    }
}
