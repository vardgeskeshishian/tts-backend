<?php


namespace App\Http\Controllers\System;

use App\Exports\TracksActivityExport;
use App\Exports\UsersActivityExport;
use App\Http\Controllers\Api\ApiController;

class StatisticsController extends ApiController
{
    public function indexView()
    {
        return view('admin.statistics.index');
    }

    public function usersExport()
    {
        ini_set('max_execution_time', 240);
        return (new UsersActivityExport(request('date_from'), request('date_to')));
    }

    public function tracksExport()
    {
        return (new TracksActivityExport(request('date_from'), request('date_to')));
    }
}
