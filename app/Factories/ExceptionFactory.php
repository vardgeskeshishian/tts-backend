<?php

namespace App\Factories;

use Throwable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class ExceptionFactory
{
    protected $statusCode = 500;
    protected $error = "Something went wrong";
    protected $bag = "";

    public function formatException(Throwable $exception)
    {
        $current = $exception;
        $previous = $exception->getPrevious();

        if ($exception->getCode() > 0
            && is_int($exception->getCode())
        ) {
            $this->statusCode = $exception->getCode();
        }

        if ($this->isTokenException($current, $previous) || $this->isNotAuthorizedException($current)) {
            $this->statusCode = 401;
            $this->error = 'Unauthorized';

            return;
        }

        if ($this->isNotFoundException($current)) {
            $this->statusCode = 404;
            $this->error = $current->getMessage();

            return;
        }

        if ($this->isValidationException($current)) {
            $this->statusCode = 400;
            $this->error = $current->errors();

            return;
        }

        if ($this->isMethodNotAllowed($current)) {
            $this->statusCode = 405;
            $this->error = "Not allowed";

            return;
        }

        if ($current->getMessage()) {
            $this->error = $current->getMessage();
        }
    }

    protected function isTokenException($current, $previous)
    {
        return ($current instanceof TokenBlacklistedException
            || $previous instanceof TokenBlacklistedException
            || $current instanceof TokenExpiredException
            || $previous instanceof TokenExpiredException
        );
    }

    protected function isNotAuthorizedException($current)
    {
        return ($current instanceof UnauthorizedHttpException
            || ($current instanceof HttpException && $current->getCode() === 401));
    }

    protected function isNotFoundException($current)
    {
        return ($current instanceof ModelNotFoundException
            || $current instanceof NotFoundHttpException);
    }

    protected function isValidationException($current)
    {
        return $current instanceof ValidationException;
    }

    protected function isMethodNotAllowed($current)
    {
        return ($current instanceof MethodNotAllowedException
            || $current instanceof MethodNotAllowedHttpException);
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getError()
    {
        return $this->error;
    }
}
