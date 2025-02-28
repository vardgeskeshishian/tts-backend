<?php


namespace App\Http\Controllers\System;

use App\Models\Options;
use App\Http\Controllers\Api\ApiController;

class OptionsController extends ApiController
{
    public function listView()
    {
        $options = Options::all();

        return view('admin.options.list', [
            'list' => $options
        ]);
    }
}
