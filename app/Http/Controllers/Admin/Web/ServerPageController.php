<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Models\VpnServer;
use App\Services\AuditService;
use Illuminate\Http\Request;

class ServerPageController extends Controller
{
    public function index()
    {
        AuditService::log('servers.index', 'VpnServer', [
            'by' => 'admin',
        ]);
        $servers = VpnServer::orderByDesc('id')->paginate(15);

        return view('admin.servers.index', compact('servers'));
    }

    public function create()
    {
        return view('admin.servers.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateServer($request);
        VpnServer::create($data);

        AuditService::log('servers.store', 'VpnServer', [
            'by' => 'admin',
            'name' => $data['name'],
            'host' => $data['host'],
        ]);

        return redirect()->route('admin.servers.index')->with('success', 'Server created.');
    }

    public function edit(VpnServer $server)
    {
        return view('admin.servers.edit', compact('server'));
    }

    public function update(Request $request, VpnServer $server)
    {
        $data = $this->validateServer($request, false);
        $server->update($data);

        AuditService::log('servers.update', 'VpnServer', [
            'by' => 'admin',
            'name' => $data['name'],
            'host' => $data['host'],
        ]);

        return redirect()->route('admin.servers.index')->with('success', 'Server updated.');
    }

    public function destroy(VpnServer $server)
    {
        $server->delete();
        AuditService::log('servers.destroy', 'VpnServer', [
            'by' => 'admin',
            'name' => $server['name'],
            'host' => $server['host'],
        ]);

        return redirect()->route('admin.servers.index')->with('success', 'Server deleted.');
    }

    private function validateServer(Request $request, bool $isCreate = true): array
    {
        return $request->validate([
            'name' => ['required', 'string'],
            'host' => ['required', 'string'],
            'port' => ['required', 'integer'],
            'type' => ['required', 'in:tcp,udp'],
            'cert_domain' => ['required', 'string'],
            'fingerprint' => ['required', 'string'],
            'public_key' => ['required', 'string'],
            'short_id' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
            'country' => ['required', 'string'],
            'flag' => ['required', 'string'],
            'server_Payment_type' => ['required', 'in:free,paid'],
            'public' => ['required', 'boolean'],
            'traffic_used' => ['required', 'integer'],
            'traffic_limit' => ['required', 'integer'],
            'available' => ['required', 'boolean'],
            'sni' => ['required', 'string'],
            'uuid' => [$isCreate ? 'nullable' : 'required', 'string'],
            'ptype' => ['required', 'in:vless,vmess,custom,shadowsocks,socks,trojan,wireguard,hysteria2,hysteria,http,policygroup'],
            'security' => ['required', 'in:reality,auto,tls,xtls,none'],
            'flow' => ['required', 'in:xtls-rprx-vision,none'],
            'encryption' => ['required', 'in:AES-256-GCM,ChaCha20-Poly1305,none'],
            'ssh_bin' => ['required', 'string'],
            'ssh_user' => ['required', 'string'],
            'ssh_key' => ['required', 'string'],
            'ssh_config_path' => ['required', 'string'],
            'xray_bin_path' => ['required', 'string'],
            'xray_stats_server' => ['required', 'string'],
            'ssh_vless_flow' => ['required', 'string'],
            'ssh_timeout' => ['required', 'integer'],
        ]);
    }
}
