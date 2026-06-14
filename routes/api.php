<?php

use App\Http\Controllers\Admin\AdminDeviceController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\ApplicationController as AdminApplicationController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\PlanController as AdminPlanController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\PaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VpnConfigController;
use App\Http\Controllers\Admin\VpnServerController;
use App\Http\Controllers\ApplicationCatalogController;
use App\Http\Controllers\PlanController as UserPlanController;

Route::post('/signup', [AuthController::class, 'signup']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/login_by_code', [AuthController::class, 'loginByCode']);
Route::post('/get_code', [AuthController::class, 'getCode']);
Route::post('/refresh', [AuthController::class, 'refresh']);
Route::post('/logout', [AuthController::class, 'logout']);

Route::post('/password/email', [PasswordResetController::class, 'sendResetLink']);
Route::post('/password/reset', [PasswordResetController::class, 'resetPassword']);

Route::get('/email/verify/{token}', [EmailVerificationController::class, 'verify']);
Route::post('/email/send', [EmailVerificationController::class, 'sendVerificationEmail'])->middleware('jwt');

Route::get('/me', [AuthController::class, 'me'])->middleware('jwt');

Route::middleware('jwt')->group(function () {
    Route::get('/profile', function (Request $request) {
        return response()->json(['user_id' => $request->user_id]);
    });

    Route::middleware(['role:admin'])->get('/admin', function (Request $request) {
        return response()->json(['message' => 'admin area']);
    });
});
  Route::apiResource('applications', AdminApplicationController::class);
Route::get('/v2ray/config', [VpnConfigController::class, 'getConfig']);
Route::get('/applications', [ApplicationCatalogController::class, 'index']);


// public listing of servers
Route::get('/vpn/servers', [VpnConfigController::class, 'servers']);
Route::get('/vpn/config', [VpnConfigController::class, 'getConfig']);

// admin endpoints (protect later)
Route::middleware(['jwt', 'admin'])->prefix('admin')->group(function () {
    Route::apiResource('servers', controller: VpnServerController::class);
    Route::apiResource('plans', AdminPlanController::class);
    Route::apiResource('applications', AdminApplicationController::class);

    Route::get('users', [AdminUserController::class, 'index']);
    Route::post('users', [AdminUserController::class, 'store']);
    Route::get('users/{id}', [AdminUserController::class, 'show']);
    Route::put('users/{id}', [AdminUserController::class, 'update']);
    Route::delete('users/{id}', [AdminUserController::class, 'destroy']);
    Route::post('users/{id}/toggle-active', [AdminUserController::class, 'toggleActive']);
    Route::post('users/{id}/assign-plan', [AdminUserController::class, 'assignPlan']);

    Route::get('/audit-logs/all', [AuditLogController::class, 'index']);
    Route::get('/audit-logs/last', [AuditLogController::class, 'last']);
    Route::get('/audit-logs/paginate-last', [AuditLogController::class, 'paginateLast']);

    Route::get('devices', [AdminDeviceController::class, 'index']);
    Route::post('devices', [AdminDeviceController::class, 'store']);
    Route::get('devices/{id}', [AdminDeviceController::class, 'show']);
    Route::put('devices/{id}', [AdminDeviceController::class, 'update']);
    Route::delete('devices/{id}', [AdminDeviceController::class, 'destroy']);
    Route::post('devices/{id}/attach-user', [AdminDeviceController::class, 'attachUser']);
    Route::post('devices/{id}/detach-user', [AdminDeviceController::class, 'detachUser']);
    Route::post('devices/{id}/reset-traffic', [AdminDeviceController::class, 'resetTraffic']);
});

Route::get('/payment/plans', [UserPlanController::class, 'index'])->middleware('jwt');
Route::get('/plans', [UserPlanController::class, 'index'])->middleware('jwt');

Route::get('/vpn/config/full', [VpnConfigController::class, 'clientFullConfig'])->middleware('jwt');

Route::get('/user/stats', [UserController::class, 'stats'])->middleware('jwt');

Route::middleware(['jwt'])->post('/stripe/checkout', [PaymentController::class, 'checkout']);

Route::post('/device/register', [DeviceController::class, 'register']);
Route::post('/device/attach-user', [DeviceController::class, 'attachUser'])->middleware('jwt');

Route::post('/device/usage', [DeviceController::class, 'usage']);
Route::get('/user/usage-total', [UserController::class, 'usageTotal'])->middleware('jwt');
Route::get('/account/subscription-history', [UserController::class, 'subscriptionHistory'])->middleware('jwt');
Route::post('/device/access-status', [DeviceController::class, 'accessStatus']);

//Route::post('/login_by_code', [AuthController::class, 'loginByCode']);
//Route::post('/request_code', [AuthController::class, 'requestCode']);
//Route::get('/get_code', [AuthController::class, 'getCode']);
