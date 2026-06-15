<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Services\AuditService;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        AuditService::log('plans.get', 'Plan', [
            'from' => false,
            'to' => true,
        ]);
        return response()->json(
            data: Plan::orderBy(
                request('sortBy', 'id'),
                request('sortDir', 'desc')
            )->where('name', 'like', '%' . request('search') . '%')
                ->paginate(request('pageSize', 10))
        );
    }

    public function store(Request $request)
    {
        AuditService::log('plans.store', 'Plan', [
            'from' => false,
            'to' => true,
        ]);
        $data = $request->validate([
            'key' => 'required|string|unique:plans,key',
            'name' => 'required|string',
            'price_cents' => 'required|integer',
            'currency' => 'required|string',
            'interval' => 'nullable|string',
            'stripe_price_id' => 'nullable|string',
            'traffic_limit' => 'nullable|integer',
            'devices_number' => 'nullable|integer',
        ]);

        $plan = Plan::create($data);
        return response()->json($plan, 201);
    }

    public function show($id)
    {
        AuditService::log('plans.show', 'Plan', [
            'from' => false,
            'to' => true,
        ]);
        $plan = Plan::findOrFail($id);
        return response()->json($plan);
    }

    public function update(Request $request, $id)
    {
        AuditService::log('plans.update', 'Plan', [
            'from' => false,
            'to' => true,
        ]);
        $data = $request->validate([
            'key' => 'sometimes|required|string|unique:plans,key,' . $id,
            'name' => 'sometimes|required|string',
            'price_cents' => 'required|integer',
            'currency' => 'required|string',
            'interval' => 'nullable|string',
            'stripe_price_id' => 'nullable|string',
            'traffic_limit' => 'nullable|integer',
            'devices_number' => 'nullable|integer',
        ]);

        $plan = Plan::findOrFail($id);
        $plan->update($data);
        return response()->json($plan);
    }

    public function destroy($id)
    {
        AuditService::log('plans.destroy', 'Plan', [
            'from' => false,
            'to' => true,
        ]);
        $plan = Plan::findOrFail($id);
        $plan->delete();
        return response()->noContent();
    }
}
