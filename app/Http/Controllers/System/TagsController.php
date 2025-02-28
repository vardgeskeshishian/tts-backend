<?php


namespace App\Http\Controllers\System;

use Illuminate\Support\Str;
use App\Services\TaggingService;
use App\Http\Controllers\Api\ApiController;

class TagsController extends ApiController
{
    public function listView(TaggingService $service)
    {
        $categories = $service->getAll(false, true);

        foreach ($categories as $key => $items) {
            unset($categories[$key]);

            $categories[Str::slug($key)] = $items;
        }

        return view('admin.tags.list', compact('categories'));
    }

    public function updatePositions(TaggingService $service, $type)
    {
        $service->updatePositions(request(), $type);

        return $this->success();
    }
}
