<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\JwtService;
use Closure;
use Exception;
use Illuminate\Http\Request;

class JwtMiddleware
{
    public function __construct(
        private readonly JwtService $jwtService
    ) {}

    public function handle($request, Closure $next)
    {
        //$authHeader = $request->header('Authorization') ?: $request->bearerToken();

        $authHeader =
            $request->header('Authorization')
            ?? $request->header('X-Authorization')
            ?? ($_SERVER['HTTP_AUTHORIZATION'] ?? null)
            ?? ($_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? null);

        if (!$authHeader) {
            return response()->json(['message' => 'Missing Authorization header'], 401);
        }

        $token = $request->bearerToken() ?? trim(str_ireplace('Bearer', '', $authHeader));

        try {
            $payload = $this->jwtService->decode($token);

            if (isset($payload->exp) && time() > (int) $payload->exp) {
                return response()->json(['message' => 'Token expired'], 401);
            }

            if (!isset($payload->sub)) {
                return response()->json(['message' => 'Invalid token payload (sub missing)'], 401);
            }

            // Use find with active check for better security
            $user = User::where('id', $payload->sub)
                ->where('active', true)
                ->first();

            if (!$user) {
                return response()->json(['message' => 'User not found or inactive'], 401);
            }

            // set normal Laravel authenticated user resolver
            $request->setUserResolver(fn() => $user);

            // optional extra request attributes
            $request->attributes->set('auth_user', $user);
            $request->attributes->set('user_id', $user->id);
            $request->attributes->set('auth_token', $token);
        } catch (Exception $e) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        return $next($request);
    }
}
