<?php

namespace App\Console\Commands;

use App\Models\Device;
use App\Services\XrayAdminService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        $this->info('Devices with xray_email: ' . $devicesByEmail->count());

        Log::info('devices:sync-traffic started', [
            'devices_count' => $devicesByEmail->count(),
            'emails_sample' => $devicesByEmail->keys()->take(10)->values()->all(),
        ]);

        if ($devicesByEmail->isEmpty()) {
            $this->info('No devices with xray_email found.');
            return self::SUCCESS;
        }

        try {
            $trafficByEmail = $xray->getTrafficForEmails($devicesByEmail->keys()->all());
            $this->info('DB emails:');
            foreach ($devicesByEmail as $email => $id) {
                $this->line("Device ID={$id}, xray_email={$email}");
            }

            $this->info('Traffic result:');
            foreach ($trafficByEmail as $email => $stats) {
                $this->line(
                    $email .
                        ' upload=' . ($stats['upload_bytes'] ?? 0) .
                        ' download=' . ($stats['download_bytes'] ?? 0)
                );
            }
        } catch (\Throwable $e) {
            $this->error('Traffic sync failed: ' . $e->getMessage());

            Log::error('devices:sync-traffic failed', [
                'error' => $e->getMessage(),
            ]);

            return self::FAILURE;
        }

        $this->info('Traffic rows returned: ' . count($trafficByEmail));

        Log::info('devices:sync-traffic traffic result sample', [
            'traffic_sample' => array_slice($trafficByEmail, 0, 10, true),
        ]);

        $now = now();
        $updates = [];

        foreach ($trafficByEmail as $email => $stats) {
            $deviceId = $devicesByEmail[$email] ?? null;

            if (!$deviceId) {
                continue;
            }

            $servers = $stats['servers'] ?? [];

            $hasSuccessfulServer = collect($servers)->contains(function ($server) {
                return ($server['success'] ?? false) === true;
            });

            if (!$hasSuccessfulServer) {
                $this->warn("Skipping {$email}: all servers failed.");
                Log::warning('devices:sync-traffic skipped device because all servers failed', [
                    'email' => $email,
                    'device_id' => $deviceId,
                    'servers' => $servers,
                ]);
                continue;
            }

            $upload = (int) ($stats['upload_bytes'] ?? 0);
            $download = (int) ($stats['download_bytes'] ?? 0);

            $this->line("Email={$email}, upload={$upload}, download={$download}");

            $updates[] = [
                'id' => $deviceId,
                'upload_bytes' => $upload,
                'download_bytes' => $download,
                'updated_at' => $now,
            ];
        }

        if (empty($updates)) {
            $this->warn('No updates generated.');
            return self::SUCCESS;
        }

        foreach ($updates as $row) {
            DB::table('devices')
                ->where('id', $row['id'])
                ->update([
                    'upload_bytes' => $row['upload_bytes'],
                    'download_bytes' => $row['download_bytes'],
                    'updated_at' => $row['updated_at'],
                ]);
        }

        $this->info(sprintf('Synced traffic for %d devices.', count($updates)));

        Log::info('devices:sync-traffic finished', [
            'updates_count' => count($updates),
        ]);

        return self::SUCCESS;
    }
}
