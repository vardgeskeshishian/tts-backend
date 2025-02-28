<?php


namespace App\Http\Controllers\System;

use App\Http\Controllers\Api\ApiController;
use JetBrains\PhpStorm\NoReturn;

class MonitorController extends ApiController
{
    #[NoReturn] public function view()
    {
        $res = shell_exec("cat ~/.pm2/logs/npm-out.log");

        dd($res);
    }
}
