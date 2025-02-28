<?php

namespace App\Http\Controllers\Api\Admin;

use App\Constants\MainPageConstants;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Api\MainPageResource;
use App\Models\Structure\MainPage;
use App\Services\MainPageService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Main Page Editing
 *
 * Class MainPageController
 * @package App\Http\Controllers\Api\Admin
 */
class MainPageController extends ApiController
{
    protected $resource = MainPageResource::class;
    /**
     * @var MainPageService
     */
    private $service;

    public function __construct(MainPageService $service)
    {
        parent::__construct();

        $this->service = $service;
    }

    public function get(): JsonResponse
    {
        return $this->success($this->service->getMainPageOfType(MainPageConstants::TYPE_ROOT));
    }

    public function sfx()
    {
        return $this->success($this->service->getMainPageOfType(MainPageConstants::TYPE_SFX));
    }

    public function find(MainPage $mainPage): JsonResponse
    {
        return $this->success(new  $this->resource($mainPage));
    }

    public function findAllForSection(string $sectionId): LengthAwarePaginator|AnonymousResourceCollection
    {
        return $this->pagination(MainPage::class, $this->resource, ['section_id' => $sectionId]);
    }

    /**
     * Create part of a section or whole section
     *
     * @bodyParam section_id string random id or null to create section_id in api. Example: 123qwe
     * @bodyParam type string [h1,text,anything] you want for use on main page. Example: h1
     * @bodyParam text string self-explanatory
     *
     * @responseFile responses/admin/main-page.create.json
     *
     * @return JsonResponse
     */
    public function create(): JsonResponse
    {
        return $this->wrapCall($this->service, 'create', request());
    }

    /**
     * Update part of a section by internal id
     *
     * @bodyParam type string [h1,text,anything] you want for use on main page. Example: h1
     * @bodyParam text string self-explanatory
     *
     * @param MainPage $mainPage
     *
     * @return JsonResponse
     */
    public function update(MainPage $mainPage): JsonResponse
    {
        return $this->wrapCall($this->service, 'update', request(), $mainPage);
    }

    public function delete(MainPage $mainPage): JsonResponse
    {
        return $this->wrapCall($this->service, 'delete', $mainPage);
    }
}
