@php
    $s = $server;
@endphp

<div class="space-y-8">

    <div>
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Info</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input type="text" name="name" value="{{ old('name', $s->name ?? '') }}"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Host</label>
                <input type="text" name="host" value="{{ old('host', $s->host ?? '') }}"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Port</label>
                <input type="number" name="port" value="{{ old('port', $s->port ?? '') }}"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                <select name="type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    <option value="tcp" {{ old('type', $s->type ?? '') === 'tcp' ? 'selected' : '' }}>tcp</option>
                    <option value="udp" {{ old('type', $s->type ?? '') === 'udp' ? 'selected' : '' }}>udp</option>
                </select>
            </div>
        </div>
    </div>

    <div>
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Location & Network</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                <input type="text" name="country" value="{{ old('country', $s->country ?? '') }}"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Flag</label>
                <input type="text" name="flag" value="{{ old('flag', $s->flag ?? '') }}"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cert Domain</label>
                <input type="text" name="cert_domain" value="{{ old('cert_domain', $s->cert_domain ?? '') }}"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">SNI</label>
                <input type="text" name="sni" value="{{ old('sni', $s->sni ?? '') }}"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            </div>
        </div>
    </div>

    <div>
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Keys & IDs</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fingerprint</label>
                <input type="text" name="fingerprint" value="{{ old('fingerprint', $s->fingerprint ?? '') }}"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Public Key</label>
                <input type="text" name="public_key" value="{{ old('public_key', $s->public_key ?? '') }}"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Short ID</label>
                <input type="text" name="short_id" value="{{ old('short_id', $s->short_id ?? '') }}"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">UUID</label>
                <input type="text" name="uuid" value="{{ old('uuid', $s->uuid ?? '') }}"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        </div>
    </div>

    <div>
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Protocol Settings</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">PType</label>
                <select name="ptype" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    @foreach(['vless','vmess','custom','shadowsocks','socks','trojan','wireguard','hysteria2','hysteria','http','policygroup'] as $item)
                        <option value="{{ $item }}" {{ old('ptype', $s->ptype ?? '') === $item ? 'selected' : '' }}>{{ $item }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Security</label>
                <select name="security" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    @foreach(['reality','auto','tls','xtls','none'] as $item)
                        <option value="{{ $item }}" {{ old('security', $s->security ?? '') === $item ? 'selected' : '' }}>{{ $item }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Flow</label>
                <select name="flow" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    @foreach(['xtls-rprx-vision','none'] as $item)
                        <option value="{{ $item }}" {{ old('flow', $s->flow ?? '') === $item ? 'selected' : '' }}>{{ $item }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Encryption</label>
                <select name="encryption" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    @foreach(['AES-256-GCM','ChaCha20-Poly1305','none'] as $item)
                        <option value="{{ $item }}" {{ old('encryption', $s->encryption ?? '') === $item ? 'selected' : '' }}>{{ $item }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div>
        <h3 class="text-lg font-semibold text-gray-900 mb-4">State & Usage</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Server Payment Type</label>
                <select name="server_Payment_type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    <option value="free" {{ old('server_Payment_type', $s->server_Payment_type ?? '') === 'free' ? 'selected' : '' }}>free</option>
                    <option value="paid" {{ old('server_Payment_type', $s->server_Payment_type ?? '') === 'paid' ? 'selected' : '' }}>paid</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Public</label>
                <select name="public" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    <option value="1" {{ (string) old('public', $s->public ?? 1) === '1' ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ (string) old('public', $s->public ?? 1) === '0' ? 'selected' : '' }}>No</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Available</label>
                <select name="available" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    <option value="1" {{ (string) old('available', $s->available ?? 1) === '1' ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ (string) old('available', $s->available ?? 1) === '0' ? 'selected' : '' }}>No</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Traffic Used</label>
                <input type="number" name="traffic_used" value="{{ old('traffic_used', $s->traffic_used ?? 0) }}"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Traffic Limit</label>
                <input type="number" name="traffic_limit" value="{{ old('traffic_limit', $s->traffic_limit ?? 0) }}"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <input type="text" name="notes" value="{{ old('notes', $s->notes ?? '') }}"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        </div>
    </div>

    <div>
        <h3 class="text-lg font-semibold text-gray-900 mb-4">SSH / Xray</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">SSH Bin</label>
                <input type="text" name="ssh_bin" value="{{ old('ssh_bin', $s->ssh_bin ?? '') }}"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">SSH User</label>
                <input type="text" name="ssh_user" value="{{ old('ssh_user', $s->ssh_user ?? '') }}"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">SSH Key</label>
                <input type="text" name="ssh_key" value="{{ old('ssh_key', $s->ssh_key ?? '') }}"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">SSH Config Path</label>
                <input type="text" name="ssh_config_path" value="{{ old('ssh_config_path', $s->ssh_config_path ?? '') }}"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Xray Bin Path</label>
                <input type="text" name="xray_bin_path" value="{{ old('xray_bin_path', $s->xray_bin_path ?? '') }}"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Xray Stats Server</label>
                <input type="text" name="xray_stats_server" value="{{ old('xray_stats_server', $s->xray_stats_server ?? '') }}"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">SSH VLESS Flow</label>
                <input type="text" name="ssh_vless_flow" value="{{ old('ssh_vless_flow', $s->ssh_vless_flow ?? '') }}"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">SSH Timeout</label>
                <input type="number" name="ssh_timeout" value="{{ old('ssh_timeout', $s->ssh_timeout ?? 30) }}"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            </div>
        </div>
    </div>

</div>
