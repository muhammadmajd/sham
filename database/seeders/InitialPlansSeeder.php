<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class InitialPlansSeeder extends Seeder
{
    public function run()
    {
        /*Plan::updateOrCreate(['key'=>'free'], [
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

        // Create admin user (only for dev)
        User::updateOrCreate(['email'=>'admin@admin.com'], [
            'name'=>'Admin',
            'is_admin'=>true,
            'plan_id'=> Plan::where('key','pro_monthly')->first()->id ?? null,
        ]);*/
    }
}
