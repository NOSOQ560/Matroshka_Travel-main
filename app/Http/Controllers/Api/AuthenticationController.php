<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ChangePasswordRequest;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Requests\Api\ResendOtpRequest;
use App\Http\Requests\Api\ResetPasswordRequest;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Http\Requests\Api\VerifyEmailRequest;
use App\Http\Resources\Api\UserResource;
use App\Models\User;
use App\Notifications\UserRegisterNotification;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

class AuthenticationController extends Controller
{
    public function register(RegisterRequest $request)
    {
        try {
            DB::beginTransaction();
            $user = User::create($request->validated());
            Notification::send($user, new UserRegisterNotification($user));
            DB::commit();

            return ResponseHelper::createdResponse(['otp' => $user->otp]);
        } catch (Exception $e) {
            DB::rollBack();

            return ResponseHelper::internalServerErrorResponse($e->getMessage());
        }
    }

    public function verifyEmail(VerifyEmailRequest $request)
    {
        try {
            $user = User::whereEmail($request->email)->first();
            if ($user->otp !== $request->otp || now()->isAfter($user->otp_till)) {
                return ResponseHelper::unauthenticatedResponse();
            }
            $user->update([
                'email_verified_at' => now(),
            ]);
            $user->resetOTP();

            return ResponseHelper::okResponse(__('verified'), [
                'user' => UserResource::make($user),
                'token_type' => 'Bearer',
                'token_value' => $user->createToken($request->userAgent(), ['all'], now()->addDays(config('travel.days_expire')))->plainTextToken,
            ], true);
        } catch (Exception $e) {
            return ResponseHelper::internalServerErrorResponse($e->getMessage());
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            if (auth()->attempt($request->validated())) {
                $user = auth()->user();
                if (! $user->hasVerifiedEmail()) {
                    return ResponseHelper::unauthorizedResponse();
                }

                return ResponseHelper::okResponse(__('verified'), [
                    'user' => UserResource::make($user),
                    'token_type' => 'Bearer',
                    'token_value' => $user->createToken($request->userAgent(), ['all'], now()->addDays(config('travel.days_expire')))->plainTextToken,
                ], true);
            }

            return ResponseHelper::unauthenticatedResponse();
        } catch (Exception $e) {
            return ResponseHelper::internalServerErrorResponse($e->getMessage());
        }
    }

    public function logout()
    {
        try {
            auth()->user()->tokens()->delete();

            return ResponseHelper::okResponse(__('logout'), [], true);
        } catch (Exception $e) {
            return ResponseHelper::internalServerErrorResponse($e->getMessage());
        }
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            $user = auth()->user();
            if (! Hash::check($request->current_password, $user->password)) {
                return ResponseHelper::unauthorizedResponse();
            }
            $user->update([
                'password' => Hash::make($request->new_password),
            ]);

            return ResponseHelper::okResponse(__('changed'), [], true);
        } catch (Exception $e) {
            return ResponseHelper::internalServerErrorResponse($e->getMessage());
        }
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        try {
            $user = auth()->user();
            $user->update($request->validated());
            if ($request->hasFile('image')) {
                $user->addMediaFromRequest('image')->toMediaCollection('profile');
            }
            $user = $user->refresh();

            return ResponseHelper::okResponse(__('updated'), UserResource::make($user), true);
        } catch (Exception $e) {
            return ResponseHelper::internalServerErrorResponse($e->getMessage());
        }
    }

    public function deleteProfile()
    {
        try {
            $user = auth()->user();
            $user->tokens()->delete();
            $user->delete();

            return ResponseHelper::okResponse(__('deleted'), [], true);
        } catch (Exception $e) {
            return ResponseHelper::internalServerErrorResponse($e->getMessage());
        }
    }

    public function resendOtp(ResendOtpRequest $request)
    {
        try {
            $user = User::whereEmail($request->email)->first();
            Notification::send($user, new UserRegisterNotification($user));

            return ResponseHelper::okResponse(__('success'), ['otp' => $user->otp], true);
        } catch (Exception $e) {
            return ResponseHelper::internalServerErrorResponse($e->getMessage());
        }
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        try {
            $user = User::whereEmail($request->email)->first();
            if ($user->otp !== $request->otp || now()->isAfter($user->otp_till)) {
                return ResponseHelper::unauthenticatedResponse();
            }
            $user->update([
                'password' => bcrypt($request->password),
            ]);
            $user->resetOTP();
            $user->tokens()->delete();

            return ResponseHelper::okResponse(__('changed'), [], true);
        } catch (Exception $e) {
            return ResponseHelper::internalServerErrorResponse($e->getMessage());
        }
    }
}
