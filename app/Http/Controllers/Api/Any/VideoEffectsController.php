<?php


namespace App\Http\Controllers\Api\Any;

use App\Constants\Env;
use App\Constants\MainPageConstants;
use App\Exceptions\EmptySearchResult;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Any\AbstractTagResource;
use App\Http\Resources\VideoEffectApplicationResource;
use App\Http\Resources\VideoEffectCategoryResource;
use App\Http\Resources\VideoEffectResource;
use App\Jobs\FillAuthorBalanceJob;
use App\Models\License;
use App\Models\Structure\TemplateMeta;
use App\Models\User;
use App\Models\UserDownloads;
use App\Models\VideoEffects\VideoEffect;
use App\Models\VideoEffects\VideoEffectApplication;
use App\Models\VideoEffects\VideoEffectCategory;
use App\Models\VideoEffects\VideoEffectPlugin;
use App\Models\VideoEffects\VideoEffectResolution;
use App\Models\VideoEffects\VideoEffectTag;
use App\Models\VideoEffects\VideoEffectVersion;
use App\Services\AnalyticsService;
use App\Services\Finance\BalanceService;
use App\Services\LicenseNumberService;
use App\Services\MainPageService;
use App\Services\OneTimeLinkService;
use App\Services\OrderService;
use App\Services\SearchStrategies\VideoEffectSearch;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Filters\VideoEffectFilter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class VideoEffectsController extends ApiController
{
    public function __construct(
        private readonly MainPageService      $pageService,
        private readonly OrderService         $orderService,
        private readonly OneTimeLinkService   $oneTimeLinkService,
        private readonly LicenseNumberService $licenseNumberService,
        private readonly AnalyticsService     $analyticsService,
        private readonly VideoEffectSearch $videoEffectSearch
    )
    {
    }

    /**
     * @OA\Get(
     *     path="/v1/public/video-effects/by-slug/{slug}",
     *     summary="Get video-effects by Slug",
     *     tags={"Video Effects"},
     *     @OA\Parameter(parameter="slug", description="Slug Video Effects", required=true, in="path", name="slug"),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="object", ref="#/components/schemas/VideoEffectResource"),
     *         ),
     *     ),
     * )
     *
     * @param string|null $slug
     * @return JsonResponse
     */
    public function findBySlug(?string $slug): JsonResponse
    {
        try {
            $videoEffect = VideoEffect::with('application', 'categories',
                'resolutions', 'plugins', 'version', 'tags')->where('slug', $slug)->firstOrFail();
            return response()->json(new VideoEffectResource($videoEffect));
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * @OA\Get(
     *     path="/v1/public/video-effects/search",
     *     summary="Search video-effects",
     *     tags={"Video Effects"},
     *     @OA\Parameter(parameter="applications[]", description="Slug applications", required=false, in="query", name="applications[]"),
     *     @OA\Parameter(parameter="application", description="Slug application", required=false, in="query", name="application"),
     *     @OA\Parameter(parameter="plugins[]", description="ID plugins", required=false, in="query", name="plugins[]"),
     *     @OA\Parameter(parameter="resolutions[]", description="ID resolutions", required=false, in="query", name="resolutions[]"),
     *     @OA\Parameter(parameter="q", description="Search string", required=false, in="query", name="q"),
     *     @OA\Parameter(parameter="sort", description="Sort", required=false, in="query", name="sort"),
     *     @OA\Parameter(parameter="author", description="Author Slug", required=false, in="query", name="author"),
     *     @OA\Parameter(parameter="category", description="Category Slug", required=false, in="query", name="category"),
     *     @OA\Parameter(parameter="tag", description="Tag Slug", required=false, in="query", name="tag"),
     *     @OA\Parameter(parameter="perpage", description="Per Page", required=false, in="query", name="perpage"),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="current_page", type="integer"),
     *              @OA\Property(property="data", type="object",
     *                   ref="/components/schemas/VideoEffectSearchResource"
     *              ),
     *              @OA\Property(property="first_page_url", type="string"),
     *              @OA\Property(property="from", type="integer"),
     *              @OA\Property(property="last_page", type="integer"),
     *              @OA\Property(property="last_page_url", type="string"),
     *              @OA\Property(property="links", type="array",
     *                  @OA\Items(type="object",
     *                      @OA\Property(property="url", type="string"),
     *                      @OA\Property(property="label", type="string"),
     *                      @OA\Property(property="active", type="boolean"),
     *                  )
     *              ),
     *              @OA\Property(property="next_page_url", type="string"),
     *              @OA\Property(property="path", type="string"),
     *              @OA\Property(property="per_page", type="integer"),
     *              @OA\Property(property="prev_page_url", type="string"),
     *              @OA\Property(property="to", type="integer"),
     *              @OA\Property(property="total", type="integer"),
     *         ),
     *     ),
     * )
     *
     * @param VideoEffectFilter $filter
     * @return JsonResponse
     */
    public function search(VideoEffectFilter $filter): JsonResponse
    {
        try {
            $filter->validate();

            $categories = [
                'applications',
                'application',
                'plugins',
                'resolutions',
                'category',
                'tag'
            ];

            $request = $filter->getRequest();
            $q = $request['q'] ?? null;
            $sort = $request['sort'] ?? 'trending';
            $sort = $sort == 'new' ? 'created_at' : $sort;

            $q_categories = '';
            foreach ($categories as $category)
            {
                if (array_key_exists($category, $request))
                    $q_categories .= $request[$category];
            }

            if ($q_categories !== '')
                $q .= $q_categories;

            return response()->json($this->videoEffectSearch->searchCustomApi($filter, $q, $sort));
        } catch (EmptySearchResult $e) {
			return response()->json([
				'message' => $e->getMessage(),
			], 404);
		} catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/v1/public/video-effects/{categoryType}/{categorySlug}",
     *     summary="Search video-effects",
     *     tags={"Video Effects"},
     *     @OA\Parameter(parameter="categoryType", description="Category Type: genres, moods, instruments, types, tags", required=true, in="path", name="categoryType"),
     *     @OA\Parameter(parameter="categorySlug", description="Category Slug", required=true, in="path", name="categorySlug"),
     *     @OA\Parameter(parameter="applications[]", description="Slug applications", required=false, in="query", name="applications[]"),
     *     @OA\Parameter(parameter="application", description="Slug application", required=false, in="query", name="application"),
     *     @OA\Parameter(parameter="plugins[]", description="ID plugins", required=false, in="query", name="plugins[]"),
     *     @OA\Parameter(parameter="resolutions[]", description="ID resolutions", required=false, in="query", name="resolutions[]"),
     *     @OA\Parameter(parameter="q", description="Search string", required=false, in="query", name="q"),
     *     @OA\Parameter(parameter="sort", description="Sort", required=false, in="query", name="sort"),
     *     @OA\Parameter(parameter="author", description="Author Slug", required=false, in="query", name="author"),
     *     @OA\Parameter(parameter="category", description="Category Slug", required=false, in="query", name="category"),
     *     @OA\Parameter(parameter="tag", description="Tag Slug", required=false, in="query", name="tag"),
     *     @OA\Parameter(parameter="perpage", description="Per Page", required=false, in="query", name="perpage"),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="current_page", type="integer"),
     *              @OA\Property(property="data", type="object",
     *                   ref="/components/schemas/VideoEffectSearchResource"
     *              ),
     *              @OA\Property(property="first_page_url", type="string"),
     *              @OA\Property(property="from", type="integer"),
     *              @OA\Property(property="last_page", type="integer"),
     *              @OA\Property(property="last_page_url", type="string"),
     *              @OA\Property(property="links", type="array",
     *                  @OA\Items(type="object",
     *                      @OA\Property(property="url", type="string"),
     *                      @OA\Property(property="label", type="string"),
     *                      @OA\Property(property="active", type="boolean"),
     *                  )
     *              ),
     *              @OA\Property(property="next_page_url", type="string"),
     *              @OA\Property(property="path", type="string"),
     *              @OA\Property(property="per_page", type="integer"),
     *              @OA\Property(property="prev_page_url", type="string"),
     *              @OA\Property(property="to", type="integer"),
     *              @OA\Property(property="total", type="integer"),
     *         ),
     *     ),
     * )
     *
     * @param VideoEffectFilter $filter
     * @param string $categoryType
     * @param string $categorySlug
     * @return JsonResponse
     */
