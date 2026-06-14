<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         Plan::updateOrCreate(['key'=>'free'], [
            'name'=>'Free',
            'price_cents'=>0,
            'currency'=>'usd',
            'interval'=>null,
            'stripe_price_id'=>null,
            'traffic_limit'=> 5 * 1024 * 1024 * 1024, // 5GB
        ]);

        Plan::updateOrCreate(['key'=>'pro_monthly'], [
            'name'=>'Pro Monthly',
            'price_cents'=>999, // $9.99
            'currency'=>'usd',
            'interval'=>'month',
            'stripe_price_id'=> env('STRIPE_PRICE_ID_MONTHLY'), // set in .env
            'traffic_limit'=> null, // unlimited
        ]);
    }
}
