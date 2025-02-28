<?php

namespace App\Http\Controllers\Api\Any;

use App\Http\Controllers\Api\ApiController;
use App\Services\TaggingService;
use Illuminate\Http\JsonResponse;

class TagsController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/v1/public/tags",
     *     summary="Tags List",
     *     tags={"Tags"},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Tag")),
     *         ),
     *     ),
     * )
     *
     * @return JsonResponse
     */
    public function get(): JsonResponse
    {
        return $this->wrapCall(TaggingService::class, 'getAll');
    }
}
