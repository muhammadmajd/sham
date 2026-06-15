<?php

namespace App\Services;

use App\Models\Device;
use App\Models\Plan;
use App\Models\User;

class TrafficLimitService
{
    public function freePlan(): ?Plan
    {
        return Plan::where('key', 'free')->first();
    }

    public function userCurrentPlan(User $user): ?Plan
    {
        if (!empty($user->plan_id)) {
            return Plan::find($user->plan_id);
        }

        return $this->freePlan();
    }

    public function setUserFreeLimit(User $user, bool $save = true): User
    {
        $freePlan = $this->freePlan();

        if (!$freePlan) {
            return $user;
        }

        $user->traffic_limit = (int) $freePlan->traffic_limit;

        /*
         * Optional but recommended:
         * Keep new users logically on the free subscription.
         * I do NOT force plan_id = free plan id here unless you want that behavior.
         */
        if (empty($user->subscription)) {
            $user->subscription = 'free';
        }

        if ($save) {
            $user->save();
        }

        return $user;
    }

    public function setUserPlanLimit(User $user, Plan $plan, bool $save = true): User
    {
        $user->plan_id = $plan->id;
        $user->traffic_limit = (int) $plan->traffic_limit;

        if ($save) {
            $user->save();
        }

        return $user;
    }

    public function setGuestDeviceLimit(Device $device, bool $save = true): Device
    {
        $freePlan = $this->freePlan();

        if (!$freePlan) {
            return $device;
        }

        /*
         * Guest device:
         * device.user_id is null, so give it the full free plan traffic limit.
         */
        $device->traffic_limit_bytes = (int) $freePlan->traffic_limit;

        if ($save) {
            $device->save();
        }

        return $device;
    }

    public function getPerDeviceLimitForUser(User $user): int
    {
        $plan = $this->userCurrentPlan($user);

        $userTrafficLimit = (int) $user->traffic_limit;

        /*
         * Safety fallback:
         * If user.traffic_limit is empty, recover it from plan/free plan.
         */
        if ($userTrafficLimit <= 0 && $plan) {
            $userTrafficLimit = (int) $plan->traffic_limit;
        }

        $devicesNumber = (int) ($plan?->devices_number ?? 0);

        /*
         * If devices_number <= 0, treat as unlimited devices or undefined.
         * In that case, do not split. Keep full user limit on device.
         */
        if ($devicesNumber <= 0) {
            return $userTrafficLimit;
        }

        return (int) floor($userTrafficLimit / $devicesNumber);
    }

    public function setDeviceLimitForUser(Device $device, User $user, bool $save = true): Device
    {
        $device->traffic_limit_bytes = $this->getPerDeviceLimitForUser($user);

        if ($save) {
            $device->save();
        }

        return $device;
    }

    public function applyCorrectDeviceLimit(Device $device, bool $save = true): Device
    {
        if (empty($device->user_id)) {
            return $this->setGuestDeviceLimit($device, $save);
        }

        $user = User::find($device->user_id);

        if (!$user) {
            return $this->setGuestDeviceLimit($device, $save);
        }

        return $this->setDeviceLimitForUser($device, $user, $save);
    }

    public function redistributeUserDeviceLimits(User $user): void
    {
        $perDeviceLimit = $this->getPerDeviceLimitForUser($user);

        Device::where('user_id', $user->id)->update([
            'traffic_limit_bytes' => $perDeviceLimit,
        ]);
    }
}
