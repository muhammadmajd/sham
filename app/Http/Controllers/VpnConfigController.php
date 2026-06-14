<?php

namespace App\Http\Controllers;

use App\Http\Services\XrayConfigService as ServicesXrayConfigService;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\VpnServer;
use App\Services\AuditService;

class VpnConfigController extends Controller
{
    // public list of servers (or you can require auth)
    public function servers()
    {
        $servers = VpnServer::all();

        AuditService::log('servers.get', 'Server', [
            'from' => false,
            'to' => true,
        ]);

        return response()->json($servers);
    }

    // return a minimal V2Ray/VLESS style config for the authenticated user
    public function getConfig(Request $request)
    {
        $user = $request->user();
        $server = VpnServer::firstOrFail();
        AuditService::log('servers.getConfig', 'Server', [
            'from' => false,
            'to' => true,
        ]);

        $config = [
            'v' => '2',
            'ps' => 'M-VPN-' . $server->name,
            'add' => $server->host,
            'port' => (string) $server->port,
            'id' => $user->uuid ?? (string) $user->id,
            'aid' => '0',
            'net' => 'tcp',
            'type' => 'none',
            'host' => '',
            'path' => '/',
            'tls' => $server->cert_domain ? 'tls' : 'none',
        ];

        return response()->json($config);
    }

    /**
     * Return a full Xray client config for the authenticated user.
     * Route protected by auth.jwt middleware.
     */
    public function clientFullConfig(Request $request, ServicesXrayConfigService $svc)
    {
        // $request->user_id is set by AuthJwtMiddleware
        $user = User::find($request->user_id);
        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // optionally allow client to choose server id via ?server_id=#
        $server = null;
        if ($request->has('server_id')) {
            $server = VpnServer::find($request->get('server_id'));
        }
        else{
            $server = VpnServer::firstOrFail();
        }
        AuditService::log('servers.clientFullConfig', 'Server', [
            'from' => false,
            'to' => true,
        ]);

        // build config using service
        $config = $svc->buildClientConfig($user, $server);

        // Return as JSON for the client to download or start local v2ray_dart with it
        return response()->json($config);
    }
}
