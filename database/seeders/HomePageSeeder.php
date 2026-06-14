<?php

namespace Database\Seeders;

use App\Models\AppVersion;
use App\Models\HomePageContent;
use Illuminate\Database\Seeder;

class HomePageSeeder extends Seeder
{
    public function run(): void
    {
        HomePageContent::current();

        foreach (AppVersion::PLATFORMS as $platform => $label) {
            AppVersion::firstOrCreate(
                ['platform' => $platform],
                [
                    'version' => '1.0.0',
                    'status' => 'active',
                    'is_visible' => true,
                    'notes_en' => "{$label} app release.",
                ]
            );
        }
    }
}
