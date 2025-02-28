<?php

namespace App\Http\Controllers\Api\Any;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Api\UserResource;
use App\Models\User;
use App\Services\OrderService;
use Exception;
use Illuminate\Http\JsonResponse;

class UserController extends ApiController
{
    protected $resource = UserResource::class;

    /**
     * Find latest order and show it as cart
     *
     * @OA\Get(
     *     path="/v1/public/me/cart",
     *     summary="Find latest order and show it as cart",
     *     tags={"Me"},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="subscription", type="object",
     *                      ref="#/components/schemas/OrderResource"
     *                  ),
     *              ),
     *         ),
     *     ),
     * )
     *
     * @responseFile responses/admin/order.json
     *
     * @return JsonResponse
     */
    public function cart()
    {
        return $this->wrapCall(OrderService::class, 'cart');
    }

    /**
     * Returns data for mini-cart (amount, sum)
     *
     * @OA\Get(
     *     path="/v1/public/me/mini-cart",
     *     summary="Returns data for mini-cart (amount, sum)",
     *     tags={"Me"},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="count", type="integer", example="10"),
     *                  @OA\Property(property="sum", type="integer", example="20"),
     *              ),
     *         ),
     *     ),
     * )
     *
     * @return JsonResponse
     */
    public function miniCart(): JsonResponse
    {
        $res = $this->wrapCall(OrderService::class, 'cart');

        $data = $res->getData(true)['data'] ?? [];

        return $this->success([
            'count' => isset($data[ 'items' ]) ? count($data[ 'items' ]) : 0,
            'sum'   => $data[ 'sum' ] ?? 0,
        ]);
    }

    /**
     *
     * @OA\Post(
     *     path = "/v1/protected/me/confirm",
     *     summary = "Confirm User",
     *     tags={"Me"},
     *     @OA\RequestBody(required=true, @OA\MediaType(mediaType="multipart/form-data", @OA\Schema(
     *          @OA\Property(property="code", type="string", description="code"),
     *     ))),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="success", type="boolean", example="true")
     *              )
     *         )
     *     ),
     * )
     *
     * @throws Exception
     */
    public function confirmUser(): JsonResponse
    {
        $code = request('code');

        $user = User::where('confirmation_code', $code)->first();
        if (!$user) {
            throw new Exception("user not found", 404);
        }

        $user->confirmed = true;
        $user->save();

        return $this->success([
            'success' => true
        ]);
    }
}
