<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Services\ImagesService;
use App\Services\TaggingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagsController extends ApiController
{
    /**
     * @var TaggingService
     */
    private $taggingService;
    /**
     * @var ImagesService
     */
    private $imagesService;

    public function __construct(TaggingService $taggingService, ImagesService $imagesService)
    {
        parent::__construct();

        $this->taggingService = $taggingService;
        $this->imagesService = $imagesService;
    }
    public function get(): JsonResponse
    {
        return $this->success($this->taggingService->getAllTypes());
    }

    public function getType(string $type): JsonResponse
    {
        return $this->wrapCall($this->taggingService, 'getAllOfType', $type);
    }

    public function updatePositions(Request $request, string $type): JsonResponse
    {
        return $this->wrapCall($this->taggingService, 'updatePositions', $request, $type);
    }

    public function uploadImage(Request $request, string $type, int $id): JsonResponse
    {
        return $this->wrapCall($this->taggingService, 'uploadImage', $request, $type, $id);
    }

    public function deleteTag(string $type, int $id): JsonResponse
    {
        return $this->wrapCall($this->taggingService, 'deleteTag', $type, $id);
    }
}
