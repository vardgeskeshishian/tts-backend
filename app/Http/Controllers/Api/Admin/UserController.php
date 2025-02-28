<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Any\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserController extends ApiController
{
    public function get(): JsonResponse
    {
        $users = User::with('roles')->paginate(100);
        $usersItems = collect($users->items())->map(fn($user) => new UserResource($user));
        return response()->json([
            'items' => $usersItems,
            'current_page' => $users->currentPage(),
            'next_page_url' => $users->nextPageUrl(),
            'path' => $users->path(),
            'per_page' => $users->perPage(),
            'prev_page_url' => $users->previousPageUrl(),
            'to' => $users->lastItem(),
            'total' => $users->total()
        ]);
    }
}
