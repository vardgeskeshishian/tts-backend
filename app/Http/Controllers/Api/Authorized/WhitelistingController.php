<?php

namespace App\Http\Controllers\Api\Authorized;

use App\Http\Controllers\Api\AuthorizedController;
use App\Models\UserFavoritesFolder;
use App\Services\FavoritesFolderService;
use App\Services\WhitelistingService;
use DB;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Log;

/**
 * @group Whitelisting
 *
 * Class WhitelistingController
 * @package App\Http\Controllers\Api\Authorized
 */
class WhitelistingController extends AuthorizedController
{

    private WhitelistingService $whitelistingService;

    public function __construct(
        WhitelistingService $whitelistingService,
    )
    {
        parent::__construct();
        $this->whitelistingService = $whitelistingService;
    }

    /**
     * Search channels
     *
     * @OA\Get(
     *     path="/v1/protected/whitelisting/search",
     *     summary="Search channels",
     *     tags={"Whitelisting"},
     *     @OA\Parameter(parameter="q", description="Search string", required=true, in="query", name="q"),
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="object",
     *                   @OA\Property(property="success", type="boolean", example="true"),
     *                   @OA\Property(property="channels", type="array", @OA\Items(
     *                        @OA\Property(property="id", type="string", example="UCfWAQKWpYS5WyHHYjQAScWT"),
     *                        @OA\Property(property="text", type="string", example="Channel name"),
     *                        @OA\Property(property="img", type="string", example="https://image-path"),
     *                   )),
     *               )
     *         ),
     *     ),
     * )
     *
     */
    public function searchChannels(Request $request)
    {
        try {
            $response = $this->whitelistingService->search($request->get('q') ?? '');
        } catch (\Exception $e) {
            return response(['message' => $e->getMessage(), 'code' => $e->getCode()], $e->getCode() >= 200 && $e->getCode() <= 500 ? $e->getCode() : 400);
        }
        return $this->success([
            'success' => true,
            'channels' => $response,
        ]);
    }

    /**
     * Add channel to whitelist
     *
     * @OA\Post(
     *     path="/v1/protected/whitelisting/{channelId}",
     *     summary="Add channel to whitelist",
     *     tags={"Whitelisting"},
     *     @OA\Parameter(parameter="channelId", description="channel id", required=true, in="path", name="channelId"),
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="object",
     *                   @OA\Property(property="success", type="boolean", example="true"),
     *                   @OA\Property(property="channels", type="array", @OA\Items(
     *                        @OA\Property(property="id", type="string", example="UCfWAQKWpYS5WyHHYjQAScWT"),
     *                        @OA\Property(property="text", type="string", example="Channel name"),
     *                        @OA\Property(property="img", type="string", example="https://image-path"),
     *                   )),
     *               )
     *         ),
     *     ),
     * )
     *
     */
    public function add(string $channel)
    {
        try {
			DB::beginTransaction();
            $user = auth()->user();
            if (!$this->whitelistingService->checkCreate($user)) {
                return response(['message' => 'The limit has been reached', 'code' => 400], 400);
            }
            $this->whitelistingService->add($user, $channel);
			DB::commit();
        } catch (\Exception | \Throwable $e) {
			DB::rollBack();
            return response(['message' => $e->getMessage(), 'code' => $e->getCode()], $e->getCode() >= 200 && $e->getCode() <= 500 ? $e->getCode() : 400);
        }
		return $this->success([
            'success' => true,
        ]);
    }

    /**
     * Remove channel from whitelist
     *
     * @OA\Delete(
     *     path="/v1/protected/whitelisting/{channelId}",
     *     summary="Remove channel from whitelist",
     *     tags={"Whitelisting"},
     *     @OA\Parameter(parameter="channelId", description="channel id", required=true, in="path", name="channelId"),
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="object",
     *                   @OA\Property(property="success", type="boolean", example="true"),
     *                   @OA\Property(property="channels", type="array", @OA\Items(
     *                        @OA\Property(property="id", type="string", example="UCfWAQKWpYS5WyHHYjQAScWT"),
     *                        @OA\Property(property="text", type="string", example="Channel name"),
     *                        @OA\Property(property="img", type="string", example="https://image-path"),
     *                   )),
     *               )
     *         ),
     *     ),
     * )
     *
     */
    public function remove(string $channel)
    {
        try {
			DB::beginTransaction();
            $user = auth()->user();
            $this->whitelistingService->remove($user, $channel);
			DB::commit();
        } catch (\Exception | \Throwable $e) {
			DB::rollBack();
			Log::debug(__METHOD__, [
				'trace' => $e->getTraceAsString(),
			]);
            return response(['message' => $e->getMessage(), 'code' => $e->getCode(), 'line' => $e->getLine()], $e->getCode() >= 200 && $e->getCode() <= 500 ? $e->getCode() : 400);
        }
		return $this->success([
            'success' => true,
        ]);
    }

    /**
     * Get whitelist channels
     *
     * @OA\Get(
     *     path="/v1/protected/whitelisting",
     *     summary="Get whitelist channels",
     *     tags={"Whitelisting"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="object",
     *                   @OA\Property(property="success", type="boolean", example="true"),
     *                   @OA\Property(property="channels", type="array", @OA\Items(
     *                        @OA\Property(property="id", type="string", example="UCfWAQKWpYS5WyHHYjQAScWT"),
     *                        @OA\Property(property="text", type="string", example="Channel name"),
     *                        @OA\Property(property="img", type="string", example="https://image-path"),
     *                   )),
     *               )
     *         ),
     *     ),
     * )
     *
     */
    public function getWhitelist()
    {
        try {
            $user = auth()->user();
            $response = $this->whitelistingService->getWhitelist($user);
        } catch (\Exception $e) {
            return response(['message' => $e->getMessage(), 'code' => $e->getCode()], $e->getCode() >= 200 && $e->getCode() <= 500 ? $e->getCode() : 400);
        }
        return $this->success([
            'success' => true,
            'channels' => $response
        ]);
    }
}
