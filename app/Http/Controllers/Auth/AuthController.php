<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\RefreshToken;
use App\Models\User;
use App\Services\AuditService;
use App\Services\JwtService;
use App\Services\XrayAdminService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    private JwtService $jwt;

    public function __construct(JwtService $jwt)
    {
        $this->jwt = $jwt;
    }

    public function signup(Request $request, XrayAdminService $xray)
    {
        AuditService::log('auth.signup', 'User', [
            'from' => false,
            'to' => true,
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:6'],
            'device_uid' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'uuid' => (string) Str::uuid(),
        ]);

        if (!empty($validated['device_uid'])) {
            $this->attachDeviceToUser(
                deviceUid: $validated['device_uid'],
                user: $user,
                xray: $xray,
            );
        }

        return $this->issueAuthResponse($user, $xray);
    }

    public function login(Request $request, XrayAdminService $xray)
    {
        AuditService::log('auth.login', 'User', [
            'from' => false,
            'to' => true,
        ]);

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'device_uid' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        if (!$user->active) {
            return response()->json([
                'message' => 'User is inactive',
            ], 401);
        }

        if (!empty($validated['device_uid'])) {
            $this->attachDeviceToUser(
                deviceUid: $validated['device_uid'],
                user: $user,
                xray: $xray,
            );
        }

        return $this->issueAuthResponse($user, $xray);
    }

    public function loginByCode(Request $request, XrayAdminService $xray)
    {
        AuditService::log('auth.loginByCode', 'User', [
            'from' => 'code',
            'to' => true,
        ]);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:100'],
            'device_uid' => ['nullable', 'string', 'max:255'],
        ]);

        $code = trim($validated['code']);

        $user = User::where('code', $code)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Invalid code',
            ], 401);
        }

        if (!$user->active) {
            return response()->json([
                'message' => 'User is inactive',
            ], 401);
        }

        if (!empty($validated['device_uid'])) {
            $this->attachDeviceToUser(
                deviceUid: $validated['device_uid'],
                user: $user,
                xray: $xray,
            );
        }

        return $this->issueAuthResponse($user, $xray);
    }

    public function refresh(Request $request)
    {
        AuditService::log('auth.refresh', 'User', [
            'from' => false,
            'to' => true,
        ]);

        $request->validate([
            'refresh_token' => ['required'],
        ]);

        $hashed = hash('sha256', $request->refresh_token);

        $token = RefreshToken::where('token', $hashed)
            ->where('revoked', false)
            ->first();

        if (!$token || Carbon::now()->greaterThan($token->expires_at)) {
            return response()->json([
                'message' => 'Invalid or expired refresh token',
            ], 401);
        }

        $user = User::find($token->user_id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        $accessToken = $this->jwt->createAccessToken($user);
        $newPlain = Str::random(80);

        $token->update([
            'revoked' => true,
        ]);

        RefreshToken::create([
            'user_id' => $user->id,
            'token' => hash('sha256', $newPlain),
            'expires_at' => Carbon::now()->addDays(30),
            'revoked' => false,
        ]);

        return response()->json([
            'accessToken' => $accessToken,
            'refreshToken' => $newPlain,
            'expiresInMinutes' => 15,
        ]);
    }

    public function me(Request $request)
    {
        AuditService::log('auth.me', 'User', [
            'from' => false,
            'to' => true,
        ]);

        $user = $request->user();

        if (!$user && !empty($request->user_id)) {
            $user = User::find($request->user_id);
        }

        if (!$user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        return response()->json($user->load('plan'));
    }

    public function logout(Request $request)
    {
        AuditService::log('auth.logout', 'User', [
            'from' => false,
            'to' => true,
        ]);

        $request->validate([
            'refresh_token' => ['required'],
        ]);

        $hashed = hash('sha256', $request->refresh_token);

        RefreshToken::where('token', $hashed)->update([
            'revoked' => true,
        ]);

        return response()->json([
            'message' => 'Logged out',
        ]);
    }

    public function getCode(Request $request)
    {
        AuditService::log('auth.getCode', 'User', [
            'from' => false,
            'to' => true,
        ]);

        $validated = $request->validate([
            'device_uid' => ['required', 'string', 'max:255'],
        ]);

        $device = Device::where('device_uid', $validated['device_uid'])->first();

        if (!$device || !$device->user_id) {
            return response()->json([
                'message' => 'No user found for this device',
            ], 404);
        }

        $user = User::find($device->user_id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        return response()->json([
            'code' => $user->code,
            'active' => (bool) $user->active,
            'user_id' => $user->id,
        ]);
    }

    private function issueAuthResponse(User $user, XrayAdminService $xray)
    {
        $xray->ensureClientExists(
            uuid: $user->uuid,
            email: 'user:' . $user->id,
        );

        $accessToken = $this->jwt->createAccessToken($user);
        $refreshTokenPlain = Str::random(80);

        RefreshToken::create([
            'user_id' => $user->id,
            'token' => hash('sha256', $refreshTokenPlain),
            'expires_at' => Carbon::now()->addDays(30),
            'revoked' => false,
        ]);

        return response()->json([
            'user' => $user->load('plan'),
            'accessToken' => $accessToken,
            'refreshToken' => $refreshTokenPlain,
            'expiresInMinutes' => 15,
        ]);
    }

    private function attachDeviceToUser(
        string $deviceUid,
        User $user,
        XrayAdminService $xray
    ): void {
        $device = Device::where('device_uid', $deviceUid)->first();

        if (!$device) {
            return;
        }

        $oldUuid = $device->xray_client_uuid;

        $device->user_id = $user->id;
        $device->last_seen_at = now();
        $device->save();

        $xray->ensureClientExists(
            uuid: $device->xray_client_uuid,
            email: $device->xray_email,
        );

        if ($oldUuid && $oldUuid !== $device->xray_client_uuid) {
            $xray->removeClientByUuid($oldUuid);
        }
    }
}
