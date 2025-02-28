<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsAuthor
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $user = auth()->user();
        $authorRole= $user->roles->where('id', 4)->first()?->id;
        if ($user && $authorRole) {
            return $next($request);
        } else {
            return response()->json([
                'message' => 'This user is not an author'
            ], 401);
        }
    }
}