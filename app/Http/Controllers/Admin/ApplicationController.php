<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ApplicationController extends Controller
{
    public function index(Request $request)
    {
        AuditService::log('applications.index', 'Application', [
            'from' => false,
            'to' => true,
        ]);

        $search = trim((string) $request->get('search', ''));
        $sortBy = $request->get('sortBy', 'id');
        $sortDir = strtolower((string) $request->get('sortDir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['id', 'name', 'app_id', 'type', 'status', 'created_at', 'updated_at'];

        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'id';
        }

        return response()->json(
            Application::query()
                ->search($search)
                ->orderBy($sortBy, $sortDir)
                ->get()
        );
    }

    public function store(Request $request)
    {
        $data = $this->validateApplication($request);
        $application = Application::create($data);

        AuditService::log('applications.store', $application, [
            'name' => $application->name,
        ]);

        return response()->json($application, 201);
    }

    public function show($id)
    {
        $application = Application::findOrFail($id);

        AuditService::log('applications.show', $application);

        return response()->json($application);
    }

    public function update(Request $request, $id)
    {
        $application = Application::findOrFail($id);
        $data = $this->validateApplication($request, $application, true);

        $application->update($data);

        AuditService::log('applications.update', $application, [
            'name' => $application->name,
        ]);

        return response()->json($application);
    }

    public function destroy($id)
    {
        $application = Application::findOrFail($id);
        $application->delete();

        AuditService::log('applications.destroy', 'Application', [
            'id' => $id,
            'name' => $application->name,
        ]);

        return response()->noContent();
    }

    private function validateApplication(Request $request, ?Application $application = null, bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return $request->validate([
            'name' => [$required, 'string', 'max:255'],
            'app_id' => [
                $required,
                'string',
                'max:255',
                Rule::unique('applications', 'app_id')->ignore($application),
            ],
            'type' => [$required, 'string', 'max:100'],
            'status' => [$required, Rule::in(['active', 'inactive'])],
        ]);
    }
}
