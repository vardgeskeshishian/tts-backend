<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use App\Models\Promocode;
use App\Services\PromocodesService;

/**
 * Class MainPageController
 * @package App\Http\Controllers\Api\Admin
 */
class PromocodesController extends ApiController
{
    protected $service;

    public function __construct(PromocodesService $promocodesService)
    {
        parent::__construct();

        $this->service = $promocodesService;
    }

    public function get(): JsonResponse
    {
        $promocodes = Promocode::all();

        return $this->success($promocodes);
    }

    public function find(Promocode $promocode): JsonResponse
    {
        return $this->success($promocode);
    }

    public function create(): JsonResponse
    {
        return $this->wrapCall($this->service, 'create', request());
    }

    public function update(Promocode $promocode): JsonResponse
    {
        return $this->wrapCall($this->service, 'update', request(), $promocode);
    }

    public function delete(Promocode $promocode): JsonResponse
    {
        return $this->wrapCall($this->service, 'delete', $promocode);
    }
}
