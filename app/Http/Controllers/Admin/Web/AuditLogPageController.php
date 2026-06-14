<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogPageController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->integer('per_page', 20);

        $logs = AuditLog::with('user:id,name,email')
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.audit-logs.index', compact('logs'));
    }

    public function clear()
    {
        AuditLog::query()->delete();

        return redirect()->route('admin.audit-logs.index')->with('success', 'Audit logs cleared.');
    }
}
