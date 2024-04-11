<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\API\CarController;
use App\Http\Controllers\API\UserController;

use App\Http\Controllers\API\GarageController;

use App\Http\Controllers\API\VendorController;
use App\Http\Controllers\API\CarTypeController;
use App\Http\Controllers\API\ParkingController;


use App\Http\Controllers\API\CarBrandController;

use App\Http\Controllers\API\OfficeFeeController;
use App\Http\Controllers\API\SparePartController;

use App\Http\Controllers\API\UserRolesController;


use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\API\CarWashFeeController;


use App\Http\Controllers\API\ParkingFeeController;
use App\Http\Controllers\API\CarWashOrderController;
use App\Http\Controllers\API\GarageReviewController;
use App\Http\Controllers\API\SparePartTypeController;
use App\Http\Controllers\API\MotorThirdPartyController;
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



        // car type
        Route::resource('car-types', CarTypeController::class);

        // carBrand routes
        Route::apiResource('car-brands', CarBrandController::class);

        // car routes
        Route::apiResource('cars', CarController::class);

        // ======================  Spare Part Types =====================
        Route::resource('spare-part-types', SparePartTypeController::class);

        // ====================== Spare Parts ===========================
        Route::resource('spare-parts', SparePartController::class);

        // ====================== Motor Third Party =====================
        Route::resource('motor-third-parties', MotorThirdPartyController::class);

        // ====================== Motor Third Party =====================
        Route::resource('garages', GarageController::class);

        // ====================== Garage Review =========================
        Route::resource('garage-review', GarageReviewController::class);

        // ====================== Office Fees ===========================
        Route::resource('office-fees', OfficeFeeController::class);

        // ParkingFee routes
        Route::apiResource('parking-fees', ParkingFeeController::class);

        //=================== parking =========================
        Route::resource('parking', ParkingController::class);


        // ====================== Car Wash Fees ===========================
        Route::resource('car-wash-fees', CarWashFeeController::class);

        // ====================== Car Wash Orders ===========================
        Route::resource('car-wash-orders', CarWashOrderController::class);





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
