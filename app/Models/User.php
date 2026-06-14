<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'uuid',
        'code',
        'name',
        'email',
        'password',
        'verification_token',
        'email_verified_at',
        'active',
        'is_admin',
        'traffic_used',
        'traffic_limit',
        'subscription',
        'plan_id',
        'stripe_customer_id',
        'stripe_subscription_id',
        'subscription_started_at',
        'subscription_ends_at',
        'subscription_renewed_at',
        'subscription_canceled_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $dates = ['email_verified_at'];

    protected $casts = [
        'active' => 'boolean',
        'is_admin' => 'boolean',
        'traffic_used' => 'integer',
        'traffic_limit' => 'integer',
        'subscription_started_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
        'subscription_renewed_at' => 'datetime',
        'subscription_canceled_at' => 'datetime',
        'email_verified_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->uuid)) {
                $user->uuid = (string) Str::uuid();
            }
        });

        static::retrieved(function (User $user) {
            if ($user->relationLoaded('roles')) {
                return;
            }
            $user->loadMissing('roles');
        });
    }

    public function refreshTokens(): HasMany
    {
        return $this->hasMany(RefreshToken::class);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function hasRole(string|array $roles): bool
    {
        if (is_string($roles)) {
            return $this->roles()->where('name', $roles)->exists();
        }

        return $this->roles()->whereIn('name', $roles)->exists();
    }

    public function hasAnyRole(array $roles): bool
    {
        return $this->roles()->whereIn('name', $roles)->exists();
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function subscriptionHistory(): HasMany
    {
        return $this->hasMany(UserSubscription::class)->orderByDesc('started_at');
    }

    public function activeSubscription(): ?UserSubscription
    {
        return $this->subscriptionHistory()
            ->where('status', 'active')
            ->first();
    }

    public function hasActiveSubscription(): bool
    {
        if (empty($this->stripe_subscription_id) && empty($this->subscription)) {
            return false;
        }

        if (!empty($this->subscription_ends_at)) {
            return $this->subscription_ends_at->isFuture();
        }

        return true;
    }

    public function getTotalDeviceTraffic(): array
    {
        $totals = $this->devices()
            ->selectRaw('
                COUNT(*) as devices_count,
                COALESCE(SUM(download_bytes), 0) as download_bytes,
                COALESCE(SUM(upload_bytes), 0) as upload_bytes
            ')
            ->first();

        return [
            'devices_count' => (int) ($totals->devices_count ?? 0),
            'download_bytes' => (int) ($totals->download_bytes ?? 0),
            'upload_bytes' => (int) ($totals->upload_bytes ?? 0),
        ];
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('uuid', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhere('subscription', 'like', "%{$search}%");
        });
    }

    public function scopeWithDeviceStats(Builder $query): Builder
    {
        return $query->addSelect([
            'devices_count' => Device::query()
                ->selectRaw('COUNT(*)')
                ->whereColumn('devices.user_id', 'users.id'),
            'download_bytes' => Device::query()
                ->selectRaw('COALESCE(SUM(download_bytes), 0)')
                ->whereColumn('devices.user_id', 'users.id'),
            'upload_bytes' => Device::query()
                ->selectRaw('COALESCE(SUM(upload_bytes), 0)')
                ->whereColumn('devices.user_id', 'users.id'),
        ]);
    }
}
