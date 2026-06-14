<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\User;
use App\Models\VpnServer;
use App\Services\VpnAccessPolicyService;
use App\Services\XrayAdminService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DeviceController extends Controller
{
    public function register(Request $request, XrayAdminService $xray)
    {
        $data = $request->validate([
            'device_uid' => ['required', 'string', 'max:255'],
            'platform'   => ['nullable', 'string', 'max:50'],
        ]);

        $device = Device::firstOrCreate(
            ['device_uid' => $data['device_uid']],
            [
                'platform' => $data['platform'] ?? 'android',
                'xray_client_uuid' => (string) Str::uuid(),
                'last_seen_at' => now(),
            ]
        );

        if (empty($device->xray_email)) {
            $device->xray_email = 'device:' . $device->id;
        }

        if (!$device->xray_client_uuid) {
            $device->xray_client_uuid = (string) Str::uuid();
        }

        if (empty($device->platform) && !empty($data['platform'])) {
            $device->platform = $data['platform'];
        }

        $device->last_seen_at = now();
        $device->save();

        $provisioning = $xray->ensureClientExists(
            uuid: $device->xray_client_uuid,
            email: $device->xray_email,
        );

        $httpSuccess = (bool) ($provisioning['success'] ?? false);
        $statusCode = $httpSuccess ? 200 : 207;

        return response()->json([
            'success' => $httpSuccess,
            'device_id' => $device->id,
            'device_uid' => $device->device_uid,
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

    public function attachUser(Request $request, XrayAdminService $xray)
    {
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

        $oldUuid = $device->xray_client_uuid;
        $oldEmail = $device->xray_email;
        $oldUserId = $device->user_id;

        $device->user_id = (int) $data['user_id'];
        //$device->xray_client_uuid = (string) Str::uuid();
        //$device->xray_email = 'user:' . $data['user_id'];
        $device->last_seen_at = now();
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
