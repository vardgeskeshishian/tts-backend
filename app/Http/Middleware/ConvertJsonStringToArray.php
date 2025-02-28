<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ConvertJsonStringToArray
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
//        dd($request->);
        return $next($request);
    }
}
