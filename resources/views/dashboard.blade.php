<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Sham VPN') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/icon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/icon.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: Figtree, Arial, sans-serif; margin: 0; background: #f3f4f6; color: #111827; }
        a { text-decoration: none; }
        .page-wrap { width: 100%; padding: 1.25rem; box-sizing: border-box; }
        .hero { margin-bottom: 1.5rem; }
        .hero h1 { margin: 0; font-size: 1.875rem; line-height: 2.25rem; font-weight: 700; }
        .hero p { margin: .35rem 0 0; color: #64748b; }
        .panel { background: #fff; border: 1px solid #e5e7eb; border-radius: .75rem; box-shadow: 0 1px 2px rgba(15, 23, 42, .04); }
        .panel-pad { padding: 1.25rem; }
        .dashboard-shell { display: grid; grid-template-columns: 260px minmax(0, 1fr); gap: 1.25rem; align-items: start; }
        .admin-side-menu { position: sticky; top: 1rem; min-height: calc(100vh - 7.5rem); padding: 18px 14px; border: 1px solid #e5e7eb; border-radius: .75rem; background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%); box-sizing: border-box; box-shadow: 0 1px 2px rgba(15, 23, 42, .04); }
        .admin-side-brand { display: flex; align-items: center; min-height: 58px; padding: 12px; margin-bottom: 12px; border-radius: 12px; background: #111827; color: #fff; }
        .admin-side-brand span { display: block; color: #cbd5e1; font-size: 12px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
        .admin-side-brand strong { display: block; margin-top: 2px; font-size: 18px; line-height: 1.2; }
        .admin-side-nav { display: grid; gap: 7px; }
        .admin-side-link { display: flex; align-items: center; justify-content: space-between; gap: 10px; min-height: 44px; padding: 9px 10px; border-radius: 10px; color: #334155; transition: background .15s ease, color .15s ease, transform .15s ease, box-shadow .15s ease; }
        .admin-side-link:hover { background: #eef2f7; color: #0f172a; transform: translateX(2px); }
        .admin-side-link.is-active { background: color-mix(in srgb, var(--item-color) 13%, #fff); color: #0f172a; box-shadow: inset 3px 0 0 var(--item-color); }
        .admin-side-link-main { display: inline-flex; align-items: center; gap: 9px; min-width: 0; font-size: 14px; font-weight: 800; }
        .admin-side-dot { width: 9px; height: 9px; border-radius: 999px; background: var(--item-color); box-shadow: 0 0 0 3px color-mix(in srgb, var(--item-color) 16%, transparent); }
        .admin-side-count { min-width: 34px; padding: 3px 8px; border-radius: 999px; background: #fff; color: #475569; border: 1px solid #e2e8f0; font-size: 12px; font-weight: 800; text-align: center; }
        .quick-link { display: flex; justify-content: space-between; align-items: center; min-height: 58px; padding: .9rem 1rem; color: #111827; transition: transform .15s ease, border-color .15s ease, box-shadow .15s ease; }
        .quick-link:hover { transform: translateY(-1px); border-color: #cbd5e1; box-shadow: 0 10px 20px rgba(15, 23, 42, .08); }
        .quick-link span:first-child { font-weight: 700; }
        .quick-link span:last-child { color: #64748b; font-size: .8rem; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
        .stat-card { padding: 1rem; border-left: 4px solid var(--accent); }
        .stat-label { color: #64748b; font-size: .875rem; font-weight: 600; }
        .stat-value { font-size: 2rem; line-height: 2.25rem; font-weight: 700; margin-top: .35rem; }
        .stat-meter { height: .45rem; background: #e5e7eb; border-radius: 999px; overflow: hidden; margin-top: .85rem; }
        .stat-meter div { height: 100%; width: var(--width); background: var(--accent); border-radius: 999px; }
        .chart-grid { display: grid; grid-template-columns: minmax(0, 1.35fr) minmax(280px, .65fr); gap: 1rem; margin-bottom: 1.5rem; }
        .section-title { margin: 0 0 1rem; font-size: 1rem; font-weight: 700; }
        .bar-row { display: grid; grid-template-columns: 120px minmax(0, 1fr) 54px; align-items: center; gap: .75rem; margin-bottom: .85rem; font-size: .875rem; }
        .bar-track { height: .75rem; background: #eef2f7; border-radius: 999px; overflow: hidden; }
        .bar-fill { height: 100%; width: var(--width); background: var(--accent); border-radius: 999px; }
        .donut-wrap { display: grid; grid-template-columns: 150px 1fr; align-items: center; gap: 1rem; }
        .donut { width: 140px; height: 140px; border-radius: 50%; background: conic-gradient(#2563eb 0 var(--users), #db2777 var(--users) var(--apps), #16a34a var(--apps) var(--servers), #e5e7eb var(--servers) 100%); display: grid; place-items: center; }
        .donut::after { content: ""; width: 88px; height: 88px; background: #fff; border-radius: 50%; box-shadow: inset 0 0 0 1px #e5e7eb; }
        .legend { display: grid; gap: .6rem; font-size: .875rem; }
        .legend-item { display: flex; align-items: center; justify-content: space-between; gap: .75rem; color: #334155; }
        .legend-label { display: inline-flex; align-items: center; gap: .45rem; }
        .dot { width: .65rem; height: .65rem; border-radius: 999px; background: var(--accent); }
        .health-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: .85rem; }
        .health-item { background: #f8fafc; border: 1px solid #e5e7eb; border-radius: .6rem; padding: .85rem; }
        .health-item strong { display: block; font-size: 1.35rem; margin-top: .25rem; }
        .table-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: .875rem; }
        th, td { padding: .7rem .8rem; text-align: left; border-bottom: 1px solid #e5e7eb; white-space: nowrap; }
        th { color: #475569; background: #f8fafc; font-weight: 700; }
        td { color: #334155; }
        .badge { display: inline-flex; padding: .2rem .5rem; border-radius: 999px; background: #eef2ff; color: #3730a3; font-size: .75rem; font-weight: 700; }
        @media (max-width: 900px) {
            .dashboard-shell, .chart-grid, .table-grid { grid-template-columns: 1fr; }
            .admin-side-menu { position: static; min-height: 0; }
            .admin-side-nav { grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); }
        }
        @media (max-width: 520px) {
            .page-wrap { padding: 1rem; }
            .donut-wrap { grid-template-columns: 1fr; }
            .bar-row { grid-template-columns: 92px minmax(0, 1fr) 42px; }
        }
    </style>
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        @include('layouts.navigation')

        <main>
            <div class="page-wrap">
                <div class="hero">
                    <div>
                        <h1>Admin Dashboard</h1>
                        <p>Operational overview for users, infrastructure, subscriptions, and applications.</p>
                    </div>
                </div>

                <div class="dashboard-shell">
                    @include('layouts.admin-side-menu')

                    <section>
                        <div class="stats-grid">
                            @foreach ($dashboardStats as $stat)
                                <a href="{{ route($stat['route']) }}" class="panel stat-card" style="--accent: {{ $stat['color'] }};">
                                    <div class="stat-label">{{ $stat['label'] }}</div>
                                    <div class="stat-value">{{ number_format($stat['count']) }}</div>
                                    <div class="stat-meter">
                                        <div style="--width: {{ max(4, round(($stat['count'] / $maxStatCount) * 100)) }}%;"></div>
                                    </div>
                                </a>
                            @endforeach
                        </div>

                        <div class="chart-grid">
                            <div class="panel panel-pad">
                                <h2 class="section-title">Resource Distribution</h2>
                                @foreach ($dashboardStats as $stat)
                                    <div class="bar-row">
                                        <div>{{ $stat['label'] }}</div>
                                        <div class="bar-track">
                                            <div class="bar-fill" style="--accent: {{ $stat['color'] }}; --width: {{ max(3, round(($stat['count'] / $maxStatCount) * 100)) }}%;"></div>
                                        </div>
                                        <strong>{{ number_format($stat['count']) }}</strong>
                                    </div>
                                @endforeach
                            </div>

                            @php
                                $donutTotal = max(1, $usersCount + $applicationsCount + $serversCount);
                                $usersEnd = round(($usersCount / $donutTotal) * 100, 2);
                                $appsEnd = round((($usersCount + $applicationsCount) / $donutTotal) * 100, 2);
                                $serversEnd = 100;
                            @endphp

                            <div class="panel panel-pad">
                                <h2 class="section-title">Core Mix</h2>
                                <div class="donut-wrap">
                                    <div class="donut" style="--users: {{ $usersEnd }}%; --apps: {{ $appsEnd }}%; --servers: {{ $serversEnd }}%;"></div>
                                    <div class="legend">
                                        <div class="legend-item">
                                            <span class="legend-label"><span class="dot" style="--accent:#2563eb;"></span>Users</span>
                                            <strong>{{ number_format($usersCount) }}</strong>
                                        </div>
                                        <div class="legend-item">
                                            <span class="legend-label"><span class="dot" style="--accent:#db2777;"></span>Applications</span>
                                            <strong>{{ number_format($applicationsCount) }}</strong>
                                        </div>
                                        <div class="legend-item">
                                            <span class="legend-label"><span class="dot" style="--accent:#16a34a;"></span>Servers</span>
                                            <strong>{{ number_format($serversCount) }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="panel panel-pad" style="margin-bottom: 1.5rem;">
                            <h2 class="section-title">Status Snapshot</h2>
                            <div class="health-grid">
                                <div class="health-item">Active Users<strong>{{ number_format($activeUsersCount) }}</strong></div>
                                <div class="health-item">Inactive Users<strong>{{ number_format($inactiveUsersCount) }}</strong></div>
                                <div class="health-item">Available Servers<strong>{{ number_format($availableServersCount) }}</strong></div>
                                <div class="health-item">Unavailable Servers<strong>{{ number_format($unavailableServersCount) }}</strong></div>
                                <div class="health-item">Active Applications<strong>{{ number_format($activeApplicationsCount) }}</strong></div>
                                <div class="health-item">Inactive Applications<strong>{{ number_format($inactiveApplicationsCount) }}</strong></div>
                            </div>
                        </div>

                        <div class="table-grid">
                            <div class="panel panel-pad">
                                <h2 class="section-title">Recent Users</h2>
                                <div class="table-wrap">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Admin</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($recentUsers ?? [] as $user)
                                                <tr>
                                                    <td>{{ $user->id }}</td>
                                                    <td>{{ $user->name }}</td>
                                                    <td>{{ $user->email }}</td>
                                                    <td><span class="badge">{{ $user->is_admin ? 'Yes' : 'No' }}</span></td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4">No users found.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="panel panel-pad">
                                <h2 class="section-title">Recent Audit Logs</h2>
                                <div class="table-wrap">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Action</th>
                                                <th>Entity</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($recentAuditLogs ?? [] as $log)
                                                <tr>
                                                    <td>{{ $log->id }}</td>
                                                    <td>{{ $log->action }}</td>
                                                    <td>{{ $log->entity_type ?? '-' }} {{ $log->entity_id ? '#' . $log->entity_id : '' }}</td>
                                                    <td>{{ $log->created_at }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4">No logs found.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
