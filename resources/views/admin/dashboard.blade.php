@extends('admin.layouts.app')

@section('content')
<div class="card">
    <h1 class="text-2xl font-bold mb-6">Admin Dashboard</h1>

    <div class="row">
        <div class="card">
            <div class="text-sm text-gray-500">Users</div>
            <div class="text-3xl font-bold mt-2">{{ $usersCount }}</div>
        </div>

        <div class="card">
            <div class="text-sm text-gray-500">Admins</div>
            <div class="text-3xl font-bold mt-2">{{ $adminsCount }}</div>
        </div>

        <div class="card">
            <div class="text-sm text-gray-500">Plans</div>
            <div class="text-3xl font-bold mt-2">{{ $plansCount }}</div>
        </div>

        <div class="card">
            <div class="text-sm text-gray-500">Servers</div>
            <div class="text-3xl font-bold mt-2">{{ $serversCount }}</div>
        </div>

        <div class="card">
            <div class="text-sm text-gray-500">Devices</div>
            <div class="text-3xl font-bold mt-2">{{ $devicesCount }}</div>
        </div>

        <div class="card">
            <div class="text-sm text-gray-500">Applications</div>
            <div class="text-3xl font-bold mt-2">{{ $applicationsCount ?? 0 }}</div>
        </div>

        <div class="card">
            <div class="text-sm text-gray-500">Subscriptions</div>
            <div class="text-3xl font-bold mt-2">{{ $subscriptionsCount }}</div>
        </div>

        <div class="card">
            <div class="text-sm text-gray-500">Audit Logs</div>
            <div class="text-3xl font-bold mt-2">{{ $auditLogsCount }}</div>
        </div>
    </div>
</div>

<div class="card">
    <h3 class="text-lg font-semibold mb-4">Quick Links</h3>

    <div class="flex flex-wrap gap-3">
        <a href="{{ route('admin.users.index') }}" class="btn">Users</a>
        <a href="{{ route('admin.plans.index') }}" class="btn">Plans</a>
        <a href="{{ route('admin.servers.index') }}" class="btn">Servers</a>
        <a href="{{ route('admin.devices.index') }}" class="btn">Devices</a>
        <a href="{{ route('admin.applications.index') }}" class="btn">Applications</a>
        <a href="{{ route('admin.subscriptions.index') }}" class="btn">Subscriptions</a>
        <a href="{{ route('admin.audit-logs.index') }}" class="btn">Audit Logs</a>
    </div>
</div>

<div class="row">
    <div class="card">
        <h3 class="text-lg font-semibold mb-4">Recent Users</h3>

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
                @forelse($recentUsers as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->is_admin ? 'Yes' : 'No' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card">
        <h3 class="text-lg font-semibold mb-4">Recent Audit Logs</h3>

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
                @forelse($recentAuditLogs as $log)
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
@endsection
