<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="icon" type="image/png" href="{{ asset('images/icon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/icon.png') }}">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: #f5f7fb;
            color: #111827;
        }

        .sidebar {
            width: 240px;
            background: #111827;
            color: #fff;
            min-height: 100vh;
            position: fixed;
            padding: 20px;
            box-sizing: border-box;
        }

        .sidebar a {
            color: #fff;
            text-decoration: none;
            display: block;
            padding: 8px 0;
        }

        .content {
            margin-left: 260px;
            padding: 24px;
        }

        .card {
            background: #fff;
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .06);
        }

        .btn {
            background: #2563eb;
            color: #fff;
            border: 0;
            padding: 10px 14px;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .btn-danger {
            background: #dc2626;
        }

        .btn-gray {
            background: #6b7280;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            box-sizing: border-box;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
        }

        th,
        td {
            border-bottom: 1px solid #e5e7eb;
            padding: 10px;
            text-align: left;
        }

        th {
            background: #f3f4f6;
        }

        .row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .mb-3 {
            margin-bottom: 14px;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 12px;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 12px;
        }

        .pagination-wrap {
            margin-top: 16px;
            display: flex;
            justify-content: center;
        }

        .pagination {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .pagination li {
            list-style: none;
        }

        .pagination li a,
        .pagination li span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 34px;
            height: 34px;
            padding: 0 10px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            background: #fff;
            color: #111827;
            text-decoration: none;
            font-size: 14px;
            line-height: 1;
            box-sizing: border-box;
        }

        .pagination li.active span {
            background: #2563eb;
            color: #fff;
            border-color: #2563eb;
        }

        .pagination li.disabled span {
            color: #9ca3af;
            background: #f3f4f6;
            border-color: #e5e7eb;
            cursor: not-allowed;
        }

        .pagination li a:hover {
            background: #f9fafb;
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <h2>Admin</h2>
        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
        <a href="{{ route('admin.users.index') }}">Users</a>
        <a href="{{ route('admin.plans.index') }}">Plans</a>
        <a href="{{ route('admin.servers.index') }}">Servers</a>
        <a href="{{ route('admin.devices.index') }}">Devices</a>
        <a href="{{ route('admin.applications.index') }}">Applications</a>
        <a href="{{ route('admin.subscriptions.index') }}">Subscriptions</a>
        <a href="{{ route('admin.audit-logs.index') }}">Audit Logs</a>

        <form method="POST" action="{{ route('logout') }}" style="margin-top:20px;">
            @csrf
            <button type="submit" class="btn btn-danger">Logout</button>
        </form>
    </div>

    <div class="content">
        @if (session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert-error">
                <ul style="margin:0; padding-left:18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </div>
</body>

</html>
