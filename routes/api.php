<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;



// Public Routes
Route::post('/register', [AuthController::class, 'register']);
// Route::post('/login', [AuthController::class, 'login']);



//check if user is still logged in
// Route::get('/user', [AuthController::class, 'checkLoginStatus']);
Route::middleware('auth:sanctum')->get('/user', [AuthController::class, 'checkLoginStatus']);


Route::post('/login', [AuthController::class, 'login'])->name('login');


Route::post('forgot-password', [PasswordResetController::class, 'forgetPassword']);
Route::get('/reset-password', [PasswordResetController::class, 'handleresetPasswordLoad']);
Route::post('/reset-password', [PasswordResetController::class, 'handlestoringNewPassword']);


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');