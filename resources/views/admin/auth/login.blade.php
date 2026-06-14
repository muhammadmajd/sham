<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="icon" type="image/png" href="{{ asset('images/icon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/icon.png') }}">
    <style>
        body { font-family: Arial, sans-serif; background: #f5f7fb; display:flex; justify-content:center; align-items:center; min-height:100vh; margin:0; }
        .card { width: 420px; background:#fff; padding:24px; border-radius:12px; box-shadow: 0 2px 10px rgba(0,0,0,.08); }
        input { width:100%; padding:10px; border:1px solid #d1d5db; border-radius:8px; box-sizing:border-box; margin-bottom:12px; }
        button { width:100%; padding:10px; background:#2563eb; color:#fff; border:0; border-radius:8px; cursor:pointer; }
        .error { background:#fee2e2; color:#991b1b; padding:10px; border-radius:8px; margin-bottom:12px; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Admin Login</h2>

        @if($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('admin.login.submit') }}">
            @csrf
            <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" required>
            <input type="password" name="password" placeholder="Password" required>

            <label style="display:block; margin-bottom:12px;">
                <input type="checkbox" name="remember" value="1" style="width:auto;"> Remember me
            </label>

            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
