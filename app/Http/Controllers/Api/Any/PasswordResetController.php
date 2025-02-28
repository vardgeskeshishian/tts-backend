<?php

namespace App\Http\Controllers\Api\Any;

use App\Http\Requests\ForgetPasswordRequest;
use App\Http\Controllers\Api\ApiController;
use App\Models\User;
use DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Password;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class PasswordResetController extends ApiController
{
    /**
     * Take user email for creating token and send to email
     *
     * @OA\Post(
     *     path="/v1/public/forgot-password",
     *     summary="Password reset",
     *     tags={"Password reset"},
     *     @OA\RequestBody(required=true, @OA\MediaType(mediaType="multipart/form-data", @OA\Schema(
     *           @OA\Property(property="email", type="string", example="example@gmail.com"),
     *     ))),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="status", type="string", example="We have emailed your password reset link"),
     *              ),
     *         ),
     *     ),
     * )
     *
     * @param ForgetPasswordRequest $request
     * @return JsonResponse
     */
    public function forgetPassword(ForgetPasswordRequest $request): JsonResponse
    {
		$status = Password::sendResetLink(
			$request->only('email')
		);
		
		return $status === Password::RESET_LINK_SENT
			? response()->json(['status' => __($status)])
			: response()->json(['email' => __($status)]);
    }
	
	
	/**
	 * Take user email for creating token and send to email
	 *
	 * @OA\Post(
	 *     path="/v1/public/forgot-password-check",
	 *     summary="Password reset check",
	 *     tags={"Password reset"},
	 *     @OA\RequestBody(required=true, @OA\MediaType(mediaType="multipart/form-data", @OA\Schema(
	 *           @OA\Property(property="email", type="string", example="example@gmail.com"),
	 *           @OA\Property(property="token", type="string", example="123456789789"),
	 *     ))),
	 *     @OA\Response(
	 *         response="200",
	 *         description="Success",
	 *         @OA\JsonContent(
	 *              @OA\Property(property="data", type="object",
	 *                  @OA\Property(property="status", type="boolean", example="true"),
	 *              ),
	 *         ),
	 *     ),
	 * )
	 *
	 * @return JsonResponse
	 */
	public function forgetPasswordCheck(Request $request): JsonResponse
	{
		$request->validate([
			'email' => [
				'required',
				Rule::exists(User::class, 'email'),
				Rule::exists('password_resets', 'email'),
			],
		]);
		
		$record = DB::table('password_resets')->where('email', $request->input('email'))->first();
		
		if(empty($record)){
			return response()->json([
				'status' => false,
				'message' => 'Record not exists',
			], 400);
		}
		
		if(Carbon::parse($record->created_at)->addSeconds(60 * 60)->isPast()){
			return response()->json([
				'status' => false,
				'message' => 'Token is expired, try again',
			], 400);
		}
		
		if(!Hash::check($request->input('token'), $record->token)){
			return response()->json([
				'status' => false,
				'message' => 'Wrong token',
			], 400);
		}
		
		return response()->json(['status' => true]);
	}

    /**
     *
     * @OA\Post(
     *     path = "/v1/public/reset-password",
     *     summary = "Update Password",
     *     tags={"Password reset"},
     *     @OA\RequestBody(required=true, @OA\MediaType(mediaType="multipart/form-data", @OA\Schema(
     *          @OA\Property(property="email", type="string", description="example@gmail.com"),
	 *          @OA\Property(property="token", type="string", example="123456789"),
	 *          @OA\Property(property="password", type="string", example="password"),
	 *          @OA\Property(property="password_confirmation", type="string", example="password"),
     *     ))),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="status", type="string", example="Your password has been reset")
     *              )
     *         )
     *     ),
     * )
     *
     * @throws Exception
     */
    public function resetPassword(Request $request): JsonResponse
    {
		$request->validate([
			'token' => 'required',
			'email' => 'required|email',
			'password' => 'required|string|min:6|confirmed',
		]);
		
		$status = Password::reset(
			$request->only('email', 'password', 'password_confirmation', 'token'),
			function (User $user, string $password) {
				$user->forceFill([
					'password' => Hash::make($password)
				])->setRememberToken(Str::random(60));
				
				$user->save();
				
				event(new PasswordReset($user));
			}
		);
		
		return $status === Password::PASSWORD_RESET
			? response()->json(['status' => __($status)])
			: response()->json(['email' => __($status)]);
    }
}
