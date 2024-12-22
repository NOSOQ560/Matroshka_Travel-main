<?php

use App\Http\Controllers\Api\AirportController;
use App\Http\Controllers\Api\CarController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ConfigrationController;
use App\Http\Controllers\Api\HotelController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\StoryController;
use Illuminate\Support\Facades\Route;

// strory
Route::group(['prefix' => 'v1/story', 'middleware' => ['auth:sanctum', 'admin-type']], function () {
    Route::get('', [StoryController::class, 'index']);
    Route::post('', [StoryController::class, 'store']);
    Route::get('{id}', [StoryController::class, 'show']);
    Route::put('{id}', [StoryController::class, 'update']);
    Route::delete('{id}', [StoryController::class, 'destroy']);
});

// configration
Route::group(['prefix' => 'v1/configration', 'middleware' => ['auth:sanctum', 'admin-type']], function () {
    Route::get('', [ConfigrationController::class, 'index']);
    Route::post('', [ConfigrationController::class, 'store']);
    Route::get('{id}', [ConfigrationController::class, 'show']);
    Route::put('{id}', [ConfigrationController::class, 'update']);
    Route::delete('{id}', [ConfigrationController::class, 'destroy']);
});

// category
Route::group(['prefix' => 'v1/category', 'middleware' => ['auth:sanctum', 'admin-type']], function () {
    Route::get('', [CategoryController::class, 'index']);
    Route::post('', [CategoryController::class, 'store']);
    Route::get('{id}', [CategoryController::class, 'show']);
    Route::put('{id}', [CategoryController::class, 'update']);
    Route::delete('{id}', [CategoryController::class, 'destroy']);
});

// product
Route::group(['prefix' => 'v1/product', 'middleware' => ['auth:sanctum', 'admin-type']], function () {
    Route::get('', [ProductController::class, 'index']);
    Route::post('', [ProductController::class, 'store']);
    Route::get('{id}', [ProductController::class, 'show']);
    Route::post('{id}', [ProductController::class, 'update']);
    Route::delete('{id}', [ProductController::class, 'destroy']);
});

// car
Route::group(['prefix' => 'v1/car', 'middleware' => ['auth:sanctum', 'admin-type']], function () {
    Route::get('', [CarController::class, 'index']);
    Route::post('', [CarController::class, 'store']);
    Route::get('{id}', [CarController::class, 'show']);
    Route::put('{id}', [CarController::class, 'update']);
    Route::delete('{id}', [CarController::class, 'destroy']);
});

// airport
Route::group(['prefix' => 'v1/airport', 'middleware' => ['auth:sanctum', 'admin-type']], function () {
    Route::get('', [AirportController::class, 'index']);
    Route::post('', [AirportController::class, 'store']);
    Route::get('{id}', [AirportController::class, 'show']);
    Route::put('{id}', [AirportController::class, 'update']);
    Route::delete('{id}', [AirportController::class, 'destroy']);
});

// hotel
Route::group(['prefix' => 'v1/hotel', 'middleware' => ['auth:sanctum', 'admin-type']], function () {
    Route::get('', [HotelController::class, 'index']);
    Route::post('', [HotelController::class, 'store']);
    Route::get('{id}', [HotelController::class, 'show']);
    Route::put('{id}', [HotelController::class, 'update']);
    Route::delete('{id}', [HotelController::class, 'destroy']);
});
