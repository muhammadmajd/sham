<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = ['key', 'name', 'price_cents', 'currency', 'interval', 'stripe_price_id', 'traffic_limit'];

    protected $table = "plans";

    public function users()
    {
        return $this->HasMany(User::class);
    }

    public function userSubscriptions()
    {
        return $this->hasMany(UserSubscription::class);
    }
}
