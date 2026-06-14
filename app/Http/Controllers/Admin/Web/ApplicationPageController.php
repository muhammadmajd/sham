<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ApplicationPageController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));

        $applications = Application::query()
            ->search($search)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        AuditService::log('Application.index', 'Application', [
            'by' => 'admin',
        ]);

        return view('admin.applications.index', compact('applications', 'search'));
    }

    public function create()
    {
        return view('admin.applications.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateApplication($request);
        $application = Application::create($data);

        AuditService::log('Application.store', $application, [
            'by' => 'admin',
            'name' => $application->name,
        ]);

        return redirect()->route('admin.applications.index')->with('success', 'Application created.');
    }

    public function edit(Application $application)
    {
        return view('admin.applications.edit', compact('application'));
    }

    public function update(Request $request, Application $application)
    {
        $data = $this->validateApplication($request, $application);
        $application->update($data);

        AuditService::log('Application.update', $application, [
            'by' => 'admin',
            'name' => $application->name,
        ]);

        return redirect()->route('admin.applications.index')->with('success', 'Application updated.');
    }

    public function destroy(Application $application)
    {
        $name = $application->name;
        $application->delete();

        AuditService::log('Application.destroy', 'Application', [
            'by' => 'admin',
            'name' => $name,
        ]);

        return redirect()->route('admin.applications.index')->with('success', 'Application deleted.');
    }

    public function toggleStatus(Application $application)
    {
        $application->update([
            'status' => $application->status === 'active' ? 'inactive' : 'active',
        ]);

        AuditService::log('Application.toggleStatus', $application, [
            'by' => 'admin',
            'status' => $application->status,
        ]);

        return redirect()->route('admin.applications.index')->with('success', 'Application status updated.');
    }

    private function validateApplication(Request $request, ?Application $application = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'app_id' => [
                'required',
                'string',
                'max:255',
                Rule::unique('applications', 'app_id')->ignore($application),
            ],
            'type' => ['required', 'string', 'max:100'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);
    }
}
