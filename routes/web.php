<?php

use App\Http\Controllers\Admin\Web\AuditLogPageController;
use App\Http\Controllers\Admin\Web\ApplicationPageController;
use App\Http\Controllers\Admin\Web\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\Web\DevicePageController;
use App\Http\Controllers\Admin\Web\HomePageController;
use App\Http\Controllers\Admin\Web\PlanPageController;
use App\Http\Controllers\Admin\Web\ServerPageController;
use App\Http\Controllers\Admin\Web\SubscriptionPageController;
use App\Http\Controllers\Admin\Web\UserPageController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/dashboard', [AdminDashboardController::class, 'index'])
    ->middleware(['auth:web', 'web.admin'])
    ->name('dashboard');

Route::middleware('auth:web')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth:web', 'web.admin'])
    ->prefix('admin-panel')
    ->name('admin.')
    ->group(function () {
        Route::get('/', function () {
            return redirect()->route('dashboard');
        })->name('dashboard');

        Route::get('/home-page', [HomePageController::class, 'edit'])->name('home-page.edit');
        Route::put('/home-page/content', [HomePageController::class, 'updateContent'])->name('home-page.content.update');
        Route::post('/home-page/versions', [HomePageController::class, 'storeVersion'])->name('home-page.versions.store');
        Route::put('/home-page/versions/{version}', [HomePageController::class, 'updateVersion'])->name('home-page.versions.update');
        Route::delete('/home-page/versions/{version}', [HomePageController::class, 'destroyVersion'])->name('home-page.versions.destroy');
        Route::post('/home-page/versions/{version}/toggle-visibility', [HomePageController::class, 'toggleVersionVisibility'])->name('home-page.versions.toggle-visibility');

        Route::get('/users', [UserPageController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserPageController::class, 'create'])->name('users.create');
        Route::post('/users', [UserPageController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserPageController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserPageController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserPageController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/{user}/toggle-active', [UserPageController::class, 'toggleActive'])->name('users.toggle-active');
        Route::post('/users/{user}/assign-plan', [UserPageController::class, 'assignPlan'])->name('users.assign-plan');

        Route::get('/plans', [PlanPageController::class, 'index'])->name('plans.index');
        Route::get('/plans/create', [PlanPageController::class, 'create'])->name('plans.create');
        Route::post('/plans', [PlanPageController::class, 'store'])->name('plans.store');
        Route::get('/plans/{plan}/edit', [PlanPageController::class, 'edit'])->name('plans.edit');
        Route::put('/plans/{plan}', [PlanPageController::class, 'update'])->name('plans.update');
        Route::delete('/plans/{plan}', [PlanPageController::class, 'destroy'])->name('plans.destroy');

        Route::get('/servers', [ServerPageController::class, 'index'])->name('servers.index');
        Route::get('/servers/create', [ServerPageController::class, 'create'])->name('servers.create');
        Route::post('/servers', [ServerPageController::class, 'store'])->name('servers.store');
        Route::get('/servers/{server}/edit', [ServerPageController::class, 'edit'])->name('servers.edit');
        Route::put('/servers/{server}', [ServerPageController::class, 'update'])->name('servers.update');
        Route::delete('/servers/{server}', [ServerPageController::class, 'destroy'])->name('servers.destroy');

        Route::get('/devices', [DevicePageController::class, 'index'])->name('devices.index');
        Route::get('/devices/create', [DevicePageController::class, 'create'])->name('devices.create');
        Route::post('/devices', [DevicePageController::class, 'store'])->name('devices.store');
        Route::get('/devices/{device}/edit', [DevicePageController::class, 'edit'])->name('devices.edit');
        Route::put('/devices/{device}', [DevicePageController::class, 'update'])->name('devices.update');
        Route::delete('/devices/{device}', [DevicePageController::class, 'destroy'])->name('devices.destroy');
        Route::post('/devices/{device}/attach-user', [DevicePageController::class, 'attachUser'])->name('devices.attach-user');
        Route::post('/devices/{device}/detach-user', [DevicePageController::class, 'detachUser'])->name('devices.detach-user');
        Route::post('/devices/{device}/reset-traffic', [DevicePageController::class, 'resetTraffic'])->name('devices.reset-traffic');

        Route::get('/applications', [ApplicationPageController::class, 'index'])->name('applications.index');
        Route::get('/applications/create', [ApplicationPageController::class, 'create'])->name('applications.create');
        Route::post('/applications', [ApplicationPageController::class, 'store'])->name('applications.store');
        Route::get('/applications/{application}/edit', [ApplicationPageController::class, 'edit'])->name('applications.edit');
        Route::put('/applications/{application}', [ApplicationPageController::class, 'update'])->name('applications.update');
        Route::delete('/applications/{application}', [ApplicationPageController::class, 'destroy'])->name('applications.destroy');
        Route::post('/applications/{application}/toggle-status', [ApplicationPageController::class, 'toggleStatus'])->name('applications.toggle-status');

        Route::get('/subscriptions', [SubscriptionPageController::class, 'index'])->name('subscriptions.index');
        Route::get('/subscriptions/create', [SubscriptionPageController::class, 'create'])->name('subscriptions.create');
        Route::post('/subscriptions', [SubscriptionPageController::class, 'store'])->name('subscriptions.store');

        Route::get('/audit-logs', [AuditLogPageController::class, 'index'])->name('audit-logs.index');
        Route::delete('/audit-logs/clear', [AuditLogPageController::class, 'clear'])->name('audit-logs.clear');
    });

require __DIR__ . '/auth.php';
