@php
    $d = $device;
@endphp

<div class="row">
    <div class="mb-3">
        <label>Device UID</label>
        <input type="text" name="device_uid" value="{{ old('device_uid', $d->device_uid ?? '') }}" required>
    </div>

    <div class="mb-3">
        <label>User</label>
        <select name="user_id">
            <option value="">-- None --</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}" {{ (string) old('user_id', $d->user_id ?? '') === (string) $user->id ? 'selected' : '' }}>
                    {{ $user->name }} ({{ $user->email }})
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="row">
    <div class="mb-3">
        <label>Platform</label>
        <input type="text" name="platform" value="{{ old('platform', $d->platform ?? 'android') }}">
    </div>

    <div class="mb-3">
        <label>Xray Client UUID</label>
        <input type="text" name="xray_client_uuid" value="{{ old('xray_client_uuid', $d->xray_client_uuid ?? '') }}">
    </div>
</div>

<div class="row">
    <div class="mb-3">
        <label>Xray Email</label>
        <input type="text" name="xray_email" value="{{ old('xray_email', $d->xray_email ?? '') }}">
    </div>

    <div class="mb-3">
        <label>Last Seen At</label>
        <input type="datetime-local" name="last_seen_at" value="{{ old('last_seen_at', isset($d->last_seen_at) && $d->last_seen_at ? \Illuminate\Support\Carbon::parse($d->last_seen_at)->format('Y-m-d\TH:i') : '') }}">
    </div>
</div>

<div class="row">
    <div class="mb-3">
        <label>Download Bytes</label>
        <input type="number" name="download_bytes" value="{{ old('download_bytes', $d->download_bytes ?? 0) }}">
    </div>

    <div class="mb-3">
        <label>Upload Bytes</label>
        <input type="number" name="upload_bytes" value="{{ old('upload_bytes', $d->upload_bytes ?? 0) }}">
    </div>
</div>
