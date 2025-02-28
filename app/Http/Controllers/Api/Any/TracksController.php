<?php

namespace App\Http\Controllers\Api\Any;

use App\Actions\CategoryResponse;
use App\Exceptions\EmptySearchResult;
use App\Http\Resources\Any\AbstractTagResource;
use App\Models\Structure\TemplateMeta;
use App\Models\Tags\Tag;
use App\Services\TracksService;
use App\Services\MailerLite\MailerLiteService;
use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Track;
use App\Models\TrackAudio;
use App\Libs\PartnerProgram;
use Illuminate\Http\Request;
use App\Models\UserDownloads;
use App\Jobs\ConvertWavToMp3;
use App\Services\AudioService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use App\Services\AnalyticsService;
use App\Services\OneTimeLinkService;
use App\Services\PartnerProgramService;
use App\Services\AudioStreamingService;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Api\TrackResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Resources\Any\Collection\TrackResource as TrackCollectionResource;
use App\Services\SearchStrategies\TrackSearch;
use App\Filters\TrackFilter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class TracksController
 *
 * @package App\Http\Controllers\Api
 */
class TracksController extends ApiController
{
    protected $resource = TrackCollectionResource::class;

    public function __construct(
        private readonly AnalyticsService   $analyticsService,
        private readonly OneTimeLinkService $oneTimeLinkService,
        private readonly AudioService       $audioService,
        private readonly TrackSearch $trackSearch
    ) {
        parent::__construct();
    }

