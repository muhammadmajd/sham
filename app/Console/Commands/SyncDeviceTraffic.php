<?php

namespace App\Console\Commands;

use App\Models\Device;
use App\Services\XrayAdminService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncDeviceTraffic extends Command
{
    protected $signature = 'devices:sync-traffic';
    protected $description = 'Sync device traffic from Xray stats';

    public function handle(XrayAdminService $xray)
    {
        $devicesByEmail = Device::query()
            ->whereNotNull('xray_email')
            ->where('xray_email', '!=', '')
            ->pluck('id', 'xray_email');

        if ($devicesByEmail->isEmpty()) {
            $this->info('No devices with xray_email found.');
            return self::SUCCESS;
        }

        try {
            $trafficByEmail = $xray->getTrafficForEmails($devicesByEmail->keys()->all());
        } catch (\Throwable $e) {
            $this->error('Traffic sync failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        $now = now();
        $updates = [];

        foreach ($trafficByEmail as $email => $stats) {
            $deviceId = $devicesByEmail[$email] ?? null;

            if (!$deviceId) {
                continue;
            }

            $updates[] = [
                'id' => $deviceId,
                'upload_bytes' => (int) ($stats['upload_bytes'] ?? 0),
                'download_bytes' => (int) ($stats['download_bytes'] ?? 0),
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($updates, 500) as $chunk) {
            DB::table('devices')->upsert(
                $chunk,
                ['id'],
                ['upload_bytes', 'download_bytes', 'updated_at']
            );
        }

        $this->info(sprintf('Synced traffic for %d devices.', count($updates)));

        return self::SUCCESS;
    }
}
