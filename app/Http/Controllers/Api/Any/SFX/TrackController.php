<?php


namespace App\Http\Controllers\Api\Any\SFX;

use App\Actions\CategoryResponse;
use App\Exceptions\EmptySearchResult;
use App\Http\Resources\Any\AbstractTagResource;
use App\Models\SFX\SFXCategory;
use App\Models\SFX\SFXTag;
use App\Models\Structure\TemplateMeta;
use Exception;
use App\Models\User;
use App\Models\License;
use App\Models\SFX\SFXTrack;
use App\Models\UserDownloads;
use App\Services\OrderService;
use App\Services\LicenseService;
use App\Constants\Env;
use App\Services\AnalyticsService;
use App\Services\OneTimeLinkService;
use App\Services\LicenseNumberService;
use App\Services\SearchStrategies\SoundEffectSearch;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\SFX\Track\BuyTrackRequest;
use App\Http\Requests\SFX\Track\AddToCartRequest;
use App\Http\Resources\Any\SFX\Track\TrackCutResource;
use Illuminate\Http\JsonResponse;
use App\Filters\SFXTrackFilter;


class TrackController extends ApiController
{

    public function __construct(
        private readonly LicenseNumberService $licenseNumberService,
        private readonly AnalyticsService     $analyticsService,
        private readonly OneTimeLinkService $oneTimeLinkService,
        private readonly SoundEffectSearch $soundEffectSearch
    ) {
        parent::__construct();
    }

