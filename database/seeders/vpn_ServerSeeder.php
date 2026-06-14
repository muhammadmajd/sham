<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\VpnServer;
use Illuminate\Support\Facades\Hash;

class vpn_ServerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $server1 = VpnServer::create(
            [
                'name' => 'United States',
                'host' => '144.172.101.253',
                'port' => 443,
                'type' => 'tcp',
                'cert_domain' => '144.172.101.253',
                'fingerprint' => 'chrome',
                'public_key' => '1qiKk-LNM8cgdg2IqyqpMfBNkwfTUkwEFqQp_a4QXBA&sid',
                'short_id' => 'a1b2c3d4',
                'notes' => 'United States server for vpn',
                'country' => 'United States',
                'flag' => '🇺🇸',
                'traffic_used' => 0,
                'traffic_limit' => 10737418240,
                'server_Payment_type' => 'paid',
                'public' => 1,
                'available' => 1,
                'uuid' => '7d6b2066-a129-4175-b245-fe2fd125d931',
                'sni' => 'www.cloudflare.com',
                'ptype' => 'vless',
                'security' => 'reality',
                'flow'=> 'xtls-rprx-vision',
                'encryption' => 'none'
            ]
        );
    }
}
