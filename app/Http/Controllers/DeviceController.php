<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\User;
use App\Models\VpnServer;
use App\Models\Plan;
use App\Services\VpnAccessPolicyService;
use App\Services\XrayAdminService;
use App\Services\TrafficLimitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class DeviceController extends Controller
{
    public function register(
        Request $request,
        XrayAdminService $xray,
        TrafficLimitService $trafficLimits
    ) {
        $startTime = microtime(true);

        Log::info('Device register endpoint HIT - before validation', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'body' => $request->all(),
            'ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
        ]);


        $data = $request->validate([
            'device_uid' => ['required', 'string', 'max:255'],
            'platform' => ['nullable', 'string', 'max:50'],
            'device_uid_source' => ['nullable', 'string', 'max:50'],
        ]);

        $deviceUid = strtolower(trim($data['device_uid']));
        $platform = strtolower(trim($data['platform'] ?? 'android'));
        $deviceUidSource = strtolower(trim($data['device_uid_source'] ?? 'unknown'));

        if ($deviceUid === '') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid device_uid',
            ], 422);
        }

        Log::info('Device registration attempt', [
            'device_uid' => $deviceUid,
            'platform' => $platform,
            'device_uid_source' => $deviceUidSource,
            'ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
        ]);

        try {
            $device = DB::transaction(function () use ($deviceUid, $platform, $deviceUidSource, $request, $trafficLimits) {
                $device = Device::where('device_uid', $deviceUid)
                    ->lockForUpdate()
                    ->first();

                if (!$device) {
                    $device = new Device();
                    $device->device_uid = $deviceUid;
                    $device->platform = $platform;
                    $device->device_uid_source = $deviceUidSource;
                    $device->xray_client_uuid = (string) Str::uuid();
                    $device->first_seen_at = now();
                }

                Log::info('Device registration - before save', [
                    'device_uid' => $device->device_uid,
                    'platform' => $device->platform,
                    'device_uid_source' => $device->device_uid_source,
                    'xray_client_uuid' => $device->xray_client_uuid,
                    'first_seen_at' => optional($device->first_seen_at)->toISOString(),
                ]);

                if (empty($device->xray_client_uuid)) {
                    $device->xray_client_uuid = (string) Str::uuid();
                }

                if (empty($device->platform) && !empty($platform)) {
                    $device->platform = $platform;
                }

                if (empty($device->device_uid_source) && !empty($deviceUidSource)) {
                    $device->device_uid_source = $deviceUidSource;
                }

                $device->last_seen_at = now();
                $device->last_ip = $request->ip();
                $device->last_user_agent = substr((string) $request->userAgent(), 0, 1000);

                /*
                * If device is not attached to any user, give it free plan traffic limit.
                * If it is already attached to a user, give it per-device user limit.
                */
                if (empty($device->user_id)) {
                    $trafficLimits->setGuestDeviceLimit($device, false);
                } else {
                    $trafficLimits->applyCorrectDeviceLimit($device, false);
                }

                $device->save();

                Log::info('Device registration - after save', [
                    'device_uid' => $device->device_uid,
                    'platform' => $device->platform,
                    'device_uid_source' => $device->device_uid_source,
                    'xray_client_uuid' => $device->xray_client_uuid,
                    'first_seen_at' => optional($device->first_seen_at)->toISOString(),
                    'last_seen_at' => optional($device->last_seen_at)->toISOString(),
                ]);

                if (empty($device->xray_email)) {
                    $device->xray_email = 'device:' . $device->platform . ':' . $device->id;
                    $device->save();
                }

                return $device;
            });
        } catch (\Throwable $e) {
            report($e);
            Log::error('Device registration failed', [
                'device_uid' => $data['device_uid'],
                'platform' => $data['platform'] ?? null,
                'device_uid_source' => $data['device_uid_source'] ?? null,
                'error_message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to register device',
            ], 500);
        }

        Log::info('Starting Xray provisioning', [
            'device_id' => $device->id,
            'device_uid' => $device->device_uid,
            'xray_client_uuid' => $device->xray_client_uuid,
            'xray_email' => $device->xray_email,
        ]);

        try {
            $provisioning = $xray->ensureClientExists(
                uuid: $device->xray_client_uuid,
                email: $device->xray_email,
            );

            Log::info('Xray provisioning completed', [
                'device_id' => $device->id,
                'success' => $provisioning['success'] ?? false,
                'result' => $provisioning,
            ]);
        } catch (\Throwable $e) {
            report($e);

            Log::error('Xray provisioning failed', [
                'device_id' => $device->id,
                'device_uid' => $device->device_uid,
                'xray_client_uuid' => $device->xray_client_uuid,
                'xray_email' => $device->xray_email,
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Device registered but provisioning failed',
            ], 500);
        }

        $httpSuccess = (bool) ($provisioning['success'] ?? false);

        Log::info('Sending registration response', [
            'device_id' => $device->id,
            'http_success' => $httpSuccess,
            'status_code' => $httpSuccess ? 200 : 207,
        ]);

        $statusCode = $httpSuccess ? 200 : 207;

        Log::info('Device registration completed', [
            'device_id' => $device->id,
            'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
        ]);

        return response()->json([
            'success' => $httpSuccess,
            'device_id' => $device->id,
            'device_uid' => $device->device_uid,
            'device_uid_source' => $device->device_uid_source,
            'platform' => $device->platform,
            'xray_client_uuid' => $device->xray_client_uuid,
            'xray_email' => $device->xray_email,
            'message' => $httpSuccess
                ? 'Device registered and provisioned on all servers'
                : 'Device registered, but provisioning failed on one or more servers',
            'provisioning' => $provisioning,
        ], $statusCode);
    }

    public function usage(Request $request)
    {
        $data = $request->validate([
            'device_uid' => ['required', 'string'],
        ]);

        $device = Device::where('device_uid', $data['device_uid'])->first();

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device not found',
                'download_bytes' => 0,
                'upload_bytes' => 0,
            ], 404);
        }

        if (!$device->xray_email) {
            return response()->json([
                'success' => false,
                'message' => 'Device has no xray_email',
                'device_uid' => $device->device_uid,
                'download_bytes' => 0,
                'upload_bytes' => 0,
            ], 422);
        }

        return response()->json([
            'success' => true,
            'device_uid' => $device->device_uid,
            'xray_email' => $device->xray_email,
            'download_bytes' => (int) $device->download_bytes,
            'upload_bytes' => (int) $device->upload_bytes,
            'synced_at' => optional($device->updated_at)->toISOString(),
        ]);
    }

    public function attachUser(
        Request $request,
        XrayAdminService $xray,
        TrafficLimitService $trafficLimits
    ) {
        $data = $request->validate([
            'device_uid' => ['required', 'string'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $device = Device::where('device_uid', $data['device_uid'])->first();

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device not found',
            ], 404);
        }

        $user = User::findOrFail($data['user_id']);

        $hasActivePaidPlan = $user->hasActiveSubscription();

        if ($hasActivePaidPlan && !empty($user->plan_id)) {
            $plan = Plan::find($user->plan_id);
        } else {
            $plan = Plan::where('key', 'free')->first();
        }

        if (!$plan) {
            return response()->json([
                'success' => false,
                'reason' => 'subscription_required',
                'message' => 'No free plan is available. Please subscribe.',
            ], 403);
        }

        $devicesLimit = (int) ($plan->devices_number ?? 0);

        $otherDevicesCount = Device::query()
            ->where('user_id', $user->id)
            ->where('id', '!=', $device->id)
            ->count();

        $effectiveDevicesCount = $otherDevicesCount + 1;

        if ($devicesLimit > 0 && $effectiveDevicesCount > $devicesLimit) {
            return response()->json([
                'success' => false,
                'reason' => $hasActivePaidPlan
                    ? 'plan_device_limit_reached'
                    : 'free_device_limit_reached',
                'message' => $hasActivePaidPlan
                    ? "You cannot connect a new device. Device limit is {$devicesLimit} for this plan. Please subscribe to another plan."
                    : "You cannot connect a new device. Device limit is {$devicesLimit} for the free plan. Please subscribe.",
                'devices_limit' => $devicesLimit,
                'devices_count' => $effectiveDevicesCount,
            ], 403);
        }

        $oldUuid = $device->xray_client_uuid;
        $oldEmail = $device->xray_email;
        $oldUserId = $device->user_id;

        $device->user_id = (int) $data['user_id'];
        //$device->xray_client_uuid = (string) Str::uuid();
        //$device->xray_email = 'user:' . $data['user_id'];
        $device->last_seen_at = now();
        /*
        * After attaching device to user:
        * device.traffic_limit_bytes = user.traffic_limit / plan.devices_number
        */
        $trafficLimits->setDeviceLimitForUser($device, $user, false);

        $device->save();

        Log::info('device.attachUser start', [
            'device_uid' => $device->device_uid,
            'old_user_id' => $oldUserId,
            'new_user_id' => $device->user_id,
            'old_uuid' => $oldUuid,
            'old_email' => $oldEmail,
            'new_uuid' => $device->xray_client_uuid,
            'new_email' => $device->xray_email,
        ]);

        $provisioning = $xray->ensureClientExists(
            uuid: $device->xray_client_uuid,
            email: $device->xray_email,
        );

        $removal = null;
        if ($oldUuid && $oldUuid !== $device->xray_client_uuid) {
            $removal = $xray->removeClientByUuid($oldUuid);
        }

        $provisioningSuccess = (bool) ($provisioning['success'] ?? false);
        $removalSuccess = $removal === null ? true : (bool) ($removal['success'] ?? false);

        $overallSuccess = $provisioningSuccess && $removalSuccess;
        $statusCode = $overallSuccess ? 200 : 207;

        return response()->json([
            'success' => $overallSuccess,
            'device_uid' => $device->device_uid,
            'user_id' => $device->user_id,
            'xray_client_uuid' => $device->xray_client_uuid,
            'xray_email' => $device->xray_email,
            'message' => $overallSuccess
                ? 'Device attached and synchronized on all servers'
                : 'Device attached, but one or more server synchronization steps failed',
            'provisioning' => $provisioning,
            'removal' => $removal,
        ], $statusCode);
    }

    public function accessStatus(Request $request, VpnAccessPolicyService $policy)
    {
        $data = $request->validate([
            'device_uid' => ['required', 'string'],
            'server_id' => ['required', 'integer', 'exists:vpn_servers,id'],
        ]);

        $device = Device::where('device_uid', $data['device_uid'])->first();

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device not found',
            ], 404);
        }

        $server = VpnServer::find($data['server_id']);

        if (!$server) {
            return response()->json([
                'success' => false,
                'message' => 'Server not found',
            ], 404);
        }

        $user = $request->user();

        if (!$user && !empty($device->user_id)) {
            $user = User::find($device->user_id);
        }

        $decision = $policy->decide($user, $device, $server);

        return response()->json([
            'success' => true,
            ...$decision,
        ]);
    }
}
