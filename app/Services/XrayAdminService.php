<?php

namespace App\Services;

use App\Models\VpnServer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Schema;

class XrayAdminService
{
    private ?Collection $serversCache = null;
    private bool $hasIsActiveColumn = true;

    public function __construct()
    {
        // Check for is_active column once during service initialization
        $this->hasIsActiveColumn = $this->checkHasIsActiveColumn();
    }

    /**
     * Check if the is_active column exists in vpn_servers table.
     */
    private function checkHasIsActiveColumn(): bool
    {
        try {
            return Schema::hasColumn('vpn_servers', 'is_active');
        } catch (\Throwable $e) {
            Log::warning('Could not check is_active column', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function ensureClientExists(string $uuid, string $email): array
    {
        $results = [];
        $servers = $this->getServers();

        foreach ($servers as $server) {
            try {
                $script = sprintf(
                    "sudo /usr/local/bin/xray_client_sync.py %s %s %s %s && sudo %s run -test -c %s && sudo /usr/bin/systemctl restart xray",
                    $this->shellQuotePosix($server->ssh_config_path),
                    $this->shellQuotePosix($uuid),
                    $this->shellQuotePosix($email),
                    $this->shellQuotePosix($server->ssh_vless_flow),
                    $server->xray_bin_path,
                    $this->shellQuotePosix($server->ssh_config_path),
                );
                $output = $this->runSshToHost($server, $script);

                $results[] = [
                    'server_id' => $server->id,
                    'host' => $server->host,
                    'success' => true,
                    'action' => 'ensure_client',
                    'output' => trim($output),
                    'message' => 'Client ensured successfully',
                ];
            } catch (\Throwable $e) {
                Log::error('ensureClientExists failed on server', [
                    'server_id' => $server->id,
                    'host' => $server->host,
                    'uuid' => $uuid,
                    'email' => $email,
                    'error' => $e->getMessage(),
                ]);

                $results[] = [
                    'server_id' => $server->id,
                    'host' => $server->host,
                    'success' => false,
                    'action' => 'ensure_client',
                    'output' => null,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return $this->buildSummary($results);
    }

    public function removeClientByUuid(string $uuid): array
    {
        $results = [];
        $servers = $this->getServers();

        foreach ($servers as $server) {
            try {
                $script = sprintf(
                    "sudo /usr/local/bin/xray_client_remove.py %s %s && sudo %s run -test -c %s && sudo /usr/bin/systemctl restart xray",
                    $this->shellQuotePosix($server->ssh_config_path),
                    $this->shellQuotePosix($uuid),
                    $server->xray_bin_path,
                    $this->shellQuotePosix($server->ssh_config_path),
                );
                $output = $this->runSshToHost($server, $script);

                $results[] = [
                    'server_id' => $server->id,
                    'host' => $server->host,
                    'success' => true,
                    'action' => 'remove_client',
                    'output' => trim($output),
                    'message' => 'Client removed successfully',
                ];
            } catch (\Throwable $e) {
                Log::error('removeClientByUuid failed on server', [
                    'server_id' => $server->id,
                    'host' => $server->host,
                    'uuid' => $uuid,
                    'error' => $e->getMessage(),
                ]);

                $results[] = [
                    'server_id' => $server->id,
                    'host' => $server->host,
                    'success' => false,
                    'action' => 'remove_client',
                    'output' => null,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return $this->buildSummary($results);
    }

    public function getUserTrafficByEmail(string $email): array
    {
        $totalUpload = 0;
        $totalDownload = 0;
        $perServer = [];
        $servers = $this->getServers();

        foreach ($servers as $server) {
            try {
                $script = sprintf(
                    "sudo %s api statsquery --server=%s",
                    $server->xray_bin_path,
                    $server->xray_stats_server
                );

                $output = $this->runSshToHost($server, $script);

                $upload = 0;
                $download = 0;

                $decoded = json_decode($output, true);

                if (is_array($decoded) && isset($decoded['stat']) && is_array($decoded['stat'])) {
                    foreach ($decoded['stat'] as $item) {
                        $name = $item['name'] ?? '';
                        $value = (int) ($item['value'] ?? 0);

                        if ($name === "user>>>{$email}>>>traffic>>>uplink") {
                            $upload = $value;
                        }

                        if ($name === "user>>>{$email}>>>traffic>>>downlink") {
                            $download = $value;
                        }
                    }
                }

                $totalUpload += $upload;
                $totalDownload += $download;

                $perServer[] = [
                    'server_id' => $server->id,
                    'host' => $server->host,
                    'success' => true,
                    'upload_bytes' => $upload,
                    'download_bytes' => $download,
                ];
            } catch (\Throwable $e) {
                Log::error('getUserTrafficByEmail failed on server', [
                    'server_id' => $server->id,
                    'host' => $server->host,
                    'email' => $email,
                    'error' => $e->getMessage(),
                ]);

                $perServer[] = [
                    'server_id' => $server->id,
                    'host' => $server->host,
                    'success' => false,
                    'upload_bytes' => 0,
                    'download_bytes' => 0,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return [
            'upload_bytes' => $totalUpload,
            'download_bytes' => $totalDownload,
            'servers' => $perServer,
        ];
    }

    /**
     * Fetch traffic for many Xray users with one stats query per server.
     *
     * @param array<int, string> $emails
     * @return array<string, array{upload_bytes: int, download_bytes: int, servers: array<int, array<string, mixed>>}>
     */
    public function getTrafficForEmails(array $emails): array
    {
        $emails = array_values(array_unique(array_filter($emails)));

        if (empty($emails)) {
            return [];
        }

        $traffic = [];
        $emailLookup = array_fill_keys($emails, true);

        foreach ($emails as $email) {
            $traffic[$email] = [
                'upload_bytes' => 0,
                'download_bytes' => 0,
                'servers' => [],
            ];
        }

        $servers = $this->getServers();

        foreach ($servers as $server) {
            try {
                $script = sprintf(
                    "sudo %s api statsquery --server=%s",
                    $server->xray_bin_path,
                    $server->xray_stats_server
                );

                $output = $this->runSshToHost($server, $script);
                $decoded = json_decode($output, true);
                $serverTraffic = [];

                if (is_array($decoded) && isset($decoded['stat']) && is_array($decoded['stat'])) {
                    foreach ($decoded['stat'] as $item) {
                        $name = $item['name'] ?? '';
                        $value = (int) ($item['value'] ?? 0);

                        if (!preg_match('/^user>>>(.+)>>>traffic>>>(uplink|downlink)$/', $name, $matches)) {
                            continue;
                        }

                        $email = $matches[1];
                        $direction = $matches[2];

                        if (!isset($emailLookup[$email])) {
                            continue;
                        }

                        $serverTraffic[$email] ??= [
                            'upload_bytes' => 0,
                            'download_bytes' => 0,
                        ];

                        if ($direction === 'uplink') {
                            $serverTraffic[$email]['upload_bytes'] = $value;
                            $traffic[$email]['upload_bytes'] += $value;
                        } else {
                            $serverTraffic[$email]['download_bytes'] = $value;
                            $traffic[$email]['download_bytes'] += $value;
                        }
                    }
                }

                foreach ($emails as $email) {
                    $traffic[$email]['servers'][] = [
                        'server_id' => $server->id,
                        'host' => $server->host,
                        'success' => true,
                        'upload_bytes' => $serverTraffic[$email]['upload_bytes'] ?? 0,
                        'download_bytes' => $serverTraffic[$email]['download_bytes'] ?? 0,
                    ];
                }
            } catch (\Throwable $e) {
                Log::error('getTrafficForEmails failed on server', [
                    'server_id' => $server->id,
                    'host' => $server->host,
                    'email_count' => count($emails),
                    'error' => $e->getMessage(),
                ]);

                foreach ($emails as $email) {
                    $traffic[$email]['servers'][] = [
                        'server_id' => $server->id,
                        'host' => $server->host,
                        'success' => false,
                        'upload_bytes' => 0,
                        'download_bytes' => 0,
                        'message' => $e->getMessage(),
                    ];
                }
            }
        }

        return $traffic;
    }

    /**
     * Get VPN servers with caching.
     * Cache is invalidated when servers are updated.
     */
    public function getServers(bool $fresh = false): Collection
    {
        if ($fresh || $this->serversCache === null) {
            $cacheKey = 'vpn_servers:active';

            $this->serversCache = Cache::remember($cacheKey, 300, function () {
                return $this->fetchServers();
            });
        }

        return $this->serversCache;
    }

    /**
     * Fetch servers from database.
     */
    private function fetchServers(): Collection
    {
        $query = VpnServer::query()
            ->whereNotNull('host')
            ->where('host', '!=', '');

        if ($this->hasIsActiveColumn) {
            $query->where('is_active', 1);
        }

        $servers = $query->orderBy('id')->get();

        if ($servers->isEmpty()) {
            throw new \RuntimeException('No VPN servers found in vpn_servers.host');
        }

        return $servers;
    }

    /**
     * Clear the servers cache.
     */
    public function clearServersCache(): void
    {
        $this->serversCache = null;
        Cache::forget('vpn_servers:active');
    }

    /**
     * Build summary from results array.
     */
    private function buildSummary(array $results): array
    {
        $total = count($results);
        $successCount = collect($results)->where('success', true)->count();
        $failedCount = $total - $successCount;

        return [
            'success' => $failedCount === 0,
            'total_servers' => $total,
            'success_count' => $successCount,
            'failed_count' => $failedCount,
            'results' => $results,
        ];
    }

    /**
     * Execute SSH command on a server.
     */
    private function runSshToHost(VpnServer $server, string $script): string
    {
        if (empty($server->ssh_key)) {
            throw new \RuntimeException('XRAY_ssh_Key is empty');
        }

        $sshTarget = $server->ssh_user . '@' . $server->host;
        $isWindows = PHP_OS_FAMILY === 'Windows';

        if ($isWindows) {
            return $this->runSshWindows($server->ssh_bin, $server->ssh_key, $sshTarget, $script, $server->host);
        }

        return $this->runSshProcess($server, $sshTarget, $script);
    }

    /**
     * Run SSH command on Windows using exec().
     */
    private function runSshWindows(string $sshBin, string $sshKey, string $sshTarget, string $script, string $host): string
    {
        $sshCommand = sprintf(
            '"%s" -i "%s" -o BatchMode=yes -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null %s "%s"',
            $sshBin,
            $sshKey,
            $sshTarget,
            str_replace('"', '\"', $script)
        );

        $output = [];
        $exitCode = 0;

        exec($sshCommand . ' 2>&1', $output, $exitCode);

        $stdout = implode("\n", $output);

        if ($exitCode !== 0) {
            throw new \RuntimeException(
                trim($stdout) ?: "SSH command failed on host {$host}"
            );
        }

        return $stdout;
    }

    /**
     * Run SSH command on Unix-like systems using Symfony Process.
     */
    private function runSshProcess(VpnServer $server, string $sshTarget, string $script): string
    {
        $args = [
            $server->ssh_bin,
            '-i',
            $server->ssh_key,
            '-o',
            'BatchMode=yes',
            '-o',
            'StrictHostKeyChecking=no',
            '-o',
            'UserKnownHostsFile=/dev/null',
            $sshTarget,
            $script,
        ];
    
        $process = new Process($args);
        $process->setTimeout($server->ssh_timeout ?? 30);
        $process->run();
    
        if (!$process->isSuccessful()) {
            throw new \RuntimeException(
                trim($process->getErrorOutput())
                    ?: trim($process->getOutput())
                    ?: "SSH command failed on host {$server->host}"
            );
        }
    
        return $process->getOutput();
    }

    /**
     * Escape a string for POSIX shell.
     */
    private function shellQuotePosix(string $value): string
    {
        return "'" . str_replace("'", "'\"'\"'", $value) . "'";
    }
}
