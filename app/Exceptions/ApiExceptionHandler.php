<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ApiExceptionHandler
{
    public static function handle(Throwable $e, Request $request): ?JsonResponse
    {
        if ( ! $request->wantsJson()) {
            return null;
        }

        $isDebugMode = App::hasDebugModeEnabled();
        $debugInfo = $isDebugMode ? self::getDebugInfo($e) : null;

        return match (true) {
            $e instanceof NotFoundHttpException => response()->error(
                message: 'The requested resource was not found.',
                errors: $debugInfo,
                status: HttpResponse::HTTP_NOT_FOUND
            ),

            $e instanceof ValidationException => response()->error(
                message: 'The given data is invalid.',
                errors: array_merge($e->errors(), $debugInfo ? ['debug' => $debugInfo] : []),
                status: HttpResponse::HTTP_UNPROCESSABLE_ENTITY
            ),

            $e instanceof AuthenticationException => response()->error(
                message: 'Unauthenticated',
                errors: array_merge(['error' => 'Authentication required'], $debugInfo ? ['debug' => $debugInfo] : []),
                status: HttpResponse::HTTP_UNAUTHORIZED
            ),

            $e instanceof AuthorizationException => response()->error(
                message: 'Forbidden',
                errors: array_merge(['error' => 'You do not have permission'], $debugInfo ? ['debug' => $debugInfo] : []),
                status: HttpResponse::HTTP_FORBIDDEN
            ),

            $e instanceof MethodNotAllowedHttpException => response()->error(
                message: 'Method Not Allowed',
                errors: $debugInfo,
                status: HttpResponse::HTTP_METHOD_NOT_ALLOWED
            ),

            $e instanceof ThrottleRequestsException => response()->error(
                message: 'Too Many Requests',
                errors: array_merge(['error' => 'Rate limit exceeded'], $debugInfo ? ['debug' => $debugInfo] : []),
                status: HttpResponse::HTTP_TOO_MANY_REQUESTS
            ),

            $e instanceof HttpResponseException => response()->error(
                message: 'HTTP Exception',
                errors: array_merge(['error' => $e->getMessage()], $debugInfo ? ['debug' => $debugInfo] : []),
                status: HttpResponse::HTTP_INTERNAL_SERVER_ERROR
            ),

            default => response()->error(
                message: 'An unexpected error occurred',
                errors: array_merge(['exception' => $e->getMessage()], $debugInfo ? ['debug' => $debugInfo] : []),
                status: HttpResponse::HTTP_INTERNAL_SERVER_ERROR
            ),
        };
    }

    /**
     * Get debug information from the exception
     */
    private static function getDebugInfo(Throwable $e): array
    {
        return [
            'message' => $e->getMessage(),
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => collect($e->getTrace())
                ->map(fn ($trace) => Arr::except($trace, ['args']))
                ->all(),
        ];
    }
}
