<?php


namespace App\Http\Controllers\System;

use App\Services\MainPageService;
use App\Constants\MainPageConstants;
use App\Http\Controllers\Api\ApiController;

class MainPageController extends ApiController
{
    public function viewMainPage($type = MainPageConstants::TYPE_ROOT)
    {
        $types = [
            MainPageConstants::TYPE_VFX => true,
            MainPageConstants::TYPE_ROOT => true,
            MainPageConstants::TYPE_SFX => true,
        ];

        if (!isset($types[$type])) {
            return redirect()->back()->with(['errors' => 'no such page']);
        }

        $menu = [
            MainPageConstants::TYPE_ROOT => [
                'url' => '',
                'active' => $type === MainPageConstants::TYPE_ROOT,
            ],
            MainPageConstants::TYPE_VFX => [
                'url' => '/' . MainPageConstants::TYPE_VFX,
                'active' => $type === MainPageConstants::TYPE_VFX,
            ],
            MainPageConstants::TYPE_SFX => [
                'url' => '/' . MainPageConstants::TYPE_SFX,
                'active' => $type === MainPageConstants::TYPE_SFX,
            ],
        ];

        $inputs = [];

        return view('admin.main-page.main-page', compact('type', 'menu', 'inputs'));
    }
}
