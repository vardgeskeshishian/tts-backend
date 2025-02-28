<?php

namespace App\Http\Controllers\Api\Any;

use App\Http\Controllers\Api\ApiController;
use App\Services\CoreService;
use Illuminate\Http\Request;

/**
 * @group Core
 * Meta, H1, text
 *
 * Class CoreController
 * @package App\Http\Controllers\Api\Admin
 */
class CoreController extends ApiController
{
    private CoreService $coreService;

    public function __construct(CoreService $coreService)
    {
        parent::__construct();
        $this->coreService = $coreService;
    }

    public function find(Request $request)
    {
        return $this->wrapCall($this->coreService->setRequest($request), 'find');
    }
}