//    public function searchByCategorySlug(VideoEffectFilter $filter, string $categoryType, string $categorySlug): JsonResponse
//    {
//        try {
//            $request = $filter->getRequest();
//            $q = $request['q'] ?? null;
//            $sort = $request['sort'] ?? 'trending';
//            $sort = $sort == 'new' ? 'created_at' : $sort;
//
//            return response()->json($this->videoEffectSearch->searchCustomApi($filter, $q, $sort,
//                categoryType: $categoryType, categorySlug: $categorySlug));
//        } catch (EmptySearchResult $e) {
//            return response()->json([
//                'message' => $e->getMessage(),
//            ], 404);
//        } catch (Exception $e) {
//            return response()->json([
//                'message' => $e->getMessage()
//            ], 500);
//        }
//    }

    /**
     * @OA\Get(
     *     path="/v1/public/plugins",
     *     summary="Get Plugins",
     *     tags={"Video Effects"},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(type="array", @OA\Items(
     *                  @OA\Property(property="id", type="string"),
     *                  @OA\Property(property="name", type="string"),
     *              ))
     *         ),
     *     ),
     * )
     *
     * @return Collection
     */
    public function getPlugins(): Collection
    {
        return VideoEffectPlugin::select(['id', 'name', 'slug'])->get();
    }

    /**
     * @OA\Get(
     *     path="/v1/public/resolutions",
     *     summary="Get Resolutions",
     *     tags={"Video Effects"},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(type="array", @OA\Items(
     *                  @OA\Property(property="id", type="string"),
     *                  @OA\Property(property="name", type="string"),
     *                  @OA\Property(property="full", type="string"),
     *                  @OA\Property(property="short", type="string"),
     *                  @OA\Property(property="height", type="string"),
     *                  @OA\Property(property="width", type="string"),
     *              ))
     *         ),
     *     ),
     * )
     *
     * @return \Illuminate\Database\Eloquent\Collection|array
     */
    public function getResolutions(): \Illuminate\Database\Eloquent\Collection|array
    {
        return VideoEffectResolution::get();
    }

    /**
     * @OA\Get(
     *     path="/v1/public/video-effects/{slug}/similar",
     *     summary="Similar video-effects",
     *     tags={"Video Effects"},
     *     @OA\Parameter(parameter="slug", description="Slug Video Effects", required=true, in="path", name="slug"),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="object", ref="#/components/schemas/VideoEffectResource"),
     *         ),
     *     ),
     * )
     *
     * @param string $slug
     * @return JsonResponse
     */
    public function similar(string $slug): JsonResponse
    {
        try {
            $videoEffect = VideoEffect::where('slug', $slug)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 404);
        }

        $words = $videoEffect->mix?->text ?? '';
        $videoEffectsIds = collect(DB::select("select track_elastics.track_id
            from track_elastics join video_effects on track_elastics.track_id = video_effects.id
            where MATCH(track_elastics.text) AGAINST(?) AND track_elastics.track_type = ?
            AND track_elastics.track_id != ? AND video_effects.deleted_at is null LIMIT 6",
            [$words, VideoEffect::class, $videoEffect->id]))->map(fn($item) => $item->track_id);

        return response()->json(VideoEffect::whereIn('id', $videoEffectsIds)
            ->with('application', 'categories',
                'resolutions', 'plugins', 'version', 'tags')
            ->get()->map(fn($item) => new VideoEffectResource($item)));
    }

    /**
     * @OA\Get(
     *     path="/v1/public/video-effects/featured",
     *     summary="Action Featured",
     *     tags={"Video Effects"},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/VideoEffectResource")),
     *         ),
     *     ),
     * )
     *
     * @return JsonResponse
     */
    public function actionFeatured(): JsonResponse
    {
        $videoEffects = VideoEffect::inRandomOrder()->limit(4)->get();

        return $this->success(VideoEffectResource::collection($videoEffects));
    }

    /**
     * @OA\Get(
     *     path="/v1/public/video-effects/main-page",
     *     summary="Find all sections and its data",
     *     tags={"Video Effects"},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="array", @OA\Items(
     *                  @OA\Property(property="section", type="object",
     *                      @OA\Property(property="name", type="string", example="Royalty Free Music & Video Templates"),
     *                      @OA\Property(property="description", type="string", example="Elevate your video production with our Music or Video Templates"),
     *                      @OA\Property(property="title", type="string", example="Royalty Free Music for Videos"),
     *                  ),
     *                  @OA\Property(property="testimonials", type="array", @OA\Items(
     *                      @OA\Property(property="id", type="integer", example="1"),
     *                      @OA\Property(property="header", type="string", example="fdsfsfs"),
     *                      @OA\Property(property="text", type="string", example="sdfsfsfsf"),
     *                      @OA\Property(property="images", type="object",
     *                          @OA\Property(property="background", type="string", example="https://static.taketones.com/f/images/1537e2849d4c33774ca1e1d8f99c628c.jpeg"),
     *                          @OA\Property(property="thumbnail", type="string", example="https://static.taketones.com/f/images/1537e2849d4c33774ca1e1d8f99c628c.jpeg"),
     *                      ),
     *                  ))
     *              )),
     *         ),
     *     ),
     * )
     *
     * @return JsonResponse
     */
    public function actionMainPage(): JsonResponse
    {
        return $this->success($this->pageService->getMainPageOfType(MainPageConstants::TYPE_VFX));
    }

    /**
     * @OA\Get(
     *     path="/v1/public/video-effects/common",
     *     summary="Action Common",
     *     tags={"Video Effects"},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="array", @OA\Items(type="string")),
     *         ),
     *     ),
     * )
     *
     * @return JsonResponse
     */
    public function actionCommon(): JsonResponse
    {
        $applications = VideoEffectApplication::with('versions')->get();
        $plugins = VideoEffectPlugin::all();
        $categories = VideoEffectCategory::all();
        $versions = VideoEffectVersion::all()->makeVisible('application_id');
        $resolutions = VideoEffectResolution::all();
        $tags = VideoEffectTag::limit(20)->get();

        return $this->success(
            compact(
                'applications',
                'plugins',
                'categories',
                'versions',
                'resolutions',
                'tags'
            )
        );
    }

    /**
     * @OA\Post(
     *     path="/v1/public/video-effects/{videoEffect}/add-to-order",
     *     summary="Fast buy",
     *     tags={"Video Effects"},
     *     @OA\Parameter(parameter="videoEffect", description="videoEffect", required=true, in="path", name="videoEffect", example="7"),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="cart", type="object", ref="#/components/schemas/OrderResource")
     *              )
     *         ),
     *     ),
     * )
     *
     * @param VideoEffect $videoEffect
     * @return JsonResponse
     */
    public function actionAddToOrder(VideoEffect $videoEffect): JsonResponse
    {
        try {
            $cart = $this->orderService
                ->setItem($videoEffect, Env::ITEM_TYPE_VIDEO_EFFECTS)
                ->addItem();

            return $this->success([
                'cart' => $cart,
            ]);
        } catch (Exception $e) {
            return $this->errorWrapped($e);
        }
    }

    /**
     * @OA\Post(
     *     path = "/v1/public/video-effects/{videoEffect}/download",
     *     summary = "Sub Download",
     *     tags={"Video Effects"},
     *     @OA\Parameter(parameter="videoEffect", description="ID SFXTrack", required=true, in="path", name="videoEffect", example="7"),
     *     @OA\RequestBody(required=true, @OA\MediaType(mediaType="multipart/form-data", @OA\Schema(
     *          @OA\Property(property="license_id", type="integer", example="1"),
     *     ))),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="success", type="boolean", example="true"),
     *                  @OA\Property(property="license", type="string"),
     *                  @OA\Property(property="zip", type="string"),
     *              )
     *         ),
     *     ),
     * )
     *
     * @param Request $request
     * @param VideoEffect $videoEffect
     * @return JsonResponse
     */
    public function actionDownload(Request $request, VideoEffect $videoEffect): JsonResponse
    {
        abort_if(!request()->has('license_id'), 404, "license not found");

        $license = License::find(request('license_id'));

        $download = UserDownloads::create([
            'user_id' => auth()->user()->id,
            'track_id' => $videoEffect->id,
            'type' => Env::ITEM_TYPE_VIDEO_EFFECTS,
            'license_number' => $this->licenseNumberService->generate($license),
            'license_id' => $license->id,
            'class' => VideoEffect::class
        ]);

        if ($download->wasRecentlyCreated) {
            $videoEffect->increment('times_downloaded');
            $videoEffect->save();
        }

        /**
         * @var $user User
         */
        $user = auth()->user();

        $user->increment('downloads');

        if ($license->payment_type === 'recurrent') {
            $this->analyticsService->sendSubDownload($videoEffect->full_name . ' (VFX)');
            smart_dispatcher((new FillAuthorBalanceJob())->setUserDownload($download), [BalanceService::class]);
        }

        $zipUrl = $this->oneTimeLinkService->generateDownloadsZip($videoEffect->id, $download);
        $licUrl = $this->oneTimeLinkService->generateForUserDownloadLicense($download);

        return $this->success([
            'success' => true,
            'license' => $licUrl,
            'zip' => $zipUrl
        ]);
    }
	
	
	/**
	 * @OA\Get(
	 *     path="/v1/public/video-effects/category/by-slug/{slug}",
	 *     summary="Get category by Slug",
	 *     tags={"Video Effects"},
	 *     @OA\Parameter(parameter="slug", description="Slug Video Effects", required=true, in="path", name="slug"),
	 *     @OA\Response(
	 *         response="200",
	 *         description="Success",
	 *         @OA\JsonContent(
	 *              @OA\Property(property="data", type="object"),
	 *         ),
	 *     ),
	 * )
	 *
	 * @param string|null $slug
	 * @return JsonResponse
	 */
	public function getCategoryBySlug(?string $slug): JsonResponse
	{
		$category = VideoEffectCategory::query()->where('slug', $slug)->first();
		
		if (empty($category)) {
            $application = VideoEffectApplication::where('slug', $slug)->first();

            if (empty($application))
            {
                return response()->json(['message' => 'No records found'], 404);
            }

            $template = TemplateMeta::where('type', VideoEffectApplication::class)->first();
            return $this->success([
                'category' => new VideoEffectApplicationResource($application, $template),
            ]);
		}

        $template = TemplateMeta::where('type', VideoEffectCategory::class)->first();
        return $this->success([
            'category' => new VideoEffectCategoryResource($category, $template),
        ]);
	}

    /**
     * @param string $slug
     * @return JsonResponse
     *
     * @OA\Get(
     *     path="/v1/public/video-effects/tag/by-slug/{slug}",
     *     summary="Get Tag by Slug",
     *     tags={"Video Effects"},
     *     @OA\Parameter(parameter="slug", description="Slug Tag", required=true, in="path", name="slug"),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="object"),
     *         ),
     *     ),
     * )
     */
    public function getTagBySlug(string $slug): JsonResponse
    {
        $template = TemplateMeta::where('type', VideoEffectTag::class)->first();
        $tag = VideoEffectTag::with('icon')->where('slug', $slug)->first();
        return response()->json(
            new AbstractTagResource($tag, $template)
        );
    }
}
