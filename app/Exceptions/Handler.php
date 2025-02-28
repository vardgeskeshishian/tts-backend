<?php

namespace App\Exceptions;

use App\Contracts\TelegramLoggerContract;
use App\Facades\TelegramLoggerFacade;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Throwable;
use App\Factories\ExceptionFactory;
use Exception;
use Whoops\Handler\HandlerInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthenticationException::class,
        AuthorizationException::class,
        TokenMismatchException::class,
        ValidationException::class,
        ModelNotFoundException::class,
        UnauthorizedHttpException::class,
        PasswordException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param Throwable $exception
     *
     * @return void
     * @throws Throwable
     */
    public function report(Throwable $exception): void
    {
        try {
            parent::report($exception);

            if ($this->shouldntReport($exception)) {
                return;
            }

            logs('telegram-laravel')->debug($exception->getMessage(), [
                'exception' => $exception
            ]);
        } catch (Exception $e) {
            logs('telegram-laravel')->debug($e->getMessage(), [
                'exception' => $e
            ]);
        }
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param Request $request
     * @param Throwable $exception
     *
     * @return JsonResponse
     */
    public function render($request, Throwable $exception): JsonResponse
    {
        /**
         * @var $exceptionFactory ExceptionFactory
         */
        $exceptionFactory = resolve(ExceptionFactory::class);
        $exceptionFactory->formatException($exception);

        $errorBag = [
            'code' => $exceptionFactory->getStatusCode(),
            'error' => $exceptionFactory->getError(),
            'trace' => $exception->getTrace(),
        ];

        if (!in_array($errorBag['code'], [401, 404])) {
            TelegramLoggerFacade::pushToChat(TelegramLoggerContract::CHANNEL_DEBUG_ID, $request->getRequestUri(), [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'message' => $exception->getMessage(),
                'code' => $exception->getCode() > 0 ? $exception->getCode() : $errorBag['code'],
                'request' => $request->except(['password', 'password_confirmed']),
            ]);
        }

        if (config('app.env') === 'dev' && !app()->runningInConsole()) {
            $errorBag['message'] = $exception->getTrace();
        }

        return response()->json($errorBag, $errorBag['code']);
    }

    protected function whoopsHandler()
    {
        try {
            return app(HandlerInterface::class);
        } catch (BindingResolutionException $e) {
            return parent::whoopsHandler();
        }
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  Request  $request
     * @param  AuthenticationException $exception
     *
     * @return JsonResponse|RedirectResponse
     */
    protected function unauthenticated($request, AuthenticationException $exception): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest(route('login'));
    }
}
