<?php
namespace App\Http\Services;

use App\Models\User;
use App\Models\VpnServer;

//This service will create a full Xray client/server config (JSON) for the Flutter client
class XrayConfigService{
    /**
     * Build a minimal Xray client config for the given user and server.
     *
     * $server can be a VpnServer model or an array with host/port/protocol/tls_domain.
     * or host/port/type/cert_domain
     */
    public function buildClientConfig(User $user, VpnServer $vpnServer): array
    {
        if (! $vpnServer) {
            $server = VpnServer::firstOrFail();
        }

        // Example: build a VLESS over TCP + TLS client config (Xray-style)
        $clientId = $user->uuid ?? (string) $user->id;

        $config = [
            "log" => [
                "access" => "",
                "error" => "",
                "loglevel" => "warning"
            ],
            "inbounds" => [
                [
                    "port" => 1080,
                    "listen" => "127.0.0.1",
                    "protocol" => "socks",
                    "settings" => [
                        "auth" => "noauth",
                        "udp" => true,
                        "ip" => "127.0.0.1"
                    ],
                    "sniffing" => [
                        "enabled" => true,
                        "destOverride" => ["http", "tls"]
                    ]
                ]
            ],
            "outbounds" => [
                [
                    "protocol" => strtolower($server->type ?? 'vless'),
                    "settings" => $this->outboundSettings($server, $clientId),
                    "streamSettings" => $this->streamSettings($server),
                    "tag" => "proxy"
                ],
                [
                    "protocol" => "freedom",
                    "settings" => new \stdClass(),
                    "tag" => "direct"
                ]
            ],
            "routing" => [
                "domainStrategy" => "AsIs",
                "rules" => []
            ]
        ];

        return $config;
    }

    private function outboundSettings(VpnServer $server, string $clientId)
    {
        $protocol = strtolower($server->type ?? 'vless');

        if ($protocol === 'vless') {
            return [
                "vnext" => [
                    [
                        "address" => $server->host,
                        "port" => (int) $server->port,
                        "users" => [
                            [
                                "id" => $clientId,
                                "encryption" => "none"
                            ]
                        ]
                    ]
                ]
            ];
        }

        // Add vmess, trojan, etc as needed
        return new \stdClass();
    }

    private function streamSettings(VpnServer $server)
    {
        $tls = !empty($server->cert_domain);
        $network = $server->type === 'vless' ? 'tcp' : 'tcp';

        $s = [
            "network" => $network,
            "security" => $tls ? "tls" : "none"
        ];

        if ($tls) {
            $s["tlsSettings"] = [
                "serverName" => $server->cert_domain,
                "allowInsecure" => false
            ];
        }

        // for ws/grpc add more fields here

        return $s;
    }
}
