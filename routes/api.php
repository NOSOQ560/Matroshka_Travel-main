<?php

use App\Http\Controllers\Api\AirportController;
use App\Http\Controllers\Api\CarController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\HotelController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\StripeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\StoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\SocialiteController;
use App\Http\Controllers\Api\ConfigrationController;
use App\Http\Controllers\Api\AuthenticationController;
use App\Http\Controllers\Api\CarReservationController;
use Illuminate\Http\Request;


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
            Route::get('showUser', [AuthenticationController::class, 'showUser']);


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
    Route::get('singleProduct/{id}', [ProductController::class, 'show']);
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



///////////////   Payment Gateways  ///////////////
Route::group(['prefix'=>'v1/payment'],function (){

    /////////////////////  stripe  ///////////////
    Route::post('stripe/sendPayment', [StripeController::class, 'sendPayment']);
    Route::match(['GET','POST'],'stripe/callback', [StripeController::class, 'callBack']);

    Route::get('/all', [StripeController::class, 'getAllPaymentsWithCashbacks']);
    Route::get('/cashbacks', [StripeController::class, 'getAllCashbacks']);

    Route::group(['middleware' => ['auth:sanctum']],function (){
        Route::get('/user/payments', [StripeController::class, 'getUserPaymentsWithCashbacks']);
        Route::get('/user/cashback', [StripeController::class, 'getUserCashback']);

    });

    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        return $request->user();
    });

});


//////////////////////// notification ////////////

Route::prefix('v1/notifications')->group(function () {
    // Route لعرض الإشعارات
    Route::get('/', [NotificationController::class, 'index']);

    // Route لتحديد الإشعارات كمقروءة
    Route::post('/mark-as-read', [NotificationController::class, 'markAsRead']);
});

////////////////////// locations ///////////

Route::prefix('v1/location')->group(function (){
   Route::get('/',[LocationController::class,'getLocations']);
});



/////////////  Admin ///////////////

Route::group(['prefix'=>'admin'],function (){
// strory
    Route::group(['prefix' => 'v1/story', 'middleware' => ['auth:sanctum']], function () {
        Route::get('', [StoryController::class, 'index']);
        Route::post('', [StoryController::class, 'store']);
        Route::get('{id}', [StoryController::class, 'show']);
        Route::put('{id}', [StoryController::class, 'update']);
        Route::delete('{id}', [StoryController::class, 'destroy']);
    });

// configration
    Route::group(['prefix' => 'v1/configration', 'middleware' => ['auth:sanctum']], function () {
        Route::get('', [ConfigrationController::class, 'index']);
        Route::post('', [ConfigrationController::class, 'store']);
        Route::get('{id}', [ConfigrationController::class, 'show']);
        Route::put('{id}', [ConfigrationController::class, 'update']);
        Route::delete('{id}', [ConfigrationController::class, 'destroy']);
    });

// category
    Route::group(['prefix' => 'v1/category', 'middleware' => ['auth:sanctum']], function () {
        Route::get('', [CategoryController::class, 'index']);
        Route::post('', [CategoryController::class, 'store']);
        Route::get('{id}', [CategoryController::class, 'show']);
        Route::put('{id}', [CategoryController::class, 'update']);
        Route::delete('{id}', [CategoryController::class, 'destroy']);
    });

// product
    Route::group(['prefix' => 'v1/product', 'middleware' => ['auth:sanctum']], function () {
        Route::get('/{categoryId}', [ProductController::class, 'index']);
        Route::get('singleProduct/{id}', [ProductController::class, 'show']);
        Route::post('', [ProductController::class, 'store']);
//        Route::get('{id}', [ProductController::class, 'show']);
        Route::post('{id}', [ProductController::class, 'update']);
        Route::delete('{id}', [ProductController::class, 'destroy']);
    });

// car
    Route::group(['prefix' => 'v1/car', 'middleware' => ['auth:sanctum']], function () {
        Route::get('', [CarController::class, 'index']);
        Route::post('', [CarController::class, 'store']);
        Route::get('{id}', [CarController::class, 'show']);
        Route::put('{id}', [CarController::class, 'update']);
        Route::delete('{id}', [CarController::class, 'destroy']);
    });

// airport
    Route::group(['prefix' => 'v1/airport', 'middleware' => ['auth:sanctum']], function () {
        Route::get('', [AirportController::class, 'index']);
        Route::post('', [AirportController::class, 'store']);
        Route::get('{id}', [AirportController::class, 'show']);
        Route::put('{id}', [AirportController::class, 'update']);
        Route::delete('{id}', [AirportController::class, 'destroy']);
    });

// hotel
    Route::group(['prefix' => 'v1/hotel', 'middleware' => ['auth:sanctum']], function () {
        Route::get('', [HotelController::class, 'index']);
        Route::post('', [HotelController::class, 'store']);
        Route::get('{id}', [HotelController::class, 'show']);
        Route::put('{id}', [HotelController::class, 'update']);
        Route::delete('{id}', [HotelController::class, 'destroy']);
    });


///////////  auth ///
    Route::group(['prefix' => 'v1/auth',], function () {
        Route::get('showAll', [AuthenticationController::class, 'showAll']);

    });


});