    /**
     * @OA\Get(
     *     path="/v1/public/tracks/{track_id}",
     *     summary="Find track",
     *     tags={"Track"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(parameter="track", description="ID Track", required=true, in="path", name="track", example="7"),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="object", ref="#/components/schemas/TrackResource"),
     *         ),
     *     ),
     * )
     *
     * @param string|null $track_id
     * @return JsonResponse
     */
    public function findById(?string $track_id): JsonResponse
    {
        try {
            $track = Track::where('id', $track_id)
                ->with('author', 'archive', 'audio',
                    'genres', 'moods', 'instruments', 'types', 'tags')->firstOrFail();
            return response()->json(new TrackResource($track));
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * @OA\Get(
     *     path="/v1/public/tracks/by-slug/{slug}",
     *     summary="Get list tracks by name",
     *     tags={"Track"},
     *     @OA\Parameter(parameter="slug", description="Slug Track", required=true, in="path", name="slug"),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="object", ref="#/components/schemas/TrackResource"),
     *         ),
     *     ),
     * )
     *
     * @param $slug
     * @return JsonResponse
     */
    public function findBySlug($slug): JsonResponse
    {
        try {
            $track = Track::with('author', 'prices', 'audio')->where('slug', $slug)->firstOrFail();
            return response()->json(new TrackResource($track));
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 404);
        }
    }

    public function getForMain(): JsonResponse
    {
        return $this->wrapCall(TracksService::class, 'getForMain');
    }

    public function single(Track $track, TrackAudio $audio): void
    {
        $this->stream($track, $audio);
    }

    public function loop(Track $track, TrackAudio $audio): void
    {
        $this->stream($track, $audio);
    }

    protected function checkAccess(Track $track, TrackAudio $audio)
    {
        abort_if($audio->track_id !== $track->id, 500, "Can't play this song");

        return storage_path($audio->url);
    }

    protected function stream($track, $audio, $iteration = 0, $exception = null)
    {
        $audioUrl = $this->checkAccess($track, $audio);

        $filename = pathinfo($audioUrl, PATHINFO_FILENAME);
        $mp3 = "$filename.mp3";

        $exceptionMessage = $exception ? $exception->getMessage() : "";
        abort_if($iteration >= 2, 500, "File not found {$mp3} {$exceptionMessage}");

        try {
            $stream = new AudioStreamingService("mp3s/{$mp3}");

            $stream->start();
        } catch (Exception $exception) {
            ConvertWavToMp3::dispatchSync($track->id);

            $this->stream($track, $audio, ++$iteration, $exception);
        }
    }

    public function addToCart(Track $track): JsonResponse
    {
        return $this->wrapCall(OrderService::class, 'full', $track);
    }

    public function prices(Track $track): JsonResponse
    {
        return $this->success($track->prices);
    }

    public function download(Track $track, TrackAudio $audio): JsonResponse
    {
        abort_if($audio->track_id !== $track->id, 404, "Song not found");
        abort_if($audio->getFormatAttribute() !== "mp3", 404, "Song not found");

        $userDownloadsFillData = [
            'type' => 'preview-download',
            'track_id' => $track->id,
            'class' => Track::class,
        ];

        /**
         * @var $user User
         */
        $user = auth()->user();

        if ($user) {
            $user->increment('previews');
            $user->last_preview_download = Carbon::now();
            $user->save();
            $user->refresh();

            PartnerProgramService::writeEarnings($user, 0, PartnerProgram::EARNING_SOURCE_PREVIEW);

            resolve(MailerLiteService::class)->setUser($user)->updateUser();

            $userDownloadsFillData['user_id'] = $user->id;
        }

        UserDownloads::create($userDownloadsFillData);

        $this->analyticsService->sendPreviewDownload($audio);

        $url = $this->oneTimeLinkService->generate(
            'p',
            ['di' => $audio->id]
        );

        return $this->success([
            'success' => true,
            'link' => $url,
        ]);
    }

    /**
     * @param TrackFilter $filter
     * @return JsonResponse
     * @OA\Get(
     *     path="/v1/public/tracks/search",
     *     summary="Search tracks",
     *     tags={"Track"},
     *     @OA\Parameter(parameter="q", description="Search string", required=false, in="path", name="slug"),
     *     @OA\Parameter(parameter="sort", description="Sort", required=false, in="query", name="sort"),
     *     @OA\Parameter(parameter="bpmMin", description="BPM min", required=false, in="query", name="bpmMin"),
     *     @OA\Parameter(parameter="bpmMax", description="BPM max", required=false, in="query", name="bpmMax"),
     *     @OA\Parameter(parameter="onlyPremium", description="Only Premium", required=false, in="query", name="onlyPremium"),
     *     @OA\Parameter(parameter="author", description="Author Slug", required=false, in="query", name="author"),
     *     @OA\Parameter(parameter="genre", description="Genre Slug", required=false, in="query", name="genre"),
     *     @OA\Parameter(parameter="mood", description="Mood Slug", required=false, in="query", name="mood"),
     *     @OA\Parameter(parameter="instrument", description="Mood Slug", required=false, in="query", name="instrument"),
     *     @OA\Parameter(parameter="usageType", description="Usage Type Slug", required=false, in="query", name="usageType"),
     *     @OA\Parameter(parameter="tag", description="Tag Slug", required=false, in="query", name="tag"),
     *     @OA\Parameter(parameter="perpage", description="Per Page", required=false, in="query", name="perpage"),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="current_page", type="integer"),
     *              @OA\Property(property="data", type="object",
     *                   ref="/components/schemas/TrackSearchResource"
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
    public function search(TrackFilter $filter): JsonResponse
    {
        try {
            $filter->validate();

            $categories = [
                'genre',
                'mood',
                'instrument',
                'usageType',
                'tag',
                'curatorPick'
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

            return response()->json($this->trackSearch->searchCustomApi($filter, $q, $sort));
        } catch (EmptySearchResult $e) {
			return response()->json([
				'message' => $e->getMessage()
			], 404);
		} catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @param TrackFilter $filter
     * @param string $categoryType
     * @param string $categorySlug
     * @return JsonResponse
     *
     *
     *  @OA\Get(
     *     path="/v1/public/tracks/{categoryType}/{categorySlug}",
     *     summary="Search tracks",
     *     tags={"Track"},
     *     @OA\Parameter(parameter="categoryType", description="Category Type: genres, moods, instruments, types, tags", required=true, in="path", name="categoryType"),
     *     @OA\Parameter(parameter="categorySlug", description="Category Slug", required=true, in="path", name="categorySlug"),
     *     @OA\Parameter(parameter="q", description="Search string", required=false, in="query", name="q"),
     *     @OA\Parameter(parameter="sort", description="Sort", required=false, in="query", name="sort"),
     *     @OA\Parameter(parameter="bpmMin", description="BPM min", required=false, in="query", name="bpmMin"),
     *     @OA\Parameter(parameter="bpmMax", description="BPM max", required=false, in="query", name="bpmMax"),
     *     @OA\Parameter(parameter="onlyPremium", description="Only Premium", required=false, in="query", name="onlyPremium"),
     *     @OA\Parameter(parameter="author", description="Author Slug", required=false, in="query", name="author"),
     *     @OA\Parameter(parameter="genre", description="Genre Slug", required=false, in="query", name="genre"),
     *     @OA\Parameter(parameter="mood", description="Mood Slug", required=false, in="query", name="mood"),
     *     @OA\Parameter(parameter="instrument", description="Mood Slug", required=false, in="query", name="instrument"),
     *     @OA\Parameter(parameter="usageType", description="Usage Type Slug", required=false, in="query", name="usageType"),
     *     @OA\Parameter(parameter="tag", description="Tag Slug", required=false, in="query", name="tag"),
     *     @OA\Parameter(parameter="perpage", description="Per Page", required=false, in="query", name="perpage"),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="current_page", type="integer"),
     *              @OA\Property(property="data", type="object",
     *                   ref="/components/schemas/TrackSearchResource"
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
//    public function searchByCategorySlug(TrackFilter $filter, string $categoryType, string $categorySlug): JsonResponse
//    {
//        try {
//            $request = $filter->getRequest();
//            $q = $request['q'] ?? null;
//            $sort = $request['sort'] ?? 'trending';
//            $sort = $sort == 'new' ? 'created_at' : $sort;
//
//            return response()->json($this->trackSearch->searchCustomApi($filter, $q, $sort,
//                categoryType: $categoryType, categorySlug: $categorySlug));
//        } catch (EmptySearchResult $e) {
//            return response()->json([
//                'message' => $e->getMessage()
//            ], 404);
//        } catch (Exception $e) {
//            return response()->json([
//                'message' => $e->getMessage()
//            ], 500);
//        }
//    }

    /**
     * @OA\Get(
     *     path="/v1/public/tracks/{slug}/similar",
     *     summary="Similar track",
     *     tags={"Track"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(parameter="slug", description="Slug Track", required=true, in="path", name="slug"),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="object", ref="#/components/schemas/TrackResource"),
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
            $track = Track::where('slug', $slug)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 404);
        }

        $words = $track->mix?->text ?? '';
        $tracksIds = collect(DB::select("select track_elastics.track_id
            from track_elastics join tracks on track_elastics.track_id = tracks.id
            where MATCH(track_elastics.text) AGAINST(?) AND track_elastics.track_type = ?
            AND track_elastics.track_id != ? AND tracks.deleted_at is null AND tracks.hidden = 0 LIMIT 6",
            [$words, Track::class, $track->id]))->map(fn($item) => $item->track_id);

        return response()->json(Track::whereIn('id', $tracksIds)
            ->with('author', 'archive', 'audio', 'genres',
                'moods', 'instruments', 'types', 'tags')
            ->get()->map(fn($item) => new TrackResource($item)));
    }

    public function findOriginal(Track $track): JsonResponse
    {
        return $this->success([
            'audio' => $track->audio->map(function ($item) {
                return [
                    'url' => $item->static_url,
                    'id' => $item->id,
                ];
            }),
        ]);
    }

    public function createAudio(Track $track, Request $request): JsonResponse
    {
        $index = $request->get('index');

        $result = $this->audioService->upload($track, $request);

        return $this->success([
            'audio' => $result,
            'index' => $index,
        ]);
    }
	
	
	/**
	 * @OA\Get(
	 *     path="/v1/public/tracks/category/by-slug/{slug}",
	 *     summary="Get Track categories by slug",
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
		$model = new Track();
		
		$category = $model->getCategoryBySlug($slug);
		
		if (empty($category)) {
			return response()->json(['message' => 'No records found'], 404);
		}
		
		return $this->success([
			'category' => (new CategoryResponse($category))->handle()
		]);
	}

    /**
     * @param string $slug
     * @return JsonResponse
     *
     * @OA\Get(
     *     path="/v1/public/tracks/tag/by-slug/{slug}",
     *     summary="Get Track tags by slug",
     *     tags={"Track"},
     *     security={{"bearerAuth":{}}},
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
        $template = TemplateMeta::where('type', Tag::class)->first();
        $tag = Tag::with('icon')->where('slug', $slug)->first();
        return response()->json(
            new AbstractTagResource($tag, $template)
        );
    }
}
