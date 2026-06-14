<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\User;
use App\Services\UserSubscriptionService;
use Illuminate\Http\Request;

class AdminSubscriptionController extends Controller
{
    public function assignPlan(Request $request, UserSubscriptionService $service)
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'plan_id' => ['required', 'integer', 'exists:plans,id'],
            'started_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'status' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $user = User::findOrFail($data['user_id']);
        $plan = Plan::findOrFail($data['plan_id']);

        $updatedUser = $service->assignPlanByAdmin(
            user: $user,
            plan: $plan,
            startedAt: isset($data['started_at']) ? \Carbon\Carbon::parse($data['started_at']) : null,
            endsAt: isset($data['ends_at']) ? \Carbon\Carbon::parse($data['ends_at']) : null,
            status: $data['status'] ?? 'active',
            notes: $data['notes'] ?? 'Assigned by admin',
        );

        return response()->json([
            'success' => true,
            'message' => 'Plan assigned successfully',
            'user' => $updatedUser,
        ]);
    }
}
