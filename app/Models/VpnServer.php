<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VpnServer extends Model
{
    //
    protected $fillable = [
        'name',
        'host',
        'port',
        'type',
        'cert_domain',
        'fingerprint',
        'public_key',
        'short_id',
        'country',
        'flag',
        'notes',
        'traffic_used',
        'traffic_limit',
        'server_Payment_type',
        'public',
        'available',
        'sni',
        'uuid',
        'ptype',
        'security',
        'flow',
        'encryption',
        'ssh_bin',
        'ssh_user',
        'ssh_key',
        'ssh_config_path',
        'xray_bin_path',
        'xray_stats_server',
        'ssh_vless_flow',
        'ssh_timeout',
    ];

    protected $casts = [
        'ssh_timeout' => 'integer',
        'public' => 'boolean',
        'available' => 'boolean',
    ];
}