    /**
     * @OA\Get(
     *     path="/v1/public/sfx/tracks/search",
     *     summary="Search SFX tracks",
     *     tags={"Track"},
     *     @OA\Parameter(parameter="q", description="Search string", required=false, in="query", name="q"),
     *     @OA\Parameter(parameter="sort", description="Sort", required=false, in="query", name="sort"),
     *     @OA\Parameter(parameter="onlyPremium", description="Only Premium", required=false, in="query", name="onlyPremium"),
     *     @OA\Parameter(parameter="category", description="Category Slug", required=false, in="query", name="category"),
     *     @OA\Parameter(parameter="tag", description="Tag Slug", required=false, in="query", name="tag"),
     *     @OA\Parameter(parameter="perpage", description="Per Page", required=false, in="query", name="perpage"),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="current_page", type="integer"),
     *              @OA\Property(property="data", type="object",
     *                   ref="/components/schemas/SFXTrackResource"
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
     * @param SFXTrackFilter $filter
     * @return JsonResponse
     */
    public function search(SFXTrackFilter $filter): JsonResponse
    {
        try {
            $filter->validate();

            $categories = [
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

            return response()->json($this->soundEffectSearch->searchCustomApi($filter, $q, $sort));
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
     * @param SFXTrackFilter $filter
     * @param string $categoryType
     * @param string $categorySlug
     * @return JsonResponse
     *
     * @OA\Get(
     *     path="/v1/public/sfx/tracks/{categoryType}/{categorySlug}",
     *     summary="Search SFX tracks",
     *     tags={"Track"},
     *     @OA\Parameter(parameter="categoryType", description="Category Type: genres, moods, instruments, types, tags", required=true, in="path", name="categoryType"),
     *     @OA\Parameter(parameter="categorySlug", description="Category Slug", required=true, in="path", name="categorySlug"),
     *     @OA\Parameter(parameter="q", description="Search string", required=false, in="query", name="q"),
     *     @OA\Parameter(parameter="sort", description="Sort", required=false, in="query", name="sort"),
     *     @OA\Parameter(parameter="onlyPremium", description="Only Premium", required=false, in="query", name="onlyPremium"),
     *     @OA\Parameter(parameter="category", description="Category Slug", required=false, in="query", name="category"),
     *     @OA\Parameter(parameter="tag", description="Tag Slug", required=false, in="query", name="tag"),
     *     @OA\Parameter(parameter="perpage", description="Per Page", required=false, in="query", name="perpage"),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="current_page", type="integer"),
     *              @OA\Property(property="data", type="object",
     *                   ref="/components/schemas/SFXTrackResource"
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
     */
//    public function searchByCategorySlug(SFXTrackFilter $filter, string $categoryType, string $categorySlug): JsonResponse
//    {
//        try {
//            $request = $filter->getRequest();
//            $q = $request['q'] ?? null;
//            $sort = $request['sort'] ?? 'trending';
//            $sort = $sort == 'new' ? 'created_at' : $sort;
//
//            return response()->json($this->soundEffectSearch->searchCustomApi($filter, $q, $sort,
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

    public function findCut(?int $trackId): TrackCutResource
    {
        $track = SFXTrack::find($trackId);

        return new TrackCutResource($track);
    }

    public function addToCart(AddToCartRequest $request, OrderService $orderService, LicenseService $licenseService): JsonResponse
    {
        try {
            $license = $licenseService->findSFXLicense($request->input('licenseId'));

            $order = $orderService->findOrCreateFullOrder();
            $cart = $orderService->addSFXOrderItem(
                $order,
                SFXTrack::find($request->input('trackId')),
                $license
            );

            return $this->success([
                'cart' => $cart,
            ]);
        } catch (Exception $e) {
            return $this->errorWrapped($e);
        }
    }

    public function buy(BuyTrackRequest $request, OrderService $orderService, LicenseService $licenseService): JsonResponse
    {
        try {
            $license = $licenseService->findSFXLicense($request->input('licenseId'));

            $paymentLink = $orderService->fastForSFX(
                SFXTrack::find($request->input('trackId')),
                $license,
            );

            return $this->success([
                'paymentLink' => $paymentLink,
            ]);
        } catch (Exception $e) {
            return $this->errorWrapped($e);
        }
    }

    public function subDownload(SFXTrack $sfxTrack): JsonResponse
    {
        abort_if(! request()->has('license_id'), 404, "license not found");

        $license = License::find(request('license_id'));

        $download = UserDownloads::create([
            'user_id'        => auth()->user()->id,
            'track_id'       => $sfxTrack->id,
            'type'           => Env::ITEM_TYPE_EFFECTS,
            'license_number' => $this->licenseNumberService->generate($license),
            'license_id'     => $license->id,
            'class' => SFXTrack::class
        ]);

        if ($download->wasRecentlyCreated) {
            $sfxTrack->params->downloads_by_subscription += 1;
            $sfxTrack->params->save();
        }

        /**
         * @var $user User
         */
        $user = auth()->user();

        $user->increment('downloads');

        $this->analyticsService->sendSubDownload($sfxTrack->name . ' (SFX)');

        $zipUrl = $this->oneTimeLinkService->generateDownloadsZip($sfxTrack->id, $download);
        $licUrl = $this->oneTimeLinkService->generateForUserDownloadLicense($download);

        return $this->success([
            'success' => true,
            'license' => $licUrl,
            'zip' => $zipUrl
        ]);
    }
	
	
	/**
	 * @OA\Get(
	 *     path="/v1/public/sfx/tracks/category/by-slug/{slug}",
	 *     summary="Get SfxTrack categories by slug",
	 *     tags={"Track"},
	 *     security={{"bearerAuth":{}}},
	 *     @OA\Parameter(parameter="slug", description="Slug Track", required=true, in="path", name="slug"),
	 *     @OA\Response(
	 *         response="200",
	 *         description="Success",
	 *         @OA\JsonContent(
	 *              @OA\Property(property="data", type="object"),
	 *         ),
	 *     ),
	 * )
	 *
	 * @param string $slug
	 * @return JsonResponse
	 */
	public function getCategoryBySlug(string $slug): JsonResponse
	{
		$category = SFXCategory::query()->where('slug', $slug)->first();
		
		if (empty($category)) {
			return response()->json(['message' => 'No records found'], 404);
		}
		
		return $this->success([
			'category' => (new CategoryResponse($category))->handle(),
		]);
	}

    /**
     * @param string $slug
     * @return JsonResponse
     *
     * @OA\Get(
     *     path="/v1/public/sfx/tracks/tag/by-slug/{slug}",
     *     summary="Get SfxTrack tag by slug",
     *     tags={"Track"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(parameter="slug", description="Slug SFX", required=true, in="path", name="slug"),
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
        $template = TemplateMeta::where('type', SFXTag::class)->first();
        $tag = SFXTag::with('icon')->where('slug', $slug)->first();
        return response()->json(
            new AbstractTagResource($tag, $template)
        );
    }
}
