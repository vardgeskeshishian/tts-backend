<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AllowedInAdmin
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
        $user = auth()->user();
        $adminRole= $user->roles->where('id', 1)->first()?->id;
        if ($user && $adminRole) {
            return $next($request);
        } else {
            return response()->json([
                'message' => 'This user is not an administrator'
            ], 401);
        }
    }
}
