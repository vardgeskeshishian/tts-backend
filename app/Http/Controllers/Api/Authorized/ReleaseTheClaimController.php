<?php

namespace App\Http\Controllers\Api\Authorized;

use App\Http\Controllers\Api\AuthorizedController;
use App\Services\ReleaseTheClaimService;
use Illuminate\Http\Request;

/**
 * @group ReleaseTheClaim
 *
 * Class ReleaseTheClaimController
 * @package App\Http\Controllers\Api\Authorized
 */
class ReleaseTheClaimController extends AuthorizedController
{
    private ReleaseTheClaimService $releaseTheClaimService;

    public function __construct(
        ReleaseTheClaimService $releaseTheClaimService,
    )
    {
        parent::__construct();
        $this->releaseTheClaimService = $releaseTheClaimService;
    }

    /**
     * Search channels
     *
     * @OA\Get(
     *     path="/v1/protected/release-the-claim/search",
     *     summary="Search video",
     *     tags={"ReleaseTheClaim"},
     *     @OA\Parameter(parameter="q", description="Link", required=true, in="query", name="q"),
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="object",
     *                   @OA\Property(property="success", type="boolean", example="true"),
     *                   @OA\Property(property="video", type="object",
     *                        @OA\Property(property="id", type="string", example="UCfWAQKWp"),
     *                        @OA\Property(property="text", type="string", example="Video name"),
     *                        @OA\Property(property="img", type="string", example="https://image-path"),
     *                   ),
     *               )
     *         ),
     *     ),
     * )
     *
     */
    public function searchVideos(Request $request)
    {
        try {
            $response = $this->releaseTheClaimService->search($request->get('q') ?? '');
        } catch (\Exception $e) {
            return response(['message' => $e->getMessage(), 'code' => $e->getCode()], $e->getCode() >= 200 && $e->getCode() <= 500 ? $e->getCode() : 400);
        }
        return $this->success([
            'success' => true,
            'video' => $response,
        ]);
    }


    /**
     * Add video to release the claim list
     *
     * @OA\Post(
     *     path="/v1/protected/release-the-claim/{videoId}",
     *     summary="Add video to release the claim list",
     *     tags={"ReleaseTheClaim"},
     *     @OA\Parameter(parameter="videoId", description="video id", required=true, in="path", name="videoId"),
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="object",
     *                   @OA\Property(property="success", type="boolean", example="true"),
     *                   @OA\Property(property="videos", type="array", @OA\Items(
     *                         @OA\Property(property="id", type="string", example="UCfWAQKWpYS5WyHHYjQAScWT"),
     *                         @OA\Property(property="text", type="string", example="Video name"),
     *                         @OA\Property(property="img", type="string", example="https://image-path"),
     *                    )),
     *               )
     *         ),
     *     ),
     * )
     *
     */
    public function add(string $video)
    {
        try {
            $user = auth()->user();
            if (!$this->releaseTheClaimService->checkCreate($user)) {
                return response(['message' => 'The limit has been reached', 'code' => 400], 400);
            }
            $this->releaseTheClaimService->add($user, $video);
			return $this->success([
				'success' => true,
			]);
        } catch (\Exception $e) {
            return response(['message' => $e->getMessage(), 'code' => $e->getCode()], $e->getCode() >= 200 && $e->getCode() <= 500 ? $e->getCode() : 400);
        }
    }

    /**
     * Remove video from release the claim list
     *
     * @OA\Delete(
     *     path="/v1/protected/release-the-claim/{videoId}",
     *     summary="Remove video from release the claim list",
     *     tags={"ReleaseTheClaim"},
     *     @OA\Parameter(parameter="videoId", description="video id", required=true, in="path", name="videoId"),
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="object",
     *                   @OA\Property(property="success", type="boolean", example="true"),
     *                       @OA\Property(property="videos", type="array", @OA\Items(
     *                          @OA\Property(property="id", type="string", example="UCfWAQKWpYS5WyHHYjQAScWT"),
     *                          @OA\Property(property="text", type="string", example="Video name"),
     *                          @OA\Property(property="img", type="string", example="https://image-path"),
     *                     )),
     *               )
     *         ),
     *     ),
     * )
     *
     */
    public function remove(string $video)
    {
        try {
            $user = auth()->user();
            $this->releaseTheClaimService->remove($user, $video);
			return $this->success([
				'success' => true,
			]);
        } catch (\Exception $e) {
            return response(['message' => $e->getMessage(), 'code' => $e->getCode()], $e->getCode() >= 200 && $e->getCode() <= 500 ? $e->getCode() : 400);
        }
    }

    /**
     * Get release the claim videos
     *
     * @OA\Get(
     *     path="/v1/protected/release-the-claim",
     *     summary="Get release the claim videos",
     *     tags={"ReleaseTheClaim"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="object",
     *                   @OA\Property(property="success", type="boolean", example="true"),
     *                       @OA\Property(property="videos", type="array", @OA\Items(
     *                          @OA\Property(property="id", type="string", example="UCfWAQKWpYS5WyHHYjQAScWT"),
     *                          @OA\Property(property="text", type="string", example="Video name"),
     *                          @OA\Property(property="img", type="string", example="https://image-path"),
     *                     )),
     *               )
     *         ),
     *     ),
     * )
     *
     */
    public function getReleaseTheClaimList()
    {
        try {
            $user = auth()->user();
            $response = $this->releaseTheClaimService->getReleaseTheClaimList($user);
        } catch (\Exception $e) {
            return response(['message' => $e->getMessage(), 'code' => $e->getCode()], $e->getCode() >= 200 && $e->getCode() <= 500 ? $e->getCode() : 400);
        }
        return $this->success([
            'success' => true,
            'videos' => $response
        ]);
    }
}
