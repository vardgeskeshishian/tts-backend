<?php

namespace App\Http\Controllers\Api;

use App\Models\User;

/**
 * Class ApiController
 *
 * @package App\Http\Controllers\Api
 */
class AuthorizedController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return User|null
     */
    public function user()
    {
        return auth()->check() ? auth()->user() : null;
    }
}
