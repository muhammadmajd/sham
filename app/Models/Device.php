<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Device extends Model
{
    protected $fillable = [
        'device_uid',
        'platform',
        'user_id',
        'xray_client_uuid',
        'xray_email',
        'download_bytes',
        'upload_bytes',
        'speed_limit_bps',
        'traffic_limit_bytes',
        'last_seen_at',
    ];

    protected $casts = [
        'download_bytes' => 'integer',
        'upload_bytes' => 'integer',
        'speed_limit_bps' => 'integer',
        'traffic_limit_bytes' => 'integer',
        'last_seen_at' => 'datetime',
    ];

    /**
     * Get the user that owns the device.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to search devices by various fields.
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('device_uid', 'like', '%' . $search . '%')
                ->orWhere('platform', 'like', '%' . $search . '%')
                ->orWhere('xray_email', 'like', '%' . $search . '%')
                ->orWhere('xray_client_uuid', 'like', '%' . $search . '%')
                ->orWhereHas('user', function ($u) use ($search) {
                    $u->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
        });
    }

    /**
     * Scope to filter by user.
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by platform.
     */
    public function scopeForPlatform(Builder $query, string $platform): Builder
    {
        return $query->where('platform', $platform);
    }

    /**
     * Scope to get devices with recent activity.
     */
    public function scopeRecentlyActive(Builder $query, int $hours = 24): Builder
    {
        return $query->where('last_seen_at', '>=', now()->subHours($hours));
    }

    /**
     * Get total traffic for this device.
     */
    public function getTotalTraffic(): int
    {
        return $this->download_bytes + $this->upload_bytes;
    }

    /**
     * Check if device is over traffic limit.
     */
    public function isOverLimit(): bool
    {
        if ($this->traffic_limit_bytes <= 0) {
            return false;
        }

        return $this->getTotalTraffic() >= $this->traffic_limit_bytes;
    }

    /**
     * Reset device traffic counters.
     */
    public function resetTraffic(): bool
    {
        $this->download_bytes = 0;
        $this->upload_bytes = 0;
        return $this->save();
    }
}
