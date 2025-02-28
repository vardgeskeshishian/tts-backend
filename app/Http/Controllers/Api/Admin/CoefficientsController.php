<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Api\CoefficientResource;
use App\Models\Coefficient;
use App\Repositories\CoefficientRepository;
use App\Services\CoefficientService;
use Illuminate\Http\JsonResponse;

/**
 * @group Coefficients
 *
 * Class CoefficientsController
 * @package App\Http\Controllers\Api\Admin
 */
class CoefficientsController extends ApiController
{
    /**
     * @var CoefficientService
     */
    private $coefficientService;
    /**
     * @var CoefficientRepository
     */
    private $coefficientRepository;

    public function __construct(CoefficientService $coefficientService, CoefficientRepository $coefficientRepository)
    {
        parent::__construct();
        $this->coefficientService = $coefficientService;
        $this->coefficientRepository = $coefficientRepository;
    }

    public function get(): JsonResponse
    {
        $all = $this->coefficientRepository->get();

        return $this->success(CoefficientResource::collection($all));
    }

    public function updateCoefficient(Coefficient $coefficient): JsonResponse
    {
        return $this->wrapCall($this->coefficientService, 'update', request(), $coefficient);
    }
}
