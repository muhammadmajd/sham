<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\Plan;
use App\Models\User;
use App\Services\AuditService;
use App\Services\UserSubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UserPageController extends Controller
{
    public function index(Request $request)
    {
        AuditService::log('User.index', 'User', [
            'by' => 'admin',
        ]);

        $pageSize = $request->integer('pageSize', 15);
        $search = trim((string) $request->get('search', ''));

        $users = User::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('uuid', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('subscription', 'like', "%{$search}%");
                });
            })
            ->addSelect([
                'devices_count' => Device::query()
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('devices.user_id', 'users.id'),
                'download_bytes' => Device::query()
                    ->selectRaw('COALESCE(SUM(download_bytes), 0)')
                    ->whereColumn('devices.user_id', 'users.id'),
                'upload_bytes' => Device::query()
                    ->selectRaw('COALESCE(SUM(upload_bytes), 0)')
                    ->whereColumn('devices.user_id', 'users.id'),
            ])
            ->latest()
            ->paginate($pageSize)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'search'));
    }

    public function create()
    {
        return view('admin.users.create', [
            'plans' => Plan::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, UserSubscriptionService $service)
    {
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
            $user = User::create([
                'uuid' => (string) Str::uuid(),
                'code' => $cleanCode,
                'name' => $generatedName,
                'email' => $generatedEmail,
                'password' => Hash::make($generatedPassword),
                'active' => (bool) ($data['active'] ?? true),
                'is_admin' => (bool) ($data['is_admin'] ?? false),
                'plan_id' => null,
                'subscription' => 'free',
                'traffic_used' => $data['traffic_used'] ?? 0,
                'traffic_limit' => $data['traffic_limit'] ?? 0,
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

            AuditService::log('User.store', 'User', [
                'by' => 'admin',
                'code' => $cleanCode,
            ]);

            return redirect()->route('admin.users.index')->with('success', 'User created.');
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', [
            'user' => $user,
            'plans' => Plan::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('users', 'code')->ignore($user->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
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

        $user->fill([
            'code' => $data['code'],
            'name' => $data['name'],
            'email' => $data['email'],
            'active' => (bool) ($data['active'] ?? false),
            'is_admin' => (bool) ($data['is_admin'] ?? false),
            'plan_id' => $data['plan_id'] ?? null,
            'subscription' => $data['subscription'] ?? 'free',
            'traffic_used' => $data['traffic_used'] ?? 0,
            'traffic_limit' => $data['traffic_limit'] ?? 0,
            'subscription_started_at' => $data['subscription_started_at'] ?? null,
            'subscription_ends_at' => $data['subscription_ends_at'] ?? null,
            'subscription_renewed_at' => $data['subscription_renewed_at'] ?? null,
            'subscription_canceled_at' => $data['subscription_canceled_at'] ?? null,
        ]);

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        AuditService::log('User.update', 'User', [
            'by' => 'admin',
            'email' => $data['email'],
            'code' => $data['code'],
        ]);

        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'User updated.');
    }

    public function destroy(User $user)
    {
        $user->delete();

        AuditService::log('User.destroy', 'User', [
            'by' => 'admin',
            'email' => $user->email,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User deleted.');
    }

    public function toggleActive(User $user)
    {
        $user->active = !$user->active;
        $user->save();

        AuditService::log('User.toggleActive', 'User', [
            'by' => 'admin',
            'email' => $user->email,
            'active' => $user->active,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User status updated.');
    }

    public function assignPlan(Request $request, User $user, UserSubscriptionService $service)
    {
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

        AuditService::log('User.assignPlan', 'User', [
            'by' => 'admin',
            'plan_id' => $data['plan_id'],
            'user_id' => $user->id,
        ]);

        return redirect()->route('admin.users.edit', $user)->with('success', 'Plan assigned.');
    }
}
