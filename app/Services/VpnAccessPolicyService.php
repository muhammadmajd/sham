<?php

namespace App\Services;
use App\Models\Plan;
use App\Models\Device;
use App\Models\User;
use App\Models\VpnServer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class VpnAccessPolicyService
{
    /**
     * Cache TTL for access policy decisions (in seconds).
     */
    private const CACHE_TTL = 60;

    /**
     * Decide if a user can access a VPN server.
     * Results are cached to reduce database queries.
     */
    public function decide(?User $user, Device $device, VpnServer $server): array
    {
        // Generate cache key based on user, device, and server
        $cacheKey = $this->getCacheKey($user, $device, $server);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user, $device, $server) {
            return $this->computeDecision($user, $device, $server);
        });
    }

    /**
     * Compute the access decision without caching.
     */
    private function computeDecision(?User $user, Device $device, VpnServer $server): array
    {
        $serverType = strtolower((string) ($server->server_Payment_type ?? 'free'));
        $isPaidServer = $serverType === 'paid';
        $isFreeServer = !$isPaidServer;

        /*
        |--------------------------------------------------------------------------
        | 1) Guest / user not logged in
        |--------------------------------------------------------------------------
        */
        if (!$user) {
            if (!$isFreeServer) {
                return $this->deny(
                    reason: 'subscription_required',
                    message: 'This server requires an active subscription.',
                    meta: [
                        'server_type' => $serverType,
                    ]
                );
            }

            $deviceTrafficUsed = (int) $device->download_bytes + (int) $device->upload_bytes;
            $deviceTrafficLimit = (int) $device->traffic_limit_bytes;

            if ($deviceTrafficLimit > 0 && $deviceTrafficUsed >= $deviceTrafficLimit) {
                return $this->deny(
                    reason: 'free_limit_reached',
                    message: 'Your free traffic has ended. Please subscribe to continue.',
                    meta: [
                        'traffic_scope' => 'device',
                        'traffic_used' => $deviceTrafficUsed,
                        'traffic_limit' => $deviceTrafficLimit,
                        'download_bytes' => (int) $device->download_bytes,
                        'upload_bytes' => (int) $device->upload_bytes,
                        'remaining_bytes' => max(0, $deviceTrafficLimit - $deviceTrafficUsed),
                        'server_type' => $serverType,
                    ]
                );
            }

            return $this->allow(
                reason: 'guest_free_allowed',
                message: 'VPN can start.',
                meta: [
                    'traffic_scope' => 'device',
                    'traffic_used' => $deviceTrafficUsed,
                    'traffic_limit' => $deviceTrafficLimit,
                    'download_bytes' => (int) $device->download_bytes,
                    'upload_bytes' => (int) $device->upload_bytes,
                    'remaining_bytes' => $deviceTrafficLimit > 0
                        ? max(0, $deviceTrafficLimit - $deviceTrafficUsed)
                        : null,
                    'server_type' => $serverType,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 2) Logged-in user
        |--------------------------------------------------------------------------
        */
        $hasActivePaidPlan = $user->hasActiveSubscription();

        $plan = $this->resolvePlanForUser($user, $hasActivePaidPlan);

        if (!$plan) {
            return $this->deny(
                reason: 'subscription_required',
                message: 'No free plan is available. Please subscribe.',
                meta: [
                    'server_type' => $serverType,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 3) Logged-in but free plan cannot access paid server
        |--------------------------------------------------------------------------
        */
        if (!$hasActivePaidPlan && $isPaidServer) {
            return $this->deny(
                reason: 'subscription_required',
                message: 'This server requires an active subscription.',
                meta: [
                    'plan_id' => $plan->id,
                    'plan_key' => $plan->key,
                    'plan_name' => $plan->name,
                    'server_type' => $serverType,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 4) Plan expired
        |--------------------------------------------------------------------------
        */
        if ($this->isPlanExpired($user)) {
            return $this->deny(
                reason: 'plan_expired',
                message: 'Your plan has expired. Please renew your subscription.',
                meta: [
                    'plan_id' => $plan->id,
                    'plan_key' => $plan->key,
                    'plan_name' => $plan->name,
                    'server_type' => $serverType,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 5) Device number limit
        |--------------------------------------------------------------------------
        | device with no user -> new device
        */
        $devicesLimit = (int) ($plan->devices_number ?? 0);

        $otherDevicesCount = Device::query()
            ->where('user_id', $user->id)
            ->where('id', '!=', $device->id)
            ->count();

        $effectiveDevicesCount = $otherDevicesCount + 1;

        if ($devicesLimit > 0 && $effectiveDevicesCount > $devicesLimit) {
            if (!$hasActivePaidPlan) {
                return $this->deny(
                    reason: 'free_device_limit_reached',
                    message: "You cannot connect a new device. Device limit is {$devicesLimit} for the free plan. Please subscribe.",
                    meta: [
                        'plan_id' => $plan->id,
                        'plan_key' => $plan->key,
                        'plan_name' => $plan->name,
                        'devices_limit' => $devicesLimit,
                        'devices_count' => $effectiveDevicesCount,
                        'server_type' => $serverType,
                    ]
                );
            }

            return $this->deny(
                reason: 'plan_device_limit_reached',
                message: "You cannot connect a new device. Device limit is {$devicesLimit} for this plan. Please subscribe to another plan.",
                meta: [
                    'plan_id' => $plan->id,
                    'plan_key' => $plan->key,
                    'plan_name' => $plan->name,
                    'devices_limit' => $devicesLimit,
                    'devices_count' => $effectiveDevicesCount,
                    'server_type' => $serverType,
                ]
            );
        }

        return $this->allow(
            reason: $hasActivePaidPlan ? 'paid_plan_allowed' : 'free_plan_allowed',
            message: 'VPN can start.',
            meta: [
                'plan_id' => $plan->id,
                'plan_key' => $plan->key,
                'plan_name' => $plan->name,
                'devices_limit' => $devicesLimit,
                'devices_count' => $effectiveDevicesCount,
                'has_active_paid_plan' => $hasActivePaidPlan,
                'server_type' => $serverType,
            ]
        );
    }

    /**
     * Check if user has a subscription plan.
     */
    private function userHasPlan(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return !empty($user->plan_id)
            || !empty($user->subscription)
            || !empty($user->stripe_subscription_id);
    }

    /**
     * Check if user's plan has expired.
     */
    private function isPlanExpired(?User $user): bool
    {
        if (!$user || empty($user->subscription_ends_at)) {
            return false;
        }

        try {
            return Carbon::parse($user->subscription_ends_at)->isPast();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Build the decision array.
     */
    private function buildDecision(
        bool $hasActivePlan,
        bool $isPaidServer,
        bool $freeLimitReached,
        int $trafficUsed,
        int $trafficLimit,
        string $serverType
    ): array {
        $canStart = true;
        $reason = null;
        $message = 'VPN can start';
        $planExpired = !$hasActivePlan && ($trafficUsed > 0 || $trafficLimit > 0);

        if ($isPaidServer && !$hasActivePlan) {
            $canStart = false;
            $reason = 'subscription_required';
            $message = 'This server requires an active subscription.';
        } elseif ($freeLimitReached) {
            $canStart = false;
            $reason = 'free_limit_reached';
            $message = 'Your free traffic has ended. Please subscribe to continue.';
        }

        return [
            'can_start' => $canStart,
            'reason' => $reason,
            'message' => $message,
            'traffic_used' => $trafficUsed,
            'traffic_limit' => $trafficLimit,
            'has_active_plan' => $hasActivePlan,
            'plan_expired' => $planExpired,
            'server_type' => $serverType,
        ];
    }

    /**
     * Generate a cache key for the access decision.
     */
    private function getCacheKey(?User $user, Device $device, VpnServer $server): string
    {
        $userId = $user?->id ?? 'anonymous';
        $deviceTrafficUsed = (int) $device->download_bytes + (int) $device->upload_bytes;
        $deviceTrafficLimit = (int) $device->traffic_limit_bytes;

        $userPlanId = $user?->plan_id ?? 0;
        $subscriptionEndsAt = $user?->subscription_ends_at?->timestamp ?? 0;

        $devicesCount = $user
            ? Device::where('user_id', $user->id)->count()
            : 0;

            return implode(':', [
            'vpn_access',
            $userId,
            $device->id,
            $server->id,
            $deviceTrafficUsed,
            $deviceTrafficLimit,
            $userPlanId,
            $subscriptionEndsAt,
            $devicesCount,
        ]);
    }

    /**
     * Clear cache for a specific user.
     */
    public function clearUserCache(int $userId): void
    {
        // Note: This is a simple approach. For production, consider using cache tags
        // or a more sophisticated cache invalidation strategy.
        Cache::forget("user_subscription_status:{$userId}");
    }

    private function allow(string $reason, string $message, array $meta = []): array
    {
        return [
            'can_start' => true,
            'reason' => $reason,
            'message' => $message,
            'requires_subscription' => false,
            ...$meta,
        ];
    }

    private function deny(string $reason, string $message, array $meta = []): array
    {
        return [
            'can_start' => false,
            'reason' => $reason,
            'message' => $message,
            'requires_subscription' => true,
            ...$meta,
        ];
    }

    private function resolvePlanForUser(User $user, bool $hasActivePaidPlan): ?Plan
    {
        if ($hasActivePaidPlan && !empty($user->plan_id)) {
            return Plan::find($user->plan_id);
        }

        $freePlan = Plan::where('key', 'free')->first();

        if ($freePlan) {
            return $freePlan;
        }

        if (!empty($user->plan_id)) {
            return Plan::find($user->plan_id);
        }

        return null;
    }
}
