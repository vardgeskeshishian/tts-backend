<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Api\ApiController;
use App\Models\CoreDefaults;
use App\Models\Structure\Core;
use App\Services\CoreService;
use App\Services\MetaService;
use App\Services\QuestionsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * @group Core
 * Meta, H1, text
 *
 * Class CoreController
 * @package App\Http\Controllers\Api\Admin
 */
class CoreController extends ApiController
{
    /**
     * @var CoreService
     */
    private $coreService;
    private QuestionsService $questionsService;

    public function __construct(CoreService $coreService, QuestionsService $questionsService)
    {
        parent::__construct();
        $this->coreService = $coreService;
        $this->questionsService = $questionsService;
    }

    public function getCore(Request $request)
    {
        $query = $request->get('q');

        $core = $this->coreService
            ->getCore()
            ->when($query, fn ($items) => $items->filter(fn ($item) => Str::contains($item->name, $query)));

        return view('admin.core.index', compact('core'));
    }

    public function findCore($coreId)
    {
        $core = Core::findOrNew($coreId);

        $request = new Request();
        $request->merge($core->getCoreDataName());
        $request->merge(['core_id' => $coreId]);

        $brandingKeys = ['brand_title', 'brand_description_1', 'brand_description_2'];

        $coreMeta = collect($this->coreService->setRequest($request)->find()['meta'] ?? []);
        $meta = $coreMeta
            ->except($brandingKeys)
            ->sortKeysUsing(fn ($key) => in_array($key, ['text2', 'description']) ? 1 : -1)
            ->all();

        $branding = $coreMeta->only($brandingKeys)->all();

        return view('admin.core.detailed', [
            'core' => $core,
            'meta' => $meta,
            'branding' => $branding,
            'faq' => $this->questionsService->getForModelAsArray($core),
        ]);
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
        Cache::forget(Str::slug($core->getMorphClass()) . ":meta:{$core->id}");

        return $this->getAdminCoreDefaults();
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

    public function deleteCore(Core $core)
    {
        $this->coreService->delete($core);

        return redirect()->back();
    }
}
