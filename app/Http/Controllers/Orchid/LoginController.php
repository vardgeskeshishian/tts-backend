<?php

namespace App\Http\Controllers\Orchid;

use App\Models\User;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Cookie\CookieJar;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Orchid\Access\Impersonation;

class LoginController extends OrchidController
{
    /**
     * @var StatefulGuard|Guard
     */
    protected StatefulGuard|Guard $guard;

    /**
     * @param Auth $auth
     */
    public function __construct(Auth $auth)
    {
        $this->guard = $auth->guard(config('platform.guard'));

        $this->middleware('guest', [
            'except' => [
                'logout',
                'switchLogout',
            ],
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     * @throws ValidationException
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|string',
            'password' => 'required|string',
        ]);

        $auth = $this->guard->attempt(
            $request->only(['email', 'password']),
            $request->filled('remember')
        );

        $user = User::where('email', $request->email)->first();
        if (!$user->roles()->where('name', 'admin')->exists())
        {
            $this->guard->logout();

            return redirect()->back()->withErrors(['msg' => __('This user is not an administrator.')]);
        }

        if ($auth) {
            return $this->sendLoginResponse($request);
        }

        return redirect()->back()->withErrors(['msg' => __('The details you entered did not match our records. Please double-check and try again.')]);
    }

    /**
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    protected function sendLoginResponse(Request $request): JsonResponse|RedirectResponse
    {
        $request->session()->regenerate();

        return $request->wantsJson()
            ? new JsonResponse([], 204)
            : redirect()->intended(route(config('platform.index')));
    }

    /**
     * @return Factory|View
     */
    public function showLoginForm(): View|Factory
    {
        return view('platform::auth.login', [
            'isLockUser' => false,
            'lockUser'   => null,
        ]);
    }

    /**
     * @param CookieJar $cookieJar
     * @return RedirectResponse
     */
    public function resetCookieLockMe(CookieJar $cookieJar): RedirectResponse
    {
        $lockUser = $cookieJar->forget('lockUser');

        return redirect()->route('platform.login')->withCookie($lockUser);
    }

    /**
     * @return RedirectResponse
     */
    public function switchLogout(): RedirectResponse
    {
        Impersonation::logout();

        return redirect()->route(config('platform.index'));
    }

    /**
     * Log the user out of the application.
     *
     *
     * @param Request $request
     * @return RedirectResponse|JsonResponse
     */
    public function logout(Request $request): JsonResponse|RedirectResponse
    {
        $this->guard->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return $request->wantsJson()
            ? new JsonResponse([], 204)
            : redirect('/admin/login');
    }
}