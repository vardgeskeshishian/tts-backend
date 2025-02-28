<?php


namespace App\Http\Controllers\Api\Any;


use App\Http\Controllers\Api\ApiController;

class RobotsController extends ApiController
{
    public function get()
    {
        $robots = file_get_contents(base_path()."/public_html/robots.txt");

        return $robots;
    }
}

