<?php

namespace App\Http\Controllers\Api\Authorized;

use App\Http\Controllers\Api\AuthorizedController;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends AuthorizedController
{
    /**
     * @OA\Post(
     *     path = "/v1/protected/auth/logout",
     *     summary = "Logout",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean"),
     *         ),
     *     ),
     * )
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function logout(): JsonResponse
    {
        auth()->logout();
        JWTAuth::invalidate(JWTAuth::getToken());

        return $this->success([
            'status' => 'ok'
        ]);
    }

    /**
     * @OA\Post(
     *     path = "/v1/protected/auth/change-password",
     *     summary = "Change Password",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\MediaType(mediaType="multipart/form-data", @OA\Schema(
     *          @OA\Property(property="old_password", type="string", description="Old Password"),
     *          @OA\Property(property="new_password", type="string", description="New Password"),
     *     ))),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *     ),
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function changePassword(Request $request): JsonResponse
    {
        $oldPassword = $request->input('old_password');
        $newPassword = $request->input('new_password');

        if ($oldPassword === $newPassword)
        {
            return response()->json([
                'message' => 'The new password cannot be the same as the old password'
            ], 401);
        }

        $user = auth()->user();

        $hasher = app('hash');
        if ($hasher->check($oldPassword, $user->password)) {
            $user->update([
                'password' => bcrypt($newPassword)
            ]);

            return response()->json([
                'message' => 'Password changed successfully'
            ]);
        } else {
            return response()->json([
                'message' => 'The old password was entered incorrectly'
            ], 401);
        }
    }
}
