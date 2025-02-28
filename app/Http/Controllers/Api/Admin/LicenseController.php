<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Api\LicenseResource;
use App\Models\License;
use App\Services\LicenseService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group License Management
 *
 * Class LicenseController
 * @package App\Http\Controllers\Api\Admin
 */
class LicenseController extends ApiController
{
    /**
     * @var LicenseService
     */
    private $licenseService;

    public function __construct(LicenseService $licenseService)
    {
        parent::__construct();
        $this->licenseService = $licenseService;
    }

    public function createLicense(Request $request): JsonResponse
    {
        return $this->wrapCall($this->licenseService, 'create', $request);
    }

    public function updateLicense(Request $request, License $license): JsonResponse
    {
        return $this->wrapCall($this->licenseService, 'update', $request, $license);
    }

    public function deleteLicense(License $license): JsonResponse
    {
        return $this->wrapCall($this->licenseService, 'delete', $license);
    }
}
