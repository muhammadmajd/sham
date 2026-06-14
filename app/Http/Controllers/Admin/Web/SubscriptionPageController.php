<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\User;
use App\Models\UserSubscription;
use App\Services\AuditService;
use App\Services\UserSubscriptionService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SubscriptionPageController extends Controller
{
    public function index()
    {
        AuditService::log('Subscription.index', 'Subscription', [
            'by' => 'admin',
        ]);
        $subscriptions = UserSubscription::with(['user:id,name,email', 'plan:id,name'])
            ->latest()
            ->paginate(15);

        return view('admin.subscriptions.index', [
            'subscriptions' => $subscriptions,
        ]);
    }

    public function create()
    {
        return view('admin.subscriptions.create', [
            'users' => User::orderBy('name')->get(['id', 'name', 'email']),
            'plans' => Plan::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request, UserSubscriptionService $service)
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

        $service->assignPlanByAdmin(
            user: $user,
            plan: $plan,
            startedAt: !empty($data['started_at']) ? Carbon::parse($data['started_at']) : null,
            endsAt: !empty($data['ends_at']) ? Carbon::parse($data['ends_at']) : null,
            status: $data['status'] ?? 'active',
            notes: $data['notes'] ?? 'Assigned by admin',
        );
        AuditService::log('Subscription.store', 'Subscription', [
            'by' => 'admin',
            'user_id' => $data['user_id'],
            'plan_id' => $data['plan_id'],
        ]);

        return redirect()->route('admin.subscriptions.index')->with('success', 'Subscription assigned.');
    }
}
