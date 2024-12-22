<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\UserResource;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function redirectToGoogle()
    {
        try {
            return Socialite::driver('google')
                ->stateless()
                ->redirect();
        } catch (Exception $e) {
            return ResponseHelper::internalServerErrorResponse($e->getMessage());
        }
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            $socialiteUser = Socialite::driver('google')->stateless()->user();
            $user = User::updateOrCreate(
                [
                    'provider' => 'google',
                    'provider_id' => $socialiteUser->getId(),
                    'email' => $socialiteUser->getEmail(),
                ],
                [
                    'name' => $socialiteUser->getName(),
                    'email_verified_at' => now(),
                ]
            );

            $user->addMediaFromUrl($socialiteUser->getAvatar())->toMediaCollection('profile');

            return ResponseHelper::createdResponse([
                'user' => UserResource::make($user),
                'token_type' => 'Bearer',
                'token_value' => $user->createToken($request->userAgent(), ['all'], now()->addDays(config('travel.days_expire')))->plainTextToken,
                'google_token' => $socialiteUser->token,
            ]);
        } catch (Exception $e) {
            return ResponseHelper::internalServerErrorResponse($e->getMessage());
        }
    }
}
