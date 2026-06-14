<?php

namespace App\Console\Commands;

use App\Models\Device;
use App\Services\XrayAdminService;
use Illuminate\Console\Command;

class SyncDeviceTraffic extends Command
{
    protected $signature = 'devices:sync-traffic';
    protected $description = 'Sync device traffic from Xray stats';

    public function handle(XrayAdminService $xray)
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, Device> $devices */
        $devices = Device::query()
            ->whereNotNull('xray_email')
            ->get();

        foreach ($devices as $device) {
            try {
                $stats = $xray->getUserTrafficByEmail($device->xray_email);

                $device->upload_bytes = (int) ($stats['upload_bytes'] ?? 0);
                $device->download_bytes = (int) ($stats['download_bytes'] ?? 0);
                $device->save();

                $this->info("Synced device {$device->device_uid}");
            } catch (\Throwable $e) {
                $this->error("Failed for {$device->device_uid}: " . $e->getMessage());
            }
        }

        return self::SUCCESS;
    }
}
