<?php

use App\Http\Controllers\Api\ChatController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\StoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\SocialiteController;
use App\Http\Controllers\Api\ConfigrationController;
use App\Http\Controllers\Api\AuthenticationController;
use App\Http\Controllers\Api\CarReservationController;

//################################### Authentication ####################################
Route::prefix('v1/auth')->group(function () {
    Route::controller(AuthenticationController::class)
        ->group(function () {
            Route::post('/register', 'register');
            Route::post('/resend-otp', 'resendOtp');
            Route::post('/verify-email', 'verifyEmail');
            Route::post('/login', 'login');
            Route::post('/forget-password', 'resendOtp');
            Route::put('/reset-password', 'resetPassword');
        });
    Route::middleware(['auth:sanctum'])
        ->controller(AuthenticationController::class)
        ->group(function () {
            Route::put('/change-password', 'changePassword');
            Route::put('/update-profile', 'updateProfile');
            Route::delete('/delete-profile', 'deleteProfile');
            Route::get('/logout', 'logout');
        });
});
Route::controller(SocialiteController::class)
    ->group(function () {
        Route::get('v1/auth/google', 'redirectToGoogle');
        Route::get('v1/auth/google/callback', 'handleGoogleCallback');
    });
//################################### Authentication ####################################

// story
Route::group(['prefix' => 'v1/story'], function () {
    Route::get('', [StoryController::class, 'index']);
    Route::get('{id}', [StoryController::class, 'show']);
});
// configration
Route::group(['prefix' => 'v1/configration'], function () {
    Route::get('', [ConfigrationController::class, 'index']);
});
// category
Route::group(['prefix' => 'v1/category'], function () {
    Route::get('', [CategoryController::class, 'index']);
});
// products
Route::group(['prefix' => 'v1/product'], function () {
    Route::get('/{categoryId}', [ProductController::class, 'index']);
    Route::get('{id}', [ProductController::class, 'show']);
});

// cart
Route::group(['prefix' => 'v1/cart', 'middleware' => ['auth:sanctum']], function () {
    Route::get('', [CartController::class, 'index']);
    Route::post('', [CartController::class, 'store']);
    Route::patch('{id}', [CartController::class, 'update']);
    Route::delete('{id}', [CartController::class, 'destroy']);
});

// car
Route::post('available-cars', [CarReservationController::class, 'getAvailableCars']);

//////////////////  chat  ///////////
Route::group(['prefix'=>'v1/chat','middleware' => ['auth:sanctum']],function (){
    Route::post('send-message', [ChatController::class, 'sendMessage']);
    Route::post('send-image', [ChatController::class, 'sendImage']);
    Route::post('conversations/auto', [ChatController::class, 'createOrGetConversationAuto']);
    Route::get('conversations', [ChatController::class, 'getUserConversations']);
    Route::get('messages/{conversation_id}', [ChatController::class, 'getMessages']);
    Route::post('getMessages', [ChatController::class, 'createOrGetConversationWithMessages']);
    Route::get('unreadMessages/{conversation_id}', [ChatController::class, 'getUnreadMessagesCount']);
    Route::get('getChats', [ChatController::class, 'getChats']);
});
