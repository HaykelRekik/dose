<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\Auth\Register\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Auth\OTPService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
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
                code: 201
            );

        } catch (Exception $e) {
            $exceptionMessage = $e->getMessage();

            Log::error('User registration error: ' . $exceptionMessage);

            return response()->error(
                message : $exceptionMessage
            );
        }

    }
}
