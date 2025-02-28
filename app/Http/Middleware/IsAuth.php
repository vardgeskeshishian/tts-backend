<?php

namespace App\Http\Middleware;

use Closure;

class IsAuth
{
    public function handle($request, Closure $next)
    {
        $user = auth()->user();
        if ($user)
            return $next($request);
        else
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
    }
}