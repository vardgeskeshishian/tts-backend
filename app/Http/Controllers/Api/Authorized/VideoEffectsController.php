<?php


namespace App\Http\Controllers\Api\Authorized;

use App\Services\OrderService;
use App\Services\VideoEffectsService;
use App\Models\VideoEffects\VideoEffect;
use App\Http\Controllers\Api\ApiController;
use Exception;
use Illuminate\Http\JsonResponse;

class VideoEffectsController extends ApiController
{
    /**
     * @var VideoEffectsService
     */
    private VideoEffectsService $effectsService;
    /**
     * @var OrderService
     */
    private OrderService $orderService;

    /**
     * VideoEffectsController constructor.
     *
     * @param VideoEffectsService $effectsService
     * @param OrderService $orderService
     */
    public function __construct(
        VideoEffectsService $effectsService,
        OrderService $orderService
    ) {
        $this->effectsService = $effectsService;
        $this->orderService = $orderService;
    }

    /**
     * @OA\Post(
     *     path="/v1/protected/video-effects",
     *     summary="Create Video Effects",
     *     tags={"Video Effects"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\MediaType(mediaType="multipart/form-data", @OA\Schema(
     *          @OA\Property(property="name", type="string", description="Name"),
     *          @OA\Property(property="description", type="string", description="description"),
     *          @OA\Property(property="author", type="integer", description="ID Author Profile"),
     *          @OA\Property(property="author_name", type="string", description="Author Name"),
     *          @OA\Property(property="standard_price", type="float", description="Standard Price"),
     *          @OA\Property(property="extended_price", type="float", description="Extended Price"),
     *          @OA\Property(property="tags", type="array", description="Tags", @OA\Items(
     *              type="string"
     *          ))
     *     ))),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="object", ref="#/components/schemas/VideoEffectProtectedResource"),
     *         ),
     *     ),
     * )
     *
     * @return JsonResponse
     */
    public function actionCreate(): JsonResponse
    {
        $this->checkAccess();

        try {
            return $this->success($this->effectsService->create());
        } catch (Exception $e) {
            return $this->errorWrapped($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/v1/protected/video-effects",
     *     summary="Get list video-effects",
     *     tags={"Video Effects"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(parameter="name", description="Name Track", required=true, in="path", name="name", example="Disco Funk"),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/VideoEffectProtectedResource")),
     *         ),
     *     ),
     * )
     *
     * @return JsonResponse
     */
    public function actionList(): JsonResponse
    {
        $this->checkAccess();

        try {
            return $this->success($this->effectsService->getForAuthor());
        } catch (Exception $e) {
            return $this->errorWrapped($e);
        }
    }

    /**
     * @OA\Put(
     *     path="/v1/protected/video-effects/{videoEffect}",
     *     summary="Update Video Effects",
     *     tags={"Video Effects"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(parameter="videoEffect", description="ID Video Effect", required=true, in="path", name="videoEffect", example="7"),
     *     @OA\RequestBody(required=true, @OA\MediaType(mediaType="multipart/form-data", @OA\Schema(
     *          @OA\Property(property="name", type="string", description="Name"),
     *          @OA\Property(property="description", type="string", description="description"),
     *          @OA\Property(property="author", type="integer", description="ID Author Profile"),
     *          @OA\Property(property="author_name", type="string", description="Author Name"),
     *          @OA\Property(property="standard_price", type="float", description="Standard Price"),
     *          @OA\Property(property="extended_price", type="float", description="Extended Price"),
     *          @OA\Property(property="tags", type="array", description="Tags", @OA\Items(
     *              type="string"
     *          ))
     *     ))),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="object", ref="#/components/schemas/VideoEffectProtectedResource"),
     *         ),
     *     ),
     * )
     *
     * @param VideoEffect $videoEffect
     * @return JsonResponse
     */
    public function actionUpdate(VideoEffect $videoEffect): JsonResponse
    {
        $this->checkAccess();

        try {
            return $this->success($this->effectsService->update($videoEffect));
        } catch (Exception $e) {
            return $this->errorWrapped($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/v1/protected/video-effects/{videoEffect}/files",
     *     summary="Action Upload files",
     *     tags={"Video Effects"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(parameter="videoEffect", description="ID Video Effect", required=true, in="path", name="videoEffect", example="7"),
     *     @OA\RequestBody(required=true, @OA\MediaType(mediaType="multipart/form-data", @OA\Schema(
     *          @OA\Property(property="preview_video", type="string", format="binary", description="preview_video"),
     *          @OA\Property(property="preview_photo", type="string", format="binary", description="preview_photo"),
     *          @OA\Property(property="zip", type="string", format="binary", description="zip"),
     *     ))),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="object", ref="#/components/schemas/VideoEffectProtectedResource"),
     *         ),
     *     ),
     * )
     *
     * @param VideoEffect $videoEffect
     * @return JsonResponse
     */
    public function actionUploadFiles(VideoEffect $videoEffect): JsonResponse
    {
        $this->checkAccess();

        try {
            return $this->success(
                $this->effectsService->uploadFiles($videoEffect)
            );
        } catch (Exception $e) {
            return $this->errorWrapped($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/v1/protected/video-effects/{videoEffect}/comments",
     *     summary="Action Add To Comment",
     *     tags={"Video Effects"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(parameter="videoEffect", description="ID Video Effect", required=true, in="path", name="videoEffect", example="7"),
     *     @OA\RequestBody(required=true, @OA\MediaType(mediaType="multipart/form-data", @OA\Schema(
     *          @OA\Property(property="user_comment", type="string", description="user_comment"),
     *     ))),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="object", ref="#/components/schemas/VideoEffectProtectedResource"),
     *         ),
     *     ),
     * )
     *
     * @param VideoEffect $videoEffect
     * @return JsonResponse
     */
    public function actionAddComment(VideoEffect $videoEffect): JsonResponse
    {
        $this->checkAccess();

        try {
            return $this->success(
                $this->effectsService->addUserComment($videoEffect)
            );
        } catch (Exception $e) {
            return $this->errorWrapped($e);
        }
    }

    /**
     * @OA\Delete(
     *     path="/v1/protected/video-effects/{videoEffect}",
     *     summary="Delete Video Effects",
     *     tags={"Video Effects"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(parameter="videoEffect", description="ID Video Effect", required=true, in="path", name="videoEffect", example="7"),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/VideoEffectProtectedResource")),
     *         ),
     *     ),
     * )
     *
     * @param VideoEffect $videoEffect
     * @return JsonResponse
     */
    public function actionDelete(VideoEffect $videoEffect): JsonResponse
    {
        $videoEffect->delete();

        return $this->success($this->effectsService->getForAuthor());
    }
}
