<?php

use App\Http\Controllers\API\CarBrandController;
use App\Http\Controllers\API\CarCartController;
use App\Http\Controllers\API\CarController;
use App\Http\Controllers\API\CarInspectionFieldCategoryController;
use App\Http\Controllers\API\CarInspectionReportController;
use App\Http\Controllers\API\CarInspectorController;
use App\Http\Controllers\API\CarTypeController;
use App\Http\Controllers\API\CarWashFeeController;
use App\Http\Controllers\API\CarWashOrderController;
use App\Http\Controllers\API\DashboardSliderPhotoController;
use App\Http\Controllers\API\GarageController;
use App\Http\Controllers\API\GarageReviewController;
use App\Http\Controllers\API\InspectionFieldController;
use App\Http\Controllers\API\MotorThirdPartyController;
use App\Http\Controllers\API\OfficeController;
use App\Http\Controllers\API\OfficeRentController;
use App\Http\Controllers\API\ParkingController;
use App\Http\Controllers\API\ParkingFeeController;
use App\Http\Controllers\API\SparePartCartController;
use App\Http\Controllers\API\SparePartController;
use App\Http\Controllers\API\SparePartTypeController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\UserPermissionsController;
use App\Http\Controllers\API\UserRolesController;
use App\Http\Controllers\API\VendorController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::post('/register', [AuthController::class, 'register']);
// Route::post('/login', [AuthController::class, 'login']);

//check if user is still logged in
// Route::get('/user', [AuthController::class, 'checkLoginStatus']);
Route::middleware('auth:sanctum')->get('/user', [AuthController::class, 'checkLoginStatus']);

Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/third-party-login-auth', [AuthController::class, 'thirdPartyLoginAuthentication'])->name('thirdPartyLoginAuthentication');
Route::post('/third-party-register-auth', [AuthController::class, 'thirdPartyRegisterAuthentication'])->name('thirdPartyRegisterAuthentication');

Route::post('forgot-password', [PasswordResetController::class, 'forgetPassword']);
Route::get('/reset-password', [PasswordResetController::class, 'handleresetPasswordLoad']);
Route::post('/reset-password', [PasswordResetController::class, 'handlestoringNewPassword']);

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

//========================= puclic routes ===================
// Public routes for viewing dashboard slider photos
Route::resource('dashboard-slider-photos', DashboardSliderPhotoController::class)->only(['index', 'show']);
// car type
Route::resource('car-types', CarTypeController::class)->only(['index', 'show']);

// car routes
Route::apiResource('cars', CarController::class)->only(['index', 'show']);
Route::get('/car/{slug}', [CarController::class, 'getBySlug']);

// ======================  Spare Part Types =====================
Route::resource('spare-part-types', SparePartTypeController::class)->only(['index', 'show']);
Route::resource('spare-parts', SparePartController::class)->only(['index', 'show']);
Route::get('/spare-part/{slug}', [SparePartController::class, 'getBySlug']);

Route::resource('garages', GarageController::class)->only(['index', 'show']);
Route::resource('motor-third-parties', MotorThirdPartyController::class)->only(['index', 'show']);

Route::apiResource('vendors', VendorController::class)->only(['index', 'show']);

//=============================== private routes ==================================
Route::group(
    ['middleware' => ['auth:sanctum']],
    function () {
        // Vendor routes
        Route::apiResource('vendors', VendorController::class)->except(['index', 'show']);

        // car type
        Route::resource('car-types', CarTypeController::class)->except(['index', 'show']);

        // carBrand routes
        Route::apiResource('car-brands', CarBrandController::class);

        // car routes
        Route::apiResource('cars', CarController::class)->except(['index', 'show']);

        // ======================  Spare Part Types =====================
        Route::resource('spare-part-types', SparePartTypeController::class)->except(['index', 'show']);

        // ====================== Spare Parts ===========================
        Route::resource('spare-parts', SparePartController::class)->except(['index', 'show']);

        // ====================== Motor Third Party =====================
        Route::resource('motor-third-parties', MotorThirdPartyController::class)->except(['index', 'show']);

        // ====================== Motor Third Party =====================
        Route::resource('garages', GarageController::class)->except(['index', 'show']);

        // ====================== Garage Review =========================
        Route::resource('garage-review', GarageReviewController::class);

        // ====================== Office Fees ===========================
        Route::resource('office-fees', OfficeController::class);
        Route::resource('office-rents', OfficeRentController::class);

        //================ Dashboard Slider ======================
        Route::resource('dashboard-slider-photos', DashboardSliderPhotoController::class)
            ->except(['index', 'show']);

        //======================= Shopping Carts ========================
        Route::apiResource('car-carts', CarCartController::class);
        Route::post('sync-car-carts', [CarCartController::class, 'syncCarCarts']);

        Route::apiResource('spare-part-carts', SparePartCartController::class);
        Route::post('sync-spare-carts', [SparePartCartController::class, 'syncSparePartCarts']);

        // ParkingFee routes
        Route::apiResource('parking-fees', ParkingFeeController::class);

        //=================== parking =========================
        Route::resource('parking', ParkingController::class);

        // ====================== Car Wash Fees ===========================
        Route::resource('car-wash-fees', CarWashFeeController::class);

        // ====================== Car Wash Orders ===========================
        Route::resource('car-wash-orders', CarWashOrderController::class);

        //=========================== Car Inspection =======================
        Route::resource('inspection-field-categories', CarInspectionFieldCategoryController::class);
        Route::apiResource('inspection-fields', InspectionFieldController::class);

        Route::apiResource('car-inspection-reports', CarInspectionReportController::class);

        Route::apiResource('car-inspectors', CarInspectorController::class);

        Route::get('categorized-inspection-fields', [CarInspectionFieldCategoryController::class, 'getCategoryWithFields']);

        Route::post('update-car-report-status', [CarInspectionReportController::class, 'updateCarInspectionReportStatus']);

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
