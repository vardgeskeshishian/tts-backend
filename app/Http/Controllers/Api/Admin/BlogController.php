<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Resources\Api\BlogResource;
use App\Services\BlogService;
use App\Http\Controllers\Api\ApiController;
use App\Models\Structure\Blog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Blog Management
 *
 * Class BlogController
 * @package App\Http\Controllers\Api\Admin
 */
class BlogController extends ApiController
{
    protected $resource = BlogResource::class;
    protected $service;

    public function __construct(BlogService $blogService)
    {
        parent::__construct();

        $this->service = $blogService;
    }
    public function get(): LengthAwarePaginator|AnonymousResourceCollection
    {
        return $this->pagination(Blog::class, $this->resource);
    }

    /**
     * Find single blog post
     *
     * @responseFile responses/admin/blog.json
     *
     * @param Blog $blog
     *
     * @return JsonResponse
     */
    public function find(Blog $blog)
    {
        return $this->success(new $this->resource($blog));
    }

    public function create(Request $request): JsonResponse
    {
        return $this->wrapCall($this->service, 'create', $request);
    }

    /**
     *
     *
     * @bodyParam title string
     * @bodyParam short_description string
     * @bodyParam body string
     * @bodyParam featured bool
     * @bodyParam categories array Example: [1,2,3]
     * @bodyParam tags array Example: ["tag one", "tag two"]
     * @bodyParam meta array
     * @bodyParam images array Background or thumbnail. Keys are ['background', 'thumbnail']
     *
     * @responseFile responses/admin/blog.json
     *
     * @param Request $request
     * @param Blog $blog
     *
     * @return JsonResponse
     */
    public function update(Request $request, Blog $blog)
    {
        return $this->wrapCall($this->service, 'update', $request, $blog);
    }

    public function delete(Blog $blog)
    {
        return $this->wrapCall($this->service, 'delete', $blog);
    }
}
