<?php


namespace App\Http\Controllers\System;


use App\Http\Controllers\Api\ApiController;

class RobotsController extends ApiController
{
    protected $fileName = "/home/admin/web/stage.taketones.com/public_html/public/robots.txt";

    public function listView()
    {
        $robots = file_get_contents($this->fileName);

        return view('admin.robots.index', [
            'robots' => $robots,
        ]);
    }

    public function update()
    {
        $robots = request()->post('robots');
        $robots = trim($robots);

        file_put_contents($this->fileName, $robots);

        return redirect()->back();
    }
}

