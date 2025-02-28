<?php


namespace App\Http\Controllers\System;

use App\Models\Structure\DynamicTags;
use App\Http\Controllers\Api\ApiController;

class DynamicTagsController extends ApiController
{
    public function listView()
    {
        return view('admin.dynamic-tags.index', [
            'first' => DynamicTags::first(),
            'last' => DynamicTags::latest()->first(),
            'dynamicTags' => DynamicTags::count(),
        ]);
    }

    public function update()
    {
        DynamicTags::truncate();

        $csv = request()->file('dynamic-tags');
        if (!$csv) {
            return redirect()->back();
        }

        $data = array_map('str_getcsv', file($csv->getRealPath()));
        $data = array_map(function ($item) {
            return ['url' => $item[0]];
        }, $data);

        DynamicTags::insert($data);

        return redirect()->back();
    }
}
