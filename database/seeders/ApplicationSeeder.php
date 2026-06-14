<?php

namespace Database\Seeders;

use App\Models\Application;
use Illuminate\Database\Seeder;

class ApplicationSeeder extends Seeder
{
    public function run(): void
    {
        $applications = [
            ['name' => 'Facebook', 'app_id' => 'com.facebook.katana'],
            ['name' => 'Facebook Lite', 'app_id' => 'com.facebook.lite'],
            ['name' => 'Instagram', 'app_id' => 'com.instagram.android'],
            ['name' => 'WhatsApp', 'app_id' => 'com.whatsapp'],
            ['name' => 'WhatsApp Business', 'app_id' => 'com.whatsapp.w4b'],
            ['name' => 'Messenger', 'app_id' => 'com.facebook.orca'],
            ['name' => 'X', 'app_id' => 'com.twitter.android'],
            ['name' => 'TikTok', 'app_id' => 'com.zhiliaoapp.musically'],
            ['name' => 'TikTok Lite', 'app_id' => 'com.zhiliaoapp.musically.go'],
            ['name' => 'Snapchat', 'app_id' => 'com.snapchat.android'],
            ['name' => 'Telegram', 'app_id' => 'org.telegram.messenger'],
            ['name' => 'Telegram X', 'app_id' => 'org.thunderdog.challegram'],
            ['name' => 'YouTube', 'app_id' => 'com.google.android.youtube'],
            ['name' => 'YouTube Music', 'app_id' => 'com.google.android.apps.youtube.music'],
            ['name' => 'LinkedIn', 'app_id' => 'com.linkedin.android'],
            ['name' => 'Pinterest', 'app_id' => 'com.pinterest'],
            ['name' => 'Reddit', 'app_id' => 'com.reddit.frontpage'],
            ['name' => 'Discord', 'app_id' => 'com.discord'],
            ['name' => 'Twitch', 'app_id' => 'tv.twitch.android.app'],
            ['name' => 'Threads', 'app_id' => 'com.instagram.barcelona'],
            ['name' => 'WeChat', 'app_id' => 'com.tencent.mm'],
            ['name' => 'LINE', 'app_id' => 'jp.naver.line.android'],
            ['name' => 'Viber', 'app_id' => 'com.viber.voip'],
            ['name' => 'Signal', 'app_id' => 'org.thoughtcrime.securesms'],
            ['name' => 'Skype', 'app_id' => 'com.skype.raider'],
            ['name' => 'Tumblr', 'app_id' => 'com.tumblr'],
            ['name' => 'Mastodon', 'app_id' => 'org.joinmastodon.android'],
            ['name' => 'Bluesky', 'app_id' => 'xyz.blueskyweb.app'],
            ['name' => 'VK', 'app_id' => 'com.vkontakte.android'],
            ['name' => 'OK', 'app_id' => 'ru.ok.android'],
            ['name' => 'BeReal', 'app_id' => 'com.bereal.ft'],
            ['name' => 'Clubhouse', 'app_id' => 'com.clubhouse.app'],
            ['name' => 'Likee', 'app_id' => 'video.like'],
            ['name' => 'Kwai', 'app_id' => 'com.kwai.video'],
            ['name' => 'ShareChat', 'app_id' => 'in.mohalla.sharechat'],
        ];

        foreach ($applications as $application) {
            Application::updateOrCreate(
                ['app_id' => $application['app_id']],
                [
                    'name' => $application['name'],
                    'type' => 'social_media',
                    'status' => 'active',
                ]
            );
        }
    }
}
