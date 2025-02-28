<?php

namespace App\Http\Controllers\Api\Authorized;

use App\Services\TracksService;
use App\Models\Track;
use Exception;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Api\AuthorizedController;
use Illuminate\Http\Request;

/**
 * Class TracksController
 * @package App\Http\Controllers\Api\Authorized
 */
class TracksController extends AuthorizedController
{
    public function __construct(
        private readonly TracksService  $tracksService,
    ) {
        parent::__construct();
    }

    /**
     * @OA\Post(
     *     path="/v1/protected/tracks",
     *     summary="Сreate Track",
     *     tags={"Track"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\MediaType(mediaType="multipart/form-data", @OA\Schema(
     *          @OA\Property(property="name", type="string", description="Name"),
     *          @OA\Property(property="slug", type="string", description="Slug"),
     *          @OA\Property(property="description", type="string", description="Description"),
     *          @OA\Property(property="author_id", type="integer", description="Author ID"),
     *          @OA\Property(property="bpm", type="integer", description="bpm"),
     *          @OA\Property(property="hidden", type="boolean", description="true"),
     *          @OA\Property(property="featured", type="boolean", description="true"),
     *          @OA\Property(property="has_content_id", type="boolean", description="true"),
     *          @OA\Property(property="exclusive", type="boolean", description="true"),
     *          @OA\Property(property="premium", type="boolean", description="true"),
     *          @OA\Property(property="images[background]", type="string", format="binary", description="Background image"),
     *          @OA\Property(property="images[thumbnail]", type="string", format="binary", description="Thumbnail image"),
     *          @OA\Property(property="meta[title]", type="string", description="Meta-Title"),
     *          @OA\Property(property="meta[description]", type="string", description="Meta-Description"),
     *          @OA\Property(property="tags", type="array", description="Tags",
     *              @OA\Items(type="integer")
     *          ),
     *          @OA\Property(property="genres", type="array", description="Tags",
     *              @OA\Items(type="integer")
     *          ),
     *          @OA\Property(property="instruments", type="array", description="Tags",
     *              @OA\Items(type="integer")
     *          ),
     *          @OA\Property(property="moods", type="array", description="Tags",
     *              @OA\Items(type="integer")
     *          ),
     *          @OA\Property(property="types", type="array", description="Tags",
     *              @OA\Items(type="integer")
     *          ),
     *          @OA\Property(property="tracks", type="array", description="Files Track",
     *              @OA\Items(type="string", format="binary")
     *          ),
     *     ))),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              ref="/components/schemas/TrackApiResource"
     *         ),
     *     ),
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createTrack(Request $request): JsonResponse
    {
        $errors = $this->tracksService->validation($request->toArray());
        if (count($errors) > 0)
            return response()->json([
                'message' => $errors
            ], 400);

        try {
            $track = $this->tracksService->create($request);

            return $this->tracksService->saveTrack($request, $track);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/v1/protected/tracks/{track_id}",
     *     summary="Update Track",
     *     tags={"Track"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\MediaType(mediaType="multipart/form-data", @OA\Schema(
     *          @OA\Property(property="name", type="string", description="Name"),
     *          @OA\Property(property="slug", type="string", description="Slug"),
     *          @OA\Property(property="description", type="string", description="Description"),
     *          @OA\Property(property="author_id", type="integer", description="Author ID"),
     *          @OA\Property(property="bpm", type="integer", description="bpm"),
     *          @OA\Property(property="hidden", type="boolean", description="true"),
     *          @OA\Property(property="featured", type="boolean", description="true"),
     *          @OA\Property(property="has_content_id", type="boolean", description="true"),
     *          @OA\Property(property="exclusive", type="boolean", description="true"),
     *          @OA\Property(property="premium", type="boolean", description="true"),
     *          @OA\Property(property="images[background]", type="string", format="binary", description="Background image"),
     *          @OA\Property(property="images[thumbnail]", type="string", format="binary", description="Thumbnail image"),
     *          @OA\Property(property="meta[title]", type="string", description="Meta-Title"),
     *          @OA\Property(property="meta[description]", type="string", description="Meta-Description"),
     *          @OA\Property(property="tags", type="array", description="Tags",
     *              @OA\Items(type="integer")
     *          ),
     *          @OA\Property(property="genres", type="array", description="Tags",
     *              @OA\Items(type="integer")
     *          ),
     *          @OA\Property(property="instruments", type="array", description="Tags",
     *              @OA\Items(type="integer")
     *          ),
     *          @OA\Property(property="moods", type="array", description="Tags",
     *              @OA\Items(type="integer")
     *          ),
     *          @OA\Property(property="types", type="array", description="Tags",
     *              @OA\Items(type="integer")
     *          ),
     *          @OA\Property(property="tracks", type="array", description="Files Track",
     *              @OA\Items(type="string", format="binary")
     *          ),
     *     ))),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              ref="/components/schemas/TrackApiResource"
     *         ),
     *     ),
     * )
     *
     * @param Request $request
     * @param int $track_id
     * @return JsonResponse
     */
    public function updateTrack(Request $request, int $track_id): JsonResponse
    {
        $errors = $this->tracksService->validation($request->toArray(), $track_id);
        if (count($errors) > 0)
            return response()->json([
                'message' => $errors
            ], 400);

        try {
            $track = Track::where('id', $track_id)->first();
            $track = $this->tracksService->updateTrack($request, $track);

            return $this->tracksService->saveTrack($request, $track);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
