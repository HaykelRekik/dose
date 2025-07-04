<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class ResponseMacroServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     *
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        $factory = $this->app->make(ResponseFactory::class);

        /**
         * Create a standardized success response.
         *
         * @param  string  $message  The success message
         * @param  mixed  $data  Optional data to include in the response
         * @param  int  $status  HTTP status code (defaults to 200 OK)
         * @return JsonResponse
         */
        $factory->macro(
            'success',
            function (string $message = 'Request successful.', mixed $data = null, int $status = HttpResponse::HTTP_OK): JsonResponse {
                $response = [
                    'success' => true,
                    'message' => $message,
                ];

                // Only include data if not null
                if (null !== $data) {
                    $response['data'] = $data;
                }

                return response()->json($response, $status);
            }
        );

        /**
         * Create a standardized error response.
         *
         * @param  string  $message  The error message
         * @param  mixed  $errors  Optional error details to include in the response
         * @param  int  $status  HTTP status code (defaults to 400 Bad Request)
         * @return JsonResponse
         */
        $factory->macro(
            'error',
            function (string $message = 'Request failed.', mixed $errors = null, int $status = HttpResponse::HTTP_BAD_REQUEST): JsonResponse {
                $response = [
                    'success' => false,
                    'message' => $message,
                ];

                if (null !== $errors) {
                    $response['errors'] = $errors;
                }

                return response()->json($response, $status);
            }
        );
    }
}
