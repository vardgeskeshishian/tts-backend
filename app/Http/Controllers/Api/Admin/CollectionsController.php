<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Api\CollectionsResource;
use App\Models\Structure\Collection;
use App\Services\CollectionsService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CollectionsController extends ApiController
{
    public function get(): LengthAwarePaginator|AnonymousResourceCollection
    {
        return $this->pagination(
            Collection::class,
            CollectionsResource::class
        );
    }

    public function find(Collection $collection): JsonResponse
    {
        $resource = new CollectionsResource($collection);

        return $this->success($resource);
    }

    public function getForMain()
    {
        $res = Collection::latest()->get();

        return $this->success($res);
    }

    public function create(): JsonResponse
    {
        return $this->wrapCall(CollectionsService::class, 'create', request());
    }

    public function update(Collection $collection): JsonResponse
    {
        return $this->wrapCall(CollectionsService::class, 'update', request(), $collection);
    }

    public function delete(Collection $collection): JsonResponse
    {
        return $this->wrapCall(CollectionsService::class, 'delete', $collection);
    }
}
