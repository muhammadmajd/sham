<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Models\AppVersion;
use App\Models\HomePageContent;
use App\Services\AuditService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class HomePageController extends Controller
{
    private const MAX_UPLOAD_KB = 40960;

    public function edit()
    {
        return view('admin.home-page.edit', [
            'content' => HomePageContent::current(),
            'versions' => AppVersion::query()
                ->orderByRaw("FIELD(platform, 'windows', 'android', 'macos', 'linux', 'ios')")
                ->orderBy('platform')
                ->get(),
            'platforms' => AppVersion::PLATFORMS,
        ]);
    }

    public function updateContent(Request $request)
    {
        $data = $request->validate([
            'is_published' => ['nullable', 'boolean'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'title_ar' => ['nullable', 'string', 'max:255'],
            'title_fa' => ['nullable', 'string', 'max:255'],
            'title_ru' => ['nullable', 'string', 'max:255'],
            'description_en' => ['nullable', 'string'],
            'description_ar' => ['nullable', 'string'],
            'description_fa' => ['nullable', 'string'],
            'description_ru' => ['nullable', 'string'],
        ]);

        $data['is_published'] = $request->boolean('is_published');
        HomePageContent::current()->update($data);

        AuditService::log('HomePage.updateContent', 'HomePage', ['by' => 'admin']);

        return redirect()->route('admin.home-page.edit')->with('success', 'Home page content updated.');
    }

    public function storeVersion(Request $request)
    {
        Log::info('Home page app upload started', [
            'platform' => $request->input('platform'),
            'has_file' => $request->hasFile('file'),
            'content_length' => $request->server('CONTENT_LENGTH'),
        ]);

        try {
            $data = $request->validate([
                'platform' => ['required', Rule::in(array_keys(AppVersion::PLATFORMS))],
                'file' => ['required', 'file', 'max:' . self::MAX_UPLOAD_KB],
            ]);

            $version = AppVersion::firstOrNew(['platform' => $data['platform']]);
            $data = array_merge($data, $this->storeUpload($request, $version->exists ? $version : null));
            $data['version'] = $this->versionFromFileName($data['file_name']);
            $data['status'] = 'active';
            $data['is_visible'] = true;
            unset($data['file']);

            $version->fill($data);
            $version->save();

            AuditService::log('HomePage.storeVersion', $version, ['by' => 'admin']);

            Log::info('Home page app upload finished', [
                'platform' => $version->platform,
                'file_path' => $version->file_path,
                'file_size' => $version->file_size,
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'message' => 'App build uploaded.',
                    'version' => $version,
                ]);
            }

            return redirect()->route('admin.home-page.edit')->with('success', 'App build uploaded.');
        } catch (Exception $exception) {
            Log::error('Home page app upload failed', [
                'message' => $exception->getMessage(),
                'platform' => $request->input('platform'),
                'has_file' => $request->hasFile('file'),
                'content_length' => $request->server('CONTENT_LENGTH'),
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'message' => $exception->getMessage(),
                ], 422);
            }

            return back()->withErrors(['file' => $exception->getMessage()])->withInput();
        }
    }

    public function updateVersion(Request $request, AppVersion $version)
    {
        $data = $this->validateVersion($request);
        $uploadData = $this->storeUpload($request, $version);

        $version->update(array_merge($data, $uploadData));

        AuditService::log('HomePage.updateVersion', $version, ['by' => 'admin']);

        return redirect()->route('admin.home-page.edit')->with('success', 'App version updated.');
    }

    public function destroyVersion(AppVersion $version)
    {
        $this->deleteFile($version->file_path);
        $version->delete();

        AuditService::log('HomePage.destroyVersion', 'AppVersion', ['id' => $version->id]);

        return redirect()->route('admin.home-page.edit')->with('success', 'App version deleted.');
    }

    public function toggleVersionVisibility(AppVersion $version)
    {
        $version->update(['is_visible' => ! $version->is_visible]);

        AuditService::log('HomePage.toggleVersionVisibility', $version, [
            'is_visible' => $version->is_visible,
        ]);

        return redirect()->route('admin.home-page.edit')->with('success', 'App version display updated.');
    }

    private function validateVersion(Request $request, bool $requireFile = false): array
    {
        $data = $request->validate([
            'platform' => ['required', Rule::in(array_keys(AppVersion::PLATFORMS))],
            'version' => ['required', 'string', 'max:100'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'is_visible' => ['nullable', 'boolean'],
            'file' => [$requireFile ? 'required' : 'nullable', 'file', 'max:' . self::MAX_UPLOAD_KB],
            'notes_en' => ['nullable', 'string'],
            'notes_ar' => ['nullable', 'string'],
            'notes_fa' => ['nullable', 'string'],
            'notes_ru' => ['nullable', 'string'],
        ]);

        $data['is_visible'] = $request->boolean('is_visible');
        unset($data['file']);

        return $data;
    }

    private function storeUpload(Request $request, ?AppVersion $version = null): array
    {
        if (! $request->hasFile('file')) {
            return [];
        }

        $file = $request->file('file');
        $directory = public_path('downloads/app-versions');
        File::ensureDirectoryExists($directory);

        if (! File::isWritable($directory)) {
            throw new Exception("Upload directory is not writable: {$directory}");
        }

        $safeName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeName = preg_replace('/[^A-Za-z0-9._-]+/', '-', $safeName) ?: 'app';
        $extension = $file->getClientOriginalExtension();
        $fileName = now()->format('YmdHis') . '-' . $safeName . ($extension ? '.' . $extension : '');

        $file->move($directory, $fileName);

        if ($version) {
            $this->deleteFile($version->file_path);
        }

        return [
            'file_path' => 'downloads/app-versions/' . $fileName,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => File::size(public_path('downloads/app-versions/' . $fileName)),
        ];
    }

    private function deleteFile(?string $path): void
    {
        if ($path && File::exists(public_path($path))) {
            File::delete(public_path($path));
        }
    }

    private function versionFromFileName(string $fileName): string
    {
        if (preg_match('/\d+(?:\.\d+){1,3}/', $fileName, $matches)) {
            return $matches[0];
        }

        return now()->format('Y.m.d.His');
    }
}
