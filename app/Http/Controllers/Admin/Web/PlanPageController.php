<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Services\AuditService;
use Illuminate\Http\Request;

class PlanPageController extends Controller
{
    public function index(Request $request)
    {
        AuditService::log('Plan.index', 'Plan', [
            'by' => 'admin',
        ]);
        $search = trim((string) $request->get('search', ''));

        $plans = Plan::query()
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.plans.index', compact('plans', 'search'));
    }

    public function create()
    {
        return view('admin.plans.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'unique:plans,key'],
            'name' => ['required', 'string'],
            'price_cents' => ['required', 'integer'],
            'currency' => ['required', 'string'],
            'interval' => ['nullable', 'string'],
            'stripe_price_id' => ['nullable', 'string'],
            'traffic_limit' => ['nullable', 'integer'],
        ]);

        Plan::create($data);
        AuditService::log('Plan.store', 'Plan', [
            'by' => 'admin',
            'name' => $data['name'],
        ]);

        return redirect()->route('admin.plans.index')->with('success', 'Plan created.');
    }

    public function edit(Plan $plan)
    {
        return view('admin.plans.edit', compact('plan'));
    }

    public function update(Request $request, Plan $plan)
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'unique:plans,key,' . $plan->id],
            'name' => ['required', 'string'],
            'price_cents' => ['required', 'integer'],
            'currency' => ['required', 'string'],
            'interval' => ['nullable', 'string'],
            'stripe_price_id' => ['nullable', 'string'],
            'traffic_limit' => ['nullable', 'integer'],
        ]);

        $plan->update($data);
        AuditService::log('Plan.update', 'Plan', [
            'by' => 'admin',
            'name' => $data['name'],
        ]);

        return redirect()->route('admin.plans.index')->with('success', 'Plan updated.');
    }

    public function destroy(Plan $plan)
    {
        $plan->delete();
        AuditService::log('Plan.destroy', 'Plan', [
            'by' => 'admin',
            'name' => $plan['name'],
        ]);

        return redirect()->route('admin.plans.index')->with('success', 'Plan deleted.');
    }
}
