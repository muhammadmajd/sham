<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if (!$user->is_admin) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $request->attributes->set('admin_user', $user);

        return $next($request);
    }
}
