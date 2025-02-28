<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Models\CoreDefaults;
use App\Models\Structure\Core;
use App\Repositories\CoreRepository;
use App\Services\CoreService;
use App\Services\MetaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * @group Core
 * Meta, H1, text
 *
 * Class CoreController
 * @package App\Http\Controllers\Api\Admin
 * @deprecated
 */
class CoreController extends ApiController
{
    /**
     * @var CoreService
     */
    private $coreService;

    public function __construct(CoreService $coreService)
    {
        parent::__construct();
        $this->coreService    = $coreService;
    }
    public function get(): JsonResponse
    {
        return $this->success($this->coreService->getCore());
    }

    /**
     * @return JsonResponse
     */
    public function find(): JsonResponse
    {
        return $this->wrapCall($this->coreService, "find");
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function fill(Request $request)
    {
        return $this->wrapCall($this->coreService, 'fill', $request);
    }

    public function delete()
    {
        $modelData = [
            'type'  => request()->get('type'),
            'tag' => request()->get('tag'),
            'value' => request()->get('value')
        ];

        $core = Core::where($modelData)->first();

        $this->coreService->delete($core);

        return $this->get();
    }

    public function getAdminCoreDefaults()
    {
        $categories = [];
        $defaults = [];

        CoreDefaults::all()->map(function ($default) use (&$categories, &$defaults) {
            if (in_array($default['name'], ['genre', 'instrument', 'usage-type', 'mood'])) {
                $categories[$default['name']] = $default->getMeta();
            } else {
                $defaults[$default['name']] = $default->getMeta();
            }
        });

        return view('admin.core.defaults', compact('defaults', 'categories'));
    }

    public function updateCoreDefaults()
    {
        $name = request('core_name');
        $meta = request('meta');

        /**
         * @var $core CoreDefaults
         */
        $core = CoreDefaults::where('name', $name)->first();
        
        if (!$core) {
            return [];
        }

        $metaService = resolve(MetaService::class);

        $metaService->fillInForObject($core, $meta);

        $core->refresh();
        $core->flushCache();
        Cache::forget("meta:" . CoreDefaults::class . ":{$core->id}");

        return $this->getAdminCoreDefaults();
    }
}
