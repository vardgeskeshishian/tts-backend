<?php

namespace App\Http\Controllers\Api\Any;

use App\Services\MainPageService;
use App\Constants\MainPageConstants;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;

class MainPageController extends ApiController
{
    /**
     * @var MainPageService
     */
    private MainPageService $pageService;

    public function __construct(MainPageService $pageService)
    {
        $this->pageService = $pageService;
    }

    /**
     * @OA\Get(
     *     path="/v1/public/main-page",
     *     summary="Find all sections and its data",
     *     tags={"Main Page"},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="array", @OA\Items(
     *                  @OA\Property(property="section", type="object",
     *                      @OA\Property(property="name", type="string", example="Royalty Free Music & Video Templates"),
     *                      @OA\Property(property="description", type="string", example="Elevate your video production with our Music or Video Templates"),
     *                      @OA\Property(property="title", type="string", example="Royalty Free Music for Videos"),
     *                  )
     *              )),
     *         ),
     *     ),
     * )
     *
     * @return JsonResponse
     */
    public function get(): JsonResponse
    {
        return $this->success($this->pageService->getMainPageOfType(MainPageConstants::TYPE_ROOT));
    }
}
