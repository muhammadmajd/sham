<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VpnServer;
use App\Services\AuditService;
use Illuminate\Http\Request;

class VpnServerController extends Controller
{
    //
    public function index()
    {
        AuditService::log('servers.get', 'Server', [
            'from' => false,
            'to' => true,
        ]);
        return response()->json(VpnServer::orderBy('id', 'desc')->get());
    }
    public function store(Request $request)
    {
        AuditService::log('servers.store', 'Server', [
            'from' => false,
            'to' => true,
        ]);
       $data = $request->validate([
            'name' => 'required|string',
            'host' => 'required|string',
            'port' => 'required|integer',
            'type' => 'required|in:tcp,udp',
            'cert_domain' => 'required|string',
            'fingerprint' => 'required|string',
            'public_key' => 'required|string',
            'short_id' => 'required|string',
            'notes' => 'nullable|string',
            'country' => 'required|string',
            'flag' => 'required|string',
            'server_Payment_type' => 'required|in:free,paid',
            'public' => 'required|boolean',
            'traffic_used' => 'required|integer',
            'traffic_limit' => 'required|integer',
            'available' => 'required|boolean',
            'sni' => 'required|string',
            'uuid' => 'nullable|string',
            'ptype' => 'required|in:vless,vmess,custom,shadowsocks,socks,trojan,wireguard,hysteria2,hysteria,http,policygroup',
            'security' => 'required|in:reality,auto,tls,xtls,none',
            'flow' => 'required|in:xtls-rprx-vision,none',
            'encryption' => 'required|in:AES-256-GCM,ChaCha20-Poly1305,none',
            'ssh_bin' => 'required|string',
            'ssh_user' => 'required|string',
            'ssh_key' => 'required|string',
            'ssh_config_path' => 'required|string',
            'xray_bin_path' => 'required|string',
            'xray_stats_server' => 'required|string',
            'ssh_vless_flow' => 'required|string',
            'ssh_timeout' => 'required|string',
        ]);
        $server = VpnServer::create($data);
        return response()->json($server, 201);
    }

    public function update(Request $request, $id)
    {
        AuditService::log('servers.update', 'Server', [
            'from' => false,
            'to' => true,
        ]);
       $data = $request->validate([
            'name' => 'required|string',
            'host' => 'required|string',
            'port' => 'required|integer',
            'type' => 'required|in:tcp,udp',
            'cert_domain' => 'required|string',
            'fingerprint' => 'required|string',
            'public_key' => 'required|string',
            'short_id' => 'required|string',
            'notes' => 'nullable|string',
            'country' => 'required|string',
            'flag' => 'required|string',
            'server_Payment_type' => 'required|in:free,paid',
            'public' => 'required|boolean',
            'traffic_used' => 'required|integer',
            'traffic_limit' => 'required|integer',
            'available' => 'required|boolean',
            'sni' => 'required|string',
            'uuid' => 'required|string',
            'ptype' => 'required|in:vless,vmess,custom,shadowsocks,socks,trojan,wireguard,hysteria2,hysteria,http,policygroup',
            'security' => 'required|in:reality,auto,tls,xtls,none',
            'flow' => 'required|in:xtls-rprx-vision,none',
            'encryption' => 'required|in:AES-256-GCM,ChaCha20-Poly1305,none',
            'ssh_bin' => 'required|string',
            'ssh_user' => 'required|string',
            'ssh_key' => 'required|string',
            'ssh_config_path' => 'required|string',
            'xray_bin_path' => 'required|string',
            'xray_stats_server' => 'required|string',
            'ssh_vless_flow' => 'required|string',
            'ssh_timeout' => 'required|string',
        ]);
        $server = VpnServer::findOrFail($id);
        if($server == null){
            return response()->json(['Message', 'Not Found'], 401);
        }
        $server->update($data);
        return response()->json($server, 201);
    }

    public function show($id)
    {
        AuditService::log('servers.show', 'Server', [
            'from' => false,
            'to' => true,
        ]);
        return response()->json(VpnServer::findOrFail($id));
    }

    public function destroy($id)
    {
        AuditService::log('servers.destroy', 'Server', [
            'from' => false,
            'to' => true,
        ]);
        $server = VpnServer::find($id);
        $server->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
