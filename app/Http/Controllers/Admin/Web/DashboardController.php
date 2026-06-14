<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\AuditLog;
use App\Models\Device;
use App\Models\Plan;
use App\Models\User;
use App\Models\UserSubscription;
use App\Models\VpnServer;
use App\Services\AuditService;

class DashboardController extends Controller
{
    public function index()
    {
        AuditService::log('Dashboard.index', 'Dashboard', [
            'by' => 'admin',
        ]);

        $usersCount = User::count();
        $adminsCount = User::where('is_admin', true)->count();
        $plansCount = Plan::count();
        $serversCount = VpnServer::count();
        $devicesCount = Device::count();
        $applicationsCount = Application::count();
        $subscriptionsCount = UserSubscription::count();
        $auditLogsCount = AuditLog::count();

        $dashboardStats = [
            ['label' => 'Users', 'count' => $usersCount, 'route' => 'admin.users.index', 'color' => '#2563eb'],
            ['label' => 'Admins', 'count' => $adminsCount, 'route' => 'admin.users.index', 'color' => '#7c3aed'],
            ['label' => 'Plans', 'count' => $plansCount, 'route' => 'admin.plans.index', 'color' => '#0891b2'],
            ['label' => 'Servers', 'count' => $serversCount, 'route' => 'admin.servers.index', 'color' => '#16a34a'],
            ['label' => 'Devices', 'count' => $devicesCount, 'route' => 'admin.devices.index', 'color' => '#ea580c'],
            ['label' => 'Applications', 'count' => $applicationsCount, 'route' => 'admin.applications.index', 'color' => '#db2777'],
            ['label' => 'Subscriptions', 'count' => $subscriptionsCount, 'route' => 'admin.subscriptions.index', 'color' => '#4f46e5'],
            ['label' => 'Audit Logs', 'count' => $auditLogsCount, 'route' => 'admin.audit-logs.index', 'color' => '#475569'],
        ];

        $maxStatCount = max(1, ...array_column($dashboardStats, 'count'));

        return view('dashboard', [
            'usersCount' => $usersCount,
            'adminsCount' => $adminsCount,
            'plansCount' => $plansCount,
            'serversCount' => $serversCount,
            'devicesCount' => $devicesCount,
            'applicationsCount' => $applicationsCount,
            'subscriptionsCount' => $subscriptionsCount,
            'auditLogsCount' => $auditLogsCount,
            'dashboardStats' => $dashboardStats,
            'maxStatCount' => $maxStatCount,
            'activeApplicationsCount' => Application::where('status', 'active')->count(),
            'inactiveApplicationsCount' => Application::where('status', 'inactive')->count(),
            'availableServersCount' => VpnServer::where('available', true)->count(),
            'unavailableServersCount' => VpnServer::where('available', false)->count(),
            'activeUsersCount' => User::where('active', true)->count(),
            'inactiveUsersCount' => User::where('active', false)->count(),
            'recentUsers' => User::latest()->limit(10)->get(),
            'recentAuditLogs' => AuditLog::latest()->limit(10)->get(),
        ]);
    }
}
