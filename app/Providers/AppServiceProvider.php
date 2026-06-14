<?php

namespace App\Providers;

use App\Models\Application;
use App\Models\AuditLog;
use App\Models\Device;
use App\Models\Plan;
use App\Models\User;
use App\Models\UserSubscription;
use App\Models\VpnServer;
use App\Observers\UserObserver;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        User::observe(UserObserver::class);

        View::composer('layouts.admin-side-menu', function ($view) {
            $view->with('adminSideLinks', [
                [
                    'label' => 'Dashboard',
                    'route' => 'dashboard',
                    'active' => 'dashboard',
                    'count' => null,
                    'color' => '#0f172a',
                ],
                [
                    'label' => 'Home Page',
                    'route' => 'admin.home-page.edit',
                    'active' => 'admin.home-page.*',
                    'count' => null,
                    'color' => '#059669',
                ],
                [
                    'label' => 'Users',
                    'route' => 'admin.users.index',
                    'active' => 'admin.users.*',
                    'count' => User::count(),
                    'color' => '#2563eb',
                ],
                [
                    'label' => 'Admins',
                    'route' => 'admin.users.index',
                    'active' => 'admin.users.*',
                    'count' => User::where('is_admin', true)->count(),
                    'color' => '#7c3aed',
                ],
                [
                    'label' => 'Plans',
                    'route' => 'admin.plans.index',
                    'active' => 'admin.plans.*',
                    'count' => Plan::count(),
                    'color' => '#0891b2',
                ],
                [
                    'label' => 'Servers',
                    'route' => 'admin.servers.index',
                    'active' => 'admin.servers.*',
                    'count' => VpnServer::count(),
                    'color' => '#16a34a',
                ],
                [
                    'label' => 'Devices',
                    'route' => 'admin.devices.index',
                    'active' => 'admin.devices.*',
                    'count' => Device::count(),
                    'color' => '#ea580c',
                ],
                [
                    'label' => 'Applications',
                    'route' => 'admin.applications.index',
                    'active' => 'admin.applications.*',
                    'count' => Application::count(),
                    'color' => '#db2777',
                ],
                [
                    'label' => 'Subscriptions',
                    'route' => 'admin.subscriptions.index',
                    'active' => 'admin.subscriptions.*',
                    'count' => UserSubscription::count(),
                    'color' => '#4f46e5',
                ],
                [
                    'label' => 'Audit Logs',
                    'route' => 'admin.audit-logs.index',
                    'active' => 'admin.audit-logs.*',
                    'count' => AuditLog::count(),
                    'color' => '#475569',
                ],
            ]);
        });
    }
}
