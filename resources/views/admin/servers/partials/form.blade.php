@php
    $s = $server;
@endphp

<div class="row">
    <div class="mb-3">
        <label>Name</label>
        <input type="text" name="name" value="{{ old('name', $s->name ?? '') }}" required>
    </div>
    <div class="mb-3">
        <label>Host</label>
        <input type="text" name="host" value="{{ old('host', $s->host ?? '') }}" required>
    </div>
</div>

<div class="row">
    <div class="mb-3">
        <label>Port</label>
        <input type="number" name="port" value="{{ old('port', $s->port ?? '') }}" required>
    </div>
    <div class="mb-3">
        <label>Type</label>
        <select name="type" required>
            <option value="tcp" {{ old('type', $s->type ?? '') === 'tcp' ? 'selected' : '' }}>tcp</option>
            <option value="udp" {{ old('type', $s->type ?? '') === 'udp' ? 'selected' : '' }}>udp</option>
        </select>
    </div>
</div>

<div class="row">
    <div class="mb-3">
        <label>Country</label>
        <input type="text" name="country" value="{{ old('country', $s->country ?? '') }}" required>
    </div>
    <div class="mb-3">
        <label>Flag</label>
        <input type="text" name="flag" value="{{ old('flag', $s->flag ?? '') }}" required>
    </div>
</div>

<div class="row">
    <div class="mb-3">
        <label>Cert Domain</label>
        <input type="text" name="cert_domain" value="{{ old('cert_domain', $s->cert_domain ?? '') }}" required>
    </div>
    <div class="mb-3">
        <label>Fingerprint</label>
        <input type="text" name="fingerprint" value="{{ old('fingerprint', $s->fingerprint ?? '') }}" required>
    </div>
</div>

<div class="row">
    <div class="mb-3">
        <label>Public Key</label>
        <input type="text" name="public_key" value="{{ old('public_key', $s->public_key ?? '') }}" required>
    </div>
    <div class="mb-3">
        <label>Short ID</label>
        <input type="text" name="short_id" value="{{ old('short_id', $s->short_id ?? '') }}" required>
    </div>
</div>

<div class="row">
    <div class="mb-3">
        <label>SNI</label>
        <input type="text" name="sni" value="{{ old('sni', $s->sni ?? '') }}" required>
    </div>
    <div class="mb-3">
        <label>UUID</label>
        <input type="text" name="uuid" value="{{ old('uuid', $s->uuid ?? '') }}">
    </div>
</div>

<div class="row">
    <div class="mb-3">
        <label>PType</label>
        <select name="ptype" required>
            @foreach(['vless','vmess','custom','shadowsocks','socks','trojan','wireguard','hysteria2','hysteria','http','policygroup'] as $item)
                <option value="{{ $item }}" {{ old('ptype', $s->ptype ?? '') === $item ? 'selected' : '' }}>{{ $item }}</option>
            @endforeach
        </select>
    </div>
    <div class="mb-3">
        <label>Security</label>
        <select name="security" required>
            @foreach(['reality','auto','tls','xtls','none'] as $item)
                <option value="{{ $item }}" {{ old('security', $s->security ?? '') === $item ? 'selected' : '' }}>{{ $item }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="row">
    <div class="mb-3">
        <label>Flow</label>
        <select name="flow" required>
            @foreach(['xtls-rprx-vision','none'] as $item)
                <option value="{{ $item }}" {{ old('flow', $s->flow ?? '') === $item ? 'selected' : '' }}>{{ $item }}</option>
            @endforeach
        </select>
    </div>
    <div class="mb-3">
        <label>Encryption</label>
        <select name="encryption" required>
            @foreach(['AES-256-GCM','ChaCha20-Poly1305','none'] as $item)
                <option value="{{ $item }}" {{ old('encryption', $s->encryption ?? '') === $item ? 'selected' : '' }}>{{ $item }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="row">
    <div class="mb-3">
        <label>Server Payment Type</label>
        <select name="server_Payment_type" required>
            <option value="free" {{ old('server_Payment_type', $s->server_Payment_type ?? '') === 'free' ? 'selected' : '' }}>free</option>
            <option value="paid" {{ old('server_Payment_type', $s->server_Payment_type ?? '') === 'paid' ? 'selected' : '' }}>paid</option>
        </select>
    </div>
    <div class="mb-3">
        <label>Public</label>
        <select name="public" required>
            <option value="1" {{ (string) old('public', $s->public ?? 1) === '1' ? 'selected' : '' }}>Yes</option>
            <option value="0" {{ (string) old('public', $s->public ?? 1) === '0' ? 'selected' : '' }}>No</option>
        </select>
    </div>
</div>

<div class="row">
    <div class="mb-3">
        <label>Available</label>
        <select name="available" required>
            <option value="1" {{ (string) old('available', $s->available ?? 1) === '1' ? 'selected' : '' }}>Yes</option>
            <option value="0" {{ (string) old('available', $s->available ?? 1) === '0' ? 'selected' : '' }}>No</option>
        </select>
    </div>
    <div class="mb-3">
        <label>Traffic Used</label>
        <input type="number" name="traffic_used" value="{{ old('traffic_used', $s->traffic_used ?? 0) }}" required>
    </div>
</div>

<div class="row">
    <div class="mb-3">
        <label>Traffic Limit</label>
        <input type="number" name="traffic_limit" value="{{ old('traffic_limit', $s->traffic_limit ?? 0) }}" required>
    </div>
    <div class="mb-3">
        <label>Notes</label>
        <input type="text" name="notes" value="{{ old('notes', $s->notes ?? '') }}">
    </div>
</div>

<div class="row">
    <div class="mb-3">
        <label>SSH Bin</label>
        <input type="text" name="ssh_bin" value="{{ old('ssh_bin', $s->ssh_bin ?? '') }}" required>
    </div>
    <div class="mb-3">
        <label>SSH User</label>
        <input type="text" name="ssh_user" value="{{ old('ssh_user', $s->ssh_user ?? '') }}" required>
    </div>
</div>

<div class="row">
    <div class="mb-3">
        <label>SSH Key</label>
        <input type="text" name="ssh_key" value="{{ old('ssh_key', $s->ssh_key ?? '') }}" required>
    </div>
    <div class="mb-3">
        <label>SSH Config Path</label>
        <input type="text" name="ssh_config_path" value="{{ old('ssh_config_path', $s->ssh_config_path ?? '') }}" required>
    </div>
</div>

<div class="row">
    <div class="mb-3">
        <label>Xray Bin Path</label>
        <input type="text" name="xray_bin_path" value="{{ old('xray_bin_path', $s->xray_bin_path ?? '') }}" required>
    </div>
    <div class="mb-3">
        <label>Xray Stats Server</label>
        <input type="text" name="xray_stats_server" value="{{ old('xray_stats_server', $s->xray_stats_server ?? '') }}" required>
    </div>
</div>

<div class="row">
    <div class="mb-3">
        <label>SSH VLESS Flow</label>
        <input type="text" name="ssh_vless_flow" value="{{ old('ssh_vless_flow', $s->ssh_vless_flow ?? '') }}" required>
    </div>
    <div class="mb-3">
        <label>SSH Timeout</label>
        <input type="number" name="ssh_timeout" value="{{ old('ssh_timeout', $s->ssh_timeout ?? 30) }}" required>
    </div>
</div>
