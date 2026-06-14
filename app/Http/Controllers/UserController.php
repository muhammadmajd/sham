<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Device;
use Illuminate\Http\Request;
use App\Services\AuditService;

class UserController extends Controller
{
    /**
     * Get user traffic statistics.
     */
    public function stats(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        AuditService::log('user.stats', 'User', [
            'from' => false,
            'to' => true,
        ]);

        return response()->json([
            'traffic_used' => $user->traffic_used,
            'traffic_limit' => $user->traffic_limit,
        ]);
    }

    /**
     * Get total device usage for a user.
     * Optimized to use a single aggregated query.
     */
    public function usageTotal(Request $request)
    {
        $user = $this->resolveUser($request, true);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
                'download_bytes' => 0,
                'upload_bytes' => 0,
                'devices_count' => 0,
            ], 404);
        }

        AuditService::log('user.usage_total', 'User', [
            'from' => $user->name ?? $user->id,
            'to' => true,
        ]);

        // Use the optimized method from User model
        $traffic = $user->getTotalDeviceTraffic();

        return response()->json([
            'success' => true,
            'user_id' => $user->id,
            'download_bytes' => $traffic['download_bytes'],
            'upload_bytes' => $traffic['upload_bytes'],
            'devices_count' => $traffic['devices_count'],
        ]);
    }

    /**
     * Get user's subscription history.
     */
    public function subscriptionHistory(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $history = $user->subscriptionHistory()
            ->with('plan:id,name')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'user_id' => $item->user_id,
                    'plan_id' => $item->plan_id,
                    'plan_name' => $item->plan?->name,
                    'subscription' => $item->subscription,
                    'payment_type' => $item->payment_type,
                    'started_at' => $item->started_at?->toIso8601String(),
                    'ends_at' => $item->ends_at?->toIso8601String(),
                    'price_cents' => $item->price_cents,
                    'currency' => $item->currency,
                    'traffic_limit' => $item->traffic_limit,
                    'stripe_subscription_id' => $item->stripe_subscription_id,
                    'stripe_price_id' => $item->stripe_price_id,
                    'notes' => $item->notes,
                ];
            });

        return response()->json($history);
    }

    /**
     * Resolve user from request.
     */
    private function resolveUser(Request $request, bool $validateUserId = false): ?User
    {
        $user = $request->user();

        if (!$user && $request->filled('user_id')) {
            if ($validateUserId) {
                $request->validate([
                    'user_id' => ['integer', 'exists:users,id'],
                ]);
            }
            $user = User::find($request->input('user_id'));
        }

        return $user;
    }
}
