<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;

class AdminDeviceController extends Controller
{
    /**
     * List all devices with pagination and search.
     */
    public function index(Request $request)
    {
        AuditService::log('admin.devices.index', 'Device', [
            'from' => false,
            'to' => true,
        ]);

        $pageSize = $request->integer('pageSize', 10);
        $search = trim((string) $request->get('search', ''));
        $sortBy = $request->get('sortBy', 'id');
        $sortDir = strtolower((string) $request->get('sortDir', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowedSorts = [
            'id',
            'device_uid',
            'user_id',
            'platform',
            'xray_email',
            'download_bytes',
            'upload_bytes',
            'created_at',
            'updated_at',
            'last_seen_at',
        ];

        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'id';
        }

        $query = Device::query()
            ->with('user:id,name,email')
            ->search($search)
            ->orderBy($sortBy, $sortDir);

        return response()->json($query->paginate($pageSize));
    }

    /**
     * Get a single device.
     */
    public function show($id)
    {
        AuditService::log('admin.devices.show', 'Device', [
            'from' => false,
            'to' => true,
        ]);

        $device = Device::with('user:id,name,email')->findOrFail($id);

        return response()->json($device);
    }

    /**
     * Create a new device.
     */
    public function store(Request $request)
    {
        AuditService::log('admin.devices.store', 'Device', [
            'from' => false,
            'to' => true,
        ]);

        $data = $request->validate([
            'device_uid' => ['required', 'string', 'max:255', 'unique:devices,device_uid'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'platform' => ['nullable', 'string', 'max:50'],
            'xray_client_uuid' => ['nullable', 'string', 'max:255'],
            'xray_email' => ['nullable', 'string', 'max:255'],
            'download_bytes' => ['nullable', 'integer', 'min:0'],
            'upload_bytes' => ['nullable', 'integer', 'min:0'],
            'last_seen_at' => ['nullable', 'date'],
        ]);

        $device = Device::create([
            'device_uid' => $data['device_uid'],
            'user_id' => $data['user_id'] ?? null,
            'platform' => $data['platform'] ?? 'android',
            'xray_client_uuid' => $data['xray_client_uuid'] ?? null,
            'xray_email' => $data['xray_email'] ?? null,
            'download_bytes' => $data['download_bytes'] ?? 0,
            'upload_bytes' => $data['upload_bytes'] ?? 0,
            'last_seen_at' => $data['last_seen_at'] ?? null,
        ]);

        return response()->json($device->load('user:id,name,email'), 201);
    }

    /**
     * Update a device.
     * Optimized to use fill() for mass assignment where possible.
     */
    public function update(Request $request, $id)
    {
        AuditService::log('admin.devices.update', 'Device', [
            'from' => false,
            'to' => true,
        ]);

        $device = Device::findOrFail($id);

        $data = $request->validate([
            'device_uid' => ['sometimes', 'required', 'string', 'max:255', 'unique:devices,device_uid,' . $device->id],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'platform' => ['nullable', 'string', 'max:50'],
            'xray_client_uuid' => ['nullable', 'string', 'max:255'],
            'xray_email' => ['nullable', 'string', 'max:255'],
            'download_bytes' => ['nullable', 'integer', 'min:0'],
            'upload_bytes' => ['nullable', 'integer', 'min:0'],
            'last_seen_at' => ['nullable', 'date'],
        ]);

        // Use fill for mass assignment on simple fields
        $fillableFields = [
            'device_uid',
            'user_id',
            'platform',
            'xray_client_uuid',
            'xray_email',
            'download_bytes',
            'upload_bytes',
            'last_seen_at',
        ];

        foreach ($fillableFields as $field) {
            if (array_key_exists($field, $data)) {
                $device->$field = $data[$field];
            }
        }

        $device->save();

        return response()->json($device->load('user:id,name,email'));
    }

    /**
     * Delete a device.
     */
    public function destroy($id)
    {
        AuditService::log('admin.devices.destroy', 'Device', [
            'from' => false,
            'to' => true,
        ]);

        $device = Device::findOrFail($id);
        $device->delete();

        return response()->json([
            'message' => 'Deleted',
        ]);
    }

    /**
     * Attach a user to a device.
     */
    public function attachUser(Request $request, $id)
    {
        AuditService::log('admin.devices.attach_user', 'Device', [
            'from' => false,
            'to' => true,
        ]);

        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $device = Device::findOrFail($id);
        $device->user_id = $data['user_id'];
        $device->save();

        return response()->json($device->load('user:id,name,email'));
    }

    /**
     * Detach user from a device.
     */
    public function detachUser($id)
    {
        AuditService::log('admin.devices.detach_user', 'Device', [
            'from' => false,
            'to' => true,
        ]);

        $device = Device::findOrFail($id);
        $device->user_id = null;
        $device->save();

        return response()->json($device->load('user:id,name,email'));
    }

    /**
     * Reset device traffic counters.
     */
    public function resetTraffic($id)
    {
        AuditService::log('admin.devices.reset_traffic', 'Device', [
            'from' => false,
            'to' => true,
        ]);

        $device = Device::findOrFail($id);
        $device->resetTraffic();

        return response()->json($device->load('user:id,name,email'));
    }
}
