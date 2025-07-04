<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Auth\OTP\RequestOTPRequest;
use App\Http\Requests\API\Auth\OTP\VerifytOTPRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Auth\OTPService;
use Illuminate\Http\JsonResponse;

class OTPController extends Controller
{
    public function __construct(protected OTPService $otpService) {}

    public function requestOtp(RequestOTPRequest $request): JsonResponse
    {
        $user = User::query()
            ->where('phone', $request->phone_number)
            ->first();

        if ( ! $this->otpService->sendOtpFor(user: $user)) {
            return response()->error(
                message : __('Failed to send OTP. Please try again later.')
            );
        }

        return response()->success(
            message : __('OTP has been sent. This code is valid for :minutes minutes.', ['minutes' => config('auth.otp_expires_in_minutes')]),
            data: app()->isLocal() ?
                [
                    'phone_number' => $user->phone,
                    'otp' => $user->otp_secret,
                    'otp_expires_at' => $user->otp_expires_at->timezone('Africa/Tunis')->toDateTimeString(),
                ] : null
        );
    }

    public function verifyOtp(VerifytOTPRequest $request): JsonResponse
    {
        $user = $this->otpService->verifyToken(
            phone_number: $request->phone_number,
            otp: $request->otp
        );

        if ( ! $user) {
            return $this->invalidOtpResponse();
        }

        return $this->loggedInResponse($user);
    }

    private function invalidOtpResponse(): JsonResponse
    {
        return response()->error(
            message: __('Invalid OTP. Please try again.'),
        );
    }

    private function loggedInResponse(User $user): JsonResponse
    {
        $this->otpService->deleteOTPFor(user: $user);

        $user->token_type = 'Bearer';
        $user->token = $user->createToken('authToken')->plainTextToken;

        return response()->success(
            message: __('Logged in successfully'),
            data: UserResource::make($user)
        );
    }
}
