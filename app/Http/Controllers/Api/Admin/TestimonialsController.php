<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Api\TestimonialsResource;
use App\Models\Structure\Testimonial;
use App\Services\TestimonialsService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TestimonialsController extends ApiController
{
    protected $resource = TestimonialsResource::class;
    /**
     * @var TestimonialsService
     */
    private $service;

    public function __construct(TestimonialsService $service)
    {
        parent::__construct();
        $this->service = $service;
    }
    public function get(): LengthAwarePaginator|AnonymousResourceCollection
    {
        return $this->pagination(Testimonial::class, $this->resource);
    }
    public function find(Testimonial $testimonial): JsonResponse
    {
        return $this->success(new $this->resource($testimonial));
    }

    public function create(): JsonResponse
    {
        return $this->wrapCall($this->service, 'create', request());
    }

    public function update(Testimonial $testimonial): JsonResponse
    {
        return $this->wrapCall($this->service, 'update', request(), $testimonial);
    }

    public function delete(Testimonial $testimonial): JsonResponse
    {
        return $this->wrapCall($this->service, 'delete', $testimonial);
    }
}
