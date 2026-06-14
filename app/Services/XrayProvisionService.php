<?php

namespace App\Services;

use phpseclib3\Net\SSH2;
use phpseclib3\Crypt\PublicKeyLoader;
use Psr\Log\LoggerInterface;

class XrayProvisionService
{
    private string $host;
    private int $port;
    private string $user;
    private string $privateKeyPath;
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        // You can inject config via env() or config/xray.php
        $this->host = env('XRAY_VPS_HOST', 'your.vps.ip');
        $this->port = (int) env('XRAY_VPS_SSH_PORT', 22);
        $this->user = env('XRAY_VPS_USER', 'root');
        //$this->privateKeyPath = env('XRAY_VPS_KEY_PATH', '/root/.ssh/id_rsa');
        $this->privateKeyPath = env('XRAY_VPS_KEY_PATH', '/home/deploy/.ssh/id_rsa');
        $this->logger = $logger;
    }

    /**
     * Adds a client UUID on the remote Xray server by running the add-v2ray-client.sh script.
     *
     * @param string $uuid
     * @param string $comment
     * @return array ['ok' => bool, 'output' => string]
     */
    public function addClient(string $uuid, string $comment = 'added-by-api'): array
    {
        // Basic validation of UUID format (very loose)
        if (! preg_match('/^[0-9a-fA-F-]{36,}$/', $uuid)) {
            $this->logger->warning("addClient called with invalid uuid format", ['uuid' => $uuid]);
            return ['ok' => false, 'output' => 'Invalid UUID format'];
        }

        // load private key
        if (! file_exists($this->privateKeyPath)) {
            $this->logger->error("Private key not found for Xray provisioning", ['path' => $this->privateKeyPath]);
            return ['ok' => false, 'output' => 'SSH key not found'];
        }

        $ssh = new SSH2($this->host, $this->port);

        try {
            $keyContents = file_get_contents($this->privateKeyPath);
            $key = PublicKeyLoader::load($keyContents);

            if (! $ssh->login($this->user, $key)) {
                $this->logger->error("SSH login failed to {$this->host} as {$this->user}");
                return ['ok' => false, 'output' => 'SSH login failed'];
            }

            // remote script path (ensure this exists and is executable on VPS)
            $remoteScript = '/usr/local/bin/add-v2ray-client.sh';
            $cmd = escapeshellcmd($remoteScript) . ' ' . escapeshellarg($uuid) . ' ' . escapeshellarg($comment);

            $this->logger->info("Running remote provisioning command: {$cmd} on {$this->host}");

            $output = $ssh->exec($cmd);
            $exitStatus = $ssh->getExitStatus(); // may be null for some servers

            // If exit is null, assume success when output contains positive message - but we prefer exit === 0
            $ok = ($exitStatus === 0) || ($exitStatus === null && strpos($output, 'Added') !== false);

            $this->logger->info("Provision result", ['ok' => $ok, 'exit' => $exitStatus, 'output' => $output]);

            return ['ok' => $ok, 'output' => trim($output ?? '')];
        } catch (\Throwable $e) {
            $this->logger->error("Provision exception: " . $e->getMessage());
            return ['ok' => false, 'output' => $e->getMessage()];
        }
    }
}
