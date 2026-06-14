<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;

class DevicePageController extends Controller
{
    public function index(Request $request)
    {
        AuditService::log('Device.index', 'Device', [
            'by' => 'admin',
        ]);
        $search = trim((string) $request->get('search', ''));

        $devices = Device::query()
            ->with('user:id,name,email')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('device_uid', 'like', "%{$search}%")
                        ->orWhere('platform', 'like', "%{$search}%")
                        ->orWhere('xray_email', 'like', "%{$search}%")
                        ->orWhere('xray_client_uuid', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($u) use ($search) {
                            $u->where('name', 'like', "%{$search}%")
                              ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.devices.index', [
            'devices' => $devices,
            'search' => $search,
            'users' => User::orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    public function create()
    {
        return view('admin.devices.create', [
            'users' => User::orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    public function store(Request $request)
    {
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

        Device::create([
            'device_uid' => $data['device_uid'],
            'user_id' => $data['user_id'] ?? null,
            'platform' => $data['platform'] ?? 'android',
            'xray_client_uuid' => $data['xray_client_uuid'] ?? null,
            'xray_email' => $data['xray_email'] ?? null,
            'download_bytes' => $data['download_bytes'] ?? 0,
            'upload_bytes' => $data['upload_bytes'] ?? 0,
            'last_seen_at' => $data['last_seen_at'] ?? null,
        ]);
        AuditService::log('Device.store', 'Device', [
            'by' => 'admin',
            'device_uid' => $data['device_uid'],
        ]);

        return redirect()->route('admin.devices.index')->with('success', 'Device created.');
    }

    public function edit(Device $device)
    {
        return view('admin.devices.edit', [
            'device' => $device,
            'users' => User::orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    public function update(Request $request, Device $device)
    {
        $data = $request->validate([
            'device_uid' => ['required', 'string', 'max:255', 'unique:devices,device_uid,' . $device->id],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'platform' => ['nullable', 'string', 'max:50'],
            'xray_client_uuid' => ['nullable', 'string', 'max:255'],
            'xray_email' => ['nullable', 'string', 'max:255'],
            'download_bytes' => ['nullable', 'integer', 'min:0'],
            'upload_bytes' => ['nullable', 'integer', 'min:0'],
            'last_seen_at' => ['nullable', 'date'],
        ]);

        $device->update($data);
        AuditService::log('Device.update', 'Device', [
            'by' => 'admin',
            'device_uid' => $data['device_uid'],
        ]);

        return redirect()->route('admin.devices.index')->with('success', 'Device updated.');
    }

    public function destroy(Device $device)
    {
        $device->delete();
        AuditService::log('Device.destroy', 'Device', [
            'by' => 'admin',
            'device_uid' =>$device['device_uid'],
        ]);

        return redirect()->route('admin.devices.index')->with('success', 'Device deleted.');
    }

    public function attachUser(Request $request, Device $device)
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $device->user_id = $data['user_id'];
        $device->save();
        AuditService::log('Device.attachUser', 'Device', [
            'by' => 'admin',
            'device_uid' =>$device['device_uid'],
            'user_id' =>$data['user_id'],
        ]);

        return redirect()->route('admin.devices.index')->with('success', 'User attached.');
    }

    public function detachUser(Device $device)
    {
        $device->user_id = null;
        $device->save();

        AuditService::log('Device.detachUser', 'Device', [
            'by' => 'admin',
            'device_uid' =>$device['device_uid'],
            'user_id' =>$device['user_id'],
        ]);
        return redirect()->route('admin.devices.index')->with('success', 'User detached.');
    }

    public function resetTraffic(Device $device)
    {
        $device->download_bytes = 0;
        $device->upload_bytes = 0;
        $device->save();

        AuditService::log('Device.resetTraffic', 'Device', [
            'by' => 'admin',
            'device_uid' =>$device['device_uid']
        ]);

        return redirect()->route('admin.devices.index')->with('success', 'Traffic reset.');
    }

}
