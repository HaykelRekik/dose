<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\User;
use App\Notifications\SendOTPNotification;

class OTPService
{
    /**
     * Generates a token for the given user.
     *
     * @param  User  $user  The user for whom the token is generated.
     */
    public function generateTokenFor(User $user): void
    {
        $this->deleteOTPFor($user);

        $user->timestamps = false;
        $user->otp_secret = random_int(100000, 999999);
        $user->otp_expires_at = now()->addMinutes(config('auth.otp_expires_in_minutes'));

        $user->save();
    }

    /**
     * Verify the token for a given phone number and OTP.
     *
     * @param  string  $phone_number  The phone number to verify.
     * @param  string  $otp  The OTP to verify.
     * @return bool|User Returns the User object if the token is verified, otherwise returns false.
     */
    public function verifyToken(string $phone_number, string $otp): ?User
    {
        return User::query()
            ->where('phone', $phone_number)
            ->where('otp_secret', $otp)
            ->where('otp_expires_at', '>', now())
            ->first();
    }

    /**
     * Delete the OTP (One-Time Password) for a given user.
     *
     * @param  User  $user  The user for whom to delete the OTP.
     */
    public function deleteOTPFor(User $user): void
    {
        if ($user->otp_secret) {
            $user->timestamps = false;
            $user->otp_secret = null;
            $user->otp_expires_at = null;
            $user->save();
        }

    }

    /**
     * Sends an OTP (One-Time Password) to the user's phone number.
     *
     * @param  User  $user  The user object.
     */
    public function sendOtpFor(User $user): bool
    {
        $this->generateTokenFor(user : $user);

        $message = __('Your verification code is :otp. This code will expire in :minutes minutes.', ['otp' => $user->otp_secret, 'minutes' => config('auth.otp_expires_in_minutes')]);
        $user->notify(new SendOTPNotification($message));

        //        Mail::to($user->email)->send(
        //            mailable: new OTPCodeEmail(
        //                OTPCode: (string) $user->otp_secret,
        //            )
        //        );

        return true;
    }
}
