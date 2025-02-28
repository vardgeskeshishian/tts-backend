<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Api\UserTypeResource;
use App\Models\UserType;
use App\Services\UserTypeService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserTypesController extends ApiController
{
    protected $resource = UserTypeResource::class;
    /**
     * @var UserTypeService
     */
    private $service;

    public function __construct(UserTypeService $service)
    {
        parent::__construct();

        $this->service = $service;
    }
    public function get(): LengthAwarePaginator|AnonymousResourceCollection
    {
        return $this->pagination(UserType::class, $this->resource);
    }

    public function find(UserType $type): JsonResponse
    {
        return $this->success(new $this->resource($type));
    }

    public function create(): JsonResponse
    {
        return $this->wrapCall($this->service, 'create', request());
    }

    public function update(UserType $type): JsonResponse
    {
        return $this->wrapCall($this->service, 'update', request(), $type);
    }

    public function delete(UserType $type): JsonResponse
    {
        return $this->wrapCall($this->service, 'delete', $type);
    }
}
