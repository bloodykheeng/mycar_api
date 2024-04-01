<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\CountyController;
use App\Http\Controllers\API\ParishController;
use App\Http\Controllers\API\VendorController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\ServiceController;
use App\Http\Controllers\API\VillageController;
use App\Http\Controllers\API\DistrictController;
use App\Http\Controllers\API\SubCountyController;
use App\Http\Controllers\API\UserRolesController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\API\ServiceTypeController;
use App\Http\Controllers\API\ProductBrandController;
use App\Http\Controllers\API\UserPermissionsController;



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


// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');




// private routes
Route::group(
    ['middleware' => ['auth:sanctum']],
    function () {
        // Vendor routes
        Route::apiResource('vendors', VendorController::class);

        // service types
        Route::apiResource('service-types', ServiceTypeController::class);


        // Standard RESTful resource routes for services
        Route::apiResource('services', ServiceController::class);

        // Additional routes for active and inactive services
        Route::get('services/active', [ServiceController::class, 'active'])->name('services.active');
        Route::get('services/inactive', [ServiceController::class, 'inactive'])->name('services.inactive');


        // ProductBrand routes
        Route::apiResource('product-brands', ProductBrandController::class);

        // Product routes
        Route::apiResource('products', ProductController::class);




        //======================= locations =============================

        Route::resource('districts', DistrictController::class);

        Route::resource('county', CountyController::class)->except(['create', 'edit']);

        Route::resource('subcounty', SubCountyController::class)->except(['create', 'edit']);

        Route::resource('parish', ParishController::class)->except(['create', 'edit']);

        Route::resource('village', VillageController::class)->except(['create', 'edit']);






        //======================== User Management =================================
        Route::Resource('users', UserController::class);

        //Roles AND Permisions
        Route::get('/roles', [UserRolesController::class, 'getAssignableRoles']);

        Route::Resource('users-roles', UserRolesController::class);
        Route::Post('users-roles-addPermissionsToRole', [UserRolesController::class, 'addPermissionsToRole']);
        Route::Post('users-roles-deletePermissionFromRole', [UserRolesController::class, 'deletePermissionFromRole']);

        Route::Resource('users-permissions', UserPermissionsController::class);
        Route::get('users-permissions-permissionNotInCurrentRole/{id}', [UserPermissionsController::class, 'permissionNotInCurrentRole']);
    }
);