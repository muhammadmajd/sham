<?php

namespace App\Http\Controllers;

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
        $plans = Plan::all();
        return response()->json($plans);
    }
}
