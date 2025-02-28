<?php


namespace App\Http\Controllers\System;

use App\Constants\VideoEffects;
use App\Http\Controllers\Api\ApiController;
use App\Models\User;
use App\Models\VideoEffects\VideoEffect;
use App\Models\VideoEffects\VideoEffectApplication;
use App\Models\VideoEffects\VideoEffectCategory;
use App\Models\VideoEffects\VideoEffectPlugin;
use App\Models\VideoEffects\VideoEffectResolution;
use App\Models\VideoEffects\VideoEffectTag;
use App\Models\VideoEffects\VideoEffectVersion;
use App\Services\VideoEffectsService;
use Illuminate\Http\Request;

class VideoEffectsController extends ApiController
{
    private VideoEffectsService $effectsService;

    public function __construct(VideoEffectsService $effectsService)
    {
        $this->effectsService = $effectsService;
    }

    public function viewDashboard(Request $request)
    {
        $all = VideoEffect::all();
        $stats = [];

        foreach (VideoEffects::STATUSES as $statusId => $statusName) {
            $stats[$statusId] = [
                'statusName' => $statusName,
                'count' => $all->where('status', $statusName)->count(),
            ];
        }

        $total = $all->count();

        return view('admin.video-effects.dashboard', compact('stats', 'total'));
    }

    public function viewList(Request $request)
    {
        $statusId = $request->get('status', VideoEffects::STATUS_PUBLISHED);

        $list = VideoEffect::byStatus($statusId)->paginate();
        $statuses = VideoEffects::STATUSES;

        return view('admin.video-effects.index', compact('list', 'statuses', 'statusId'));
    }

    public function viewDetailed(Request $request, VideoEffect $videoEffect)
    {
        $applications = VideoEffectApplication::with('versions')->get();
        $plugins = VideoEffectPlugin::all();
        $categories = VideoEffectCategory::all();
        $versions = VideoEffectVersion::all()->makeVisible('application_id');
        $resolutions = VideoEffectResolution::all();

        $isSelected = function ($currentId, $selectedIds) {
            return in_array($currentId, $selectedIds);
        };

        return view('admin.video-effects.detailed', compact(
            'videoEffect',
            'applications',
            'plugins',
            'categories',
            'versions',
            'resolutions',
            'isSelected'
        ));
    }

    public function viewSubmissions(Request $request)
    {
        $filters = $request->get('filters');

        $list = VideoEffect::where('status', '!=', VideoEffects::STATUS_PUBLISHED)
            ->when($request->has('filters'), fn($q) => $q->where('status', $filters['status']))
            ->orderByDesc('created_at')->paginate();

        $statuses = VideoEffects::STATUSES;

        $admins = User::where('role', 'admin')->get();

        return view('admin.video-effects.submissions', compact('list', 'filters', 'admins', 'statuses'));
    }

    public function viewTags()
    {
        return view('admin.video-effects.tags');
    }

    public function actionTagSearch(Request $request)
    {
        $q = $request->get('q');

        return VideoEffectTag::where(['id' => $q])->orWhere('name', 'like', "$q%")->get();
    }

    public function actionVideoEffectUpdate(Request $request, VideoEffect $videoEffect)
    {
        $videoEffect->fill($request->all());
        $videoEffect->save();

        if ($request->has('tags')) {
            $videoEffect->tags()->delete();
            $videoEffect->tags()->createMany($this->effectsService->prepareTagsFromArray($request->get('tags') ?? []));
        }

        return redirect()->back();
    }

    public function actionAddPublicComment(Request $request, VideoEffect $videoEffect)
    {
        $this->effectsService->addReviewerComment($videoEffect, $request->get('reviewer'));

        return redirect()->back();
    }

    public function actionAddPrivateComment(Request $request, VideoEffect $videoEffect)
    {
        $this->effectsService->addHiddenComment($videoEffect, $request->get('reviewer'));

        return redirect()->back();
    }
}
