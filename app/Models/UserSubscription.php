<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'plan_id',
        'subscription',
        'payment_type',
        'started_at',
        'ends_at',
        'price_cents',
        'currency',
        'traffic_limit',
        'stripe_subscription_id',
        'stripe_price_id',
        'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ends_at' => 'datetime',
        'price_cents' => 'integer',
        'traffic_limit' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}
