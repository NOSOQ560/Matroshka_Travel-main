<?php

use App\Mail\TestEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

//Route::get('/send-test-email', function () {
//    Mail::to('example@gmail.com')->send(new TestEmail());
//    return 'تم إرسال البريد الإلكتروني بنجاح!';
//});

Route::get('/payment-success', function () {
    return view('payment-success');
})->name('payment.success');

Route::get('/payment-failed', function () {
    return view('payment-failed');
})->name('payment.failed');



