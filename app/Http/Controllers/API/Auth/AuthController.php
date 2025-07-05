<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\Auth\Profile\UpdateProfileRequest;
use App\Http\Requests\API\Auth\Register\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Auth\OTPService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class AuthController extends Controller
{
    public function __construct(protected OTPService $otpService) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $payload['role'] = UserRole::CUSTOMER->value;

        try {
            $user = User::create($payload);
            $this->otpService->sendOtpFor(user: $user);

            return response()->success(
                message: __('Account created successfully. Please login using the otp sent to you.'),
                data: new UserResource($user),
                status: HttpResponse::HTTP_CREATED
            );

        } catch (Exception $e) {
            $exceptionMessage = $e->getMessage();

            Log::error('User registration error: ' . $exceptionMessage);

            return response()->error(
                message : $exceptionMessage
            );
        }

    }

    public function me(): JsonResponse
    {
        return response()->success(
            message: __('Authenticated User Information'),
            data: new UserResource(request()->user())
        );
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $request->user()->update($request->validated());

        return response()->success(
            message: __('User profile updated successfully'),
            data: new UserResource(request()->user())
        );
    }
}
