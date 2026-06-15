<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = ['key', 'name', 'price_cents', 'currency', 'interval', 'stripe_price_id', 'traffic_limit', 'devices_number'];

    protected $table = "plans";

    protected $casts = [
        'devices_number' => 'integer',
        'traffic_limit' => 'integer',
    ];

    public function users()
    {
        return $this->HasMany(User::class);
    }

    public function userSubscriptions()
    {
        return $this->hasMany(UserSubscription::class);
    }
}
