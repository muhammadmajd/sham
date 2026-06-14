<?php

namespace App\Services;

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
        $serverType = strtolower((string) ($server->server_payment_type ?? 'free'));
        $isPaidServer = $serverType === 'paid';
        $isFreeServer = !$isPaidServer;

        $hasPlan = $this->userHasPlan($user);
        $hasActivePlan = $hasPlan && !$this->isPlanExpired($user);

        $trafficUsed = (int) ($user?->traffic_used ?? 0);
        $trafficLimit = (int) ($user?->traffic_limit ?? 0);

        $freeLimitReached =
            !$hasActivePlan &&
            $isFreeServer &&
            $trafficLimit > 0 &&
            $trafficUsed >= $trafficLimit;

        return $this->buildDecision($hasActivePlan, $isPaidServer, $freeLimitReached, $trafficUsed, $trafficLimit, $serverType);
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
        $trafficUsed = $user?->traffic_used ?? 0;
        $trafficLimit = $user?->traffic_limit ?? 0;
        $subscriptionEndsAt = $user?->subscription_ends_at?->timestamp ?? 0;

        return "vpn_access:{$userId}:{$device->id}:{$server->id}:{$trafficUsed}:{$trafficLimit}:{$subscriptionEndsAt}";
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
}
