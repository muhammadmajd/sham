<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\Request;

class ApplicationCatalogController extends Controller
{
    public function index(Request $request)
    {
        $type = trim((string) $request->get('type', ''));

        $applications = Application::query()
            ->select(['id', 'name', 'app_id', 'type', 'status'])
            ->where('status', 'active')
            ->when($type !== '', fn ($query) => $query->where('type', $type))
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $applications,
        ]);
    }
}
