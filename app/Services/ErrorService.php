<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;

class ErrorService
{
    /**
     * @param $message
     * @param $params
     *
     * @throws Exception
     */
    public function logError($message, $params = [])
    {
        $id = uniqid();

        $message = "Prefix {$id} - {$message}";

        if (env('APP_DEBUG')) {
            throw new Exception($message);
        }

        Log::debug($message, $params);
    }
}
