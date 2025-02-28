<?php

namespace App\Http\Middleware;

use App\Models\UserActivityLog;
use Closure;
use Illuminate\Http\Request;

class After
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        try {
//            UserActivityLog::addRecord($request);
        } catch (\Exception $e) {
            logs('debug')->error("user-activity-log:add-record", [
                'msg' => $e->getMessage(),
                'code' => $e->getCode(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace()
            ]);
        }

        return $response;
    }
}
