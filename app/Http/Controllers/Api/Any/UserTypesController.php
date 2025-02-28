<?php

namespace App\Http\Controllers\Api\Any;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Api\UserTypeResource;
use App\Models\UserType;

class UserTypesController extends ApiController
{
    protected $resource = UserTypeResource::class;

    public function get()
    {
        return $this->pagination(UserType::class, $this->resource);
    }
}
