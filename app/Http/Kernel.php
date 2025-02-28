<?php

namespace App\Http;

use App\Http\Middleware\IsAuth;
use App\Http\Middleware\IsAuthor;
use Illuminate\Http\Middleware\HandleCors;
use App\Http\Middleware\TrimStrings;
use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\AllowedInAdmin;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Auth\Middleware\Authorize;
use App\Http\Middleware\DashboardUserVerified;
use Illuminate\Session\Middleware\StartSession;
use Tymon\JWTAuth\Http\Middleware\Authenticate;
use Tymon\JWTAuth\Http\Middleware\RefreshToken;
use App\Http\Middleware\DashboardUserAuthenticate;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Spatie\ResponseCache\Middlewares\CacheResponse;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Http\Middleware\SetCacheHeaders;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        ValidatePostSize::class,
        TrimStrings::class,
        ConvertEmptyStringsToNull::class,
		HandleCors::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
        ],

        'api' => [
            StartSession::class,
            'throttle:api',
            'bindings',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => DashboardUserAuthenticate::class,
        'auth.basic' => AuthenticateWithBasicAuth::class,
        'cache.headers' => SetCacheHeaders::class,
        'bindings' => SubstituteBindings::class,
        'is_auth' => IsAuth::class,
        'guest' => Middleware\RedirectIfAuthenticated::class,
        'throttle' => ThrottleRequests::class,
        'is_admin' => AllowedInAdmin::class,
        'jwt.auth' => Authenticate::class,
        'jwt.refresh' => RefreshToken::class,
        'response.cache' => CacheResponse::class,
        'verified' => DashboardUserVerified::class,
        'page-cache' => \Silber\PageCache\Middleware\CacheResponse::class,
        'is_author' => IsAuthor::class,
		'cors' => HandleCors::class,
    ];
}
