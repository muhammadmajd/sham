<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\User;
use App\Models\UserSubscription;
use App\Services\TrafficLimitService;
use Carbon\Carbon;

class UserSubscriptionService
{
    public function __construct(
        private TrafficLimitService $trafficLimits
    ) {}
    public function assignPlanByAdmin(
        User $user,
        Plan $plan,
        ?Carbon $startedAt = null,
        ?Carbon $endsAt = null,
        ?string $status = 'active',
        ?string $notes = 'Assigned by admin'
    ): User {
        $startedAt = $startedAt ?? now();

        if ($endsAt === null) {
            if ($plan->interval === 'month') {
                $endsAt = (clone $startedAt)->addMonth();
            } elseif ($plan->interval === 'year') {
                $endsAt = (clone $startedAt)->addYear();
            }
        }

        $user->plan_id = $plan->id;
        $user->subscription = $status;
        $user->traffic_limit = $plan->traffic_limit;
        $user->subscription_started_at = $startedAt;
        $user->subscription_ends_at = $endsAt;
        $user->subscription_renewed_at = now();

        // Optional: since this is admin assignment, usually no Stripe subscription exists
        $user->stripe_subscription_id = null;

        $user->save();
        /*
        * After plan change:
        * every attached device gets:
        * user.traffic_limit / plan.devices_number
        */
        $this->trafficLimits->redistributeUserDeviceLimits($user);

        UserSubscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'subscription' => $status,
            'payment_type' => 'by_admin',
            'started_at' => $startedAt,
            'ends_at' => $endsAt,
            'price_cents' => $plan->price_cents,
            'currency' => $plan->currency,
            'traffic_limit' => $plan->traffic_limit,
            'stripe_subscription_id' => null,
            'stripe_price_id' => $plan->stripe_price_id,
            'notes' => $notes,
        ]);

        return $user->fresh();
    }
}
