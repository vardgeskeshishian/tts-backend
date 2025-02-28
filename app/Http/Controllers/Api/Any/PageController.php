<?php

namespace App\Http\Controllers\Api\Any;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Any\PageResource;
use App\Models\Structure\Page;
use Illuminate\Http\JsonResponse;

class PageController extends ApiController
{
    /**
     * Get Page by Url
     *
     * @OA\Get(
     *     path="/v1/public/pages/{url}",
     *     summary="Get Page by Url",
     *     @OA\Parameter(parameter="url", description="Url page", required=true, in="path", name="url", example="main"),
     *     tags={"Pages"},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/PageResource"
     *         ),
     *     ),
     * )
     *
     * @param string $url
     * @return JsonResponse
     */
    public function getPageByUrl(string $url): JsonResponse
    {
        $page = Page::where('url', $url)->first();

        return response()->json(
            new PageResource($page)
        );
    }

    /**
     * @OA\Get(
     *     path="/v1/public/pages",
     *     summary="Get Page List",
     *     tags={"Pages"},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="array", @OA\Items(
     *                  ref="#/components/schemas/PageResource"
     *              )),
     *         ),
     *     ),
     * )
     *
     * @return JsonResponse
     */
    public function get(): JsonResponse
    {
        return response()->json(
            Page::get()->map(fn($item) => new PageResource($item))
        );
    }
}
