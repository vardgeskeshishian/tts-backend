<?php

namespace App\Http\Controllers\Api\Any\SFX;

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

    public function get(): JsonResponse
    {
        return $this->success($this->pageService->getMainPageOfType(MainPageConstants::TYPE_SFX));
    }
}
