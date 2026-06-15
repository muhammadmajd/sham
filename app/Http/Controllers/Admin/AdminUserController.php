<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\User;
use App\Services\AuditService;
use App\Services\UserSubscriptionService;
use App\Services\TrafficLimitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        AuditService::log('admin.users.index', 'User', [
            'from' => false,
            'to' => true,
        ]);

        $pageSize = $request->integer('pageSize', 10);
        $search = trim((string) $request->get('search', ''));
        $sortBy = $request->get('sortBy', 'id');
        $sortDir = strtolower((string) $request->get('sortDir', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowedSorts = [
            'id',
            'name',
            'email',
            'code',
            'active',
            'plan_id',
            'subscription',
            'traffic_used',
            'traffic_limit',
            'created_at',
            'updated_at',
        ];

        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'id';
        }

        $query = User::query()
            ->with('plan:id,name')
            ->search($search)
            ->withDeviceStats()
            ->orderBy($sortBy, $sortDir);

        return response()->json($query->paginate($pageSize));
    }

    public function show($id)
    {
        AuditService::log('admin.users.show', 'User', [
            'from' => false,
            'to' => true,
        ]);

        $user = User::query()
            ->with('plan:id,name')
            ->withDeviceStats()
            ->whereKey($id)
            ->firstOrFail();

        return response()->json($user);
    }

    public function store(
        Request $request,
        UserSubscriptionService $service,
        TrafficLimitService $trafficLimits
    ) {
        AuditService::log('admin.users.store', 'User', [
            'from' => false,
            'to' => true,
        ]);

        $data = $request->validate([
            'code' => ['required', 'string', 'max:100', 'unique:users,code'],
            'active' => ['nullable', 'boolean'],
            'is_admin' => ['nullable', 'boolean'],
            'plan_id' => ['nullable', 'integer', 'exists:plans,id'],
            'subscription' => ['nullable', 'string', 'max:255'],
            'traffic_used' => ['nullable', 'integer', 'min:0'],
            'traffic_limit' => ['nullable', 'integer', 'min:0'],
        ]);

        $cleanCode = trim($data['code']);
        $generatedName = 'user_' . $cleanCode;
        $generatedEmail = 'code_' . Str::lower(Str::slug($cleanCode, '_')) . '@code.mvpn';
        $generatedPassword = Str::random(16);

        DB::beginTransaction();

        try {
            $freePlan = $trafficLimits->freePlan();
            $defaultFreeTrafficLimit = (int) ($freePlan?->traffic_limit ?? 0);

            $user = User::create([
                'uuid' => (string) Str::uuid(),
                'code' => $cleanCode,
                'name' => $generatedName,
                'email' => $generatedEmail,
                'password' => Hash::make($generatedPassword),
                'active' => (bool) ($data['active'] ?? true),
                'is_admin' => (bool) ($data['is_admin'] ?? false),

                // start as free unless service upgrades him
                'plan_id' => null,
                'subscription' => 'free',
                'traffic_used' => $data['traffic_used'] ?? 0,
                'traffic_limit' => $data['traffic_limit'] ?? $defaultFreeTrafficLimit,
            ]);

            if (!empty($data['plan_id'])) {
                $plan = Plan::findOrFail($data['plan_id']);

                $user = $service->assignPlanByAdmin(
                    user: $user,
                    plan: $plan,
                    startedAt: now(),
                    endsAt: null,
                    status: $data['subscription'] ?? 'active',
                    notes: 'Assigned automatically when user was created by admin',
                );
            }

            DB::commit();

            return response()->json($user->load('plan:id,name'), 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(Request $request, $id)
    {
        AuditService::log('admin.users.update', 'User', [
            'from' => false,
            'to' => true,
        ]);

        $user = User::findOrFail($id);

        $data = $request->validate([
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('users', 'code')->ignore($user->id),
            ],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => ['nullable', 'string', 'min:6'],
            'active' => ['nullable', 'boolean'],
            'is_admin' => ['nullable', 'boolean'],
            'plan_id' => ['nullable', 'integer', 'exists:plans,id'],
            'subscription' => ['nullable', 'string', 'max:255'],
            'traffic_used' => ['nullable', 'integer', 'min:0'],
            'traffic_limit' => ['nullable', 'integer', 'min:0'],
            'subscription_started_at' => ['nullable', 'date'],
            'subscription_ends_at' => ['nullable', 'date'],
            'subscription_renewed_at' => ['nullable', 'date'],
            'subscription_canceled_at' => ['nullable', 'date'],
        ]);

        $fillableFields = [
            'code',
            'name',
            'email',
            'active',
            'is_admin',
            'plan_id',
            'subscription',
            'traffic_used',
            'traffic_limit',
            'subscription_started_at',
            'subscription_ends_at',
            'subscription_renewed_at',
            'subscription_canceled_at',
        ];

        foreach ($fillableFields as $field) {
            if (array_key_exists($field, $data)) {
                $user->$field = $data[$field];
            }
        }

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return response()->json($user->load('plan:id,name'));
    }

    public function destroy($id)
    {
        AuditService::log('admin.users.destroy', 'User', [
            'from' => false,
            'to' => true,
        ]);

        $user = User::findOrFail($id);
        $user->delete();

        return response()->json([
            'message' => 'Deleted',
        ]);
    }

    public function toggleActive($id)
    {
        AuditService::log('admin.users.toggle_active', 'User', [
            'from' => false,
            'to' => true,
        ]);

        $user = User::findOrFail($id);
        $user->active = !$user->active;
        $user->save();

        $user = User::query()
            ->with('plan:id,name')
            ->withDeviceStats()
            ->whereKey($id)
            ->firstOrFail();

        return response()->json($user);
    }

    public function assignPlan(Request $request, $id, UserSubscriptionService $service)
    {
        AuditService::log('admin.users.assign_plan', 'User', [
            'from' => false,
            'to' => true,
        ]);

        $user = User::findOrFail($id);

        $data = $request->validate([
            'plan_id' => ['required', 'integer', 'exists:plans,id'],
            'started_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $plan = Plan::findOrFail($data['plan_id']);

        $service->assignPlanByAdmin(
            user: $user,
            plan: $plan,
            startedAt: !empty($data['started_at']) ? \Carbon\Carbon::parse($data['started_at']) : null,
            endsAt: !empty($data['ends_at']) ? \Carbon\Carbon::parse($data['ends_at']) : null,
            status: $data['status'] ?? 'active',
            notes: $data['notes'] ?? 'Assigned by admin',
        );

        $user->refresh();

        return response()->json([
            'message' => 'Plan assigned successfully',
            'user' => $user->load('plan:id,name'),
        ]);
    }
}
