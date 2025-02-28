<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Api\BlogCategoryResource;
use App\Models\BlogCategory;
use App\Services\BlogCategoryService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Blog Categories
 *
 * Class BlogCategoryController
 * @package App\Http\Controllers\Api\Admin
 */
class BlogCategoryController extends ApiController
{
    protected $resource = BlogCategoryResource::class;
    /**
     * @var BlogCategoryService
     */
    protected $service;

    public function __construct(BlogCategoryService $service)
    {
        parent::__construct();

        $this->service = $service;
    }
    public function get(): LengthAwarePaginator|AnonymousResourceCollection
    {
        return $this->pagination(BlogCategory::class, $this->resource);
    }

    public function find(BlogCategory $category)
    {
        return $this->success(new $this->resource($category));
    }

    public function create(Request $request): JsonResponse
    {
        return $this->wrapCall($this->service, 'create', $request);
    }

    public function update(Request $request, BlogCategory $category): JsonResponse
    {
        return $this->wrapCall($this->service, 'update', $request, $category);
    }


    public function delete(BlogCategory $category): JsonResponse
    {
        if (!$category->isDeletable()) {
            return $this->error("can't delete", "this category can't be deleted");
        }

        return $this->wrapCall($this->service, 'delete', $category);
    }
}
