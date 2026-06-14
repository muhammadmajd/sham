<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Home Page
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="bg-green-100 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-100 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                    <ul class="list-disc ps-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('admin.home-page.content.update') }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <label class="inline-flex items-center gap-2">
                            <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $content->is_published)) class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <span class="text-sm font-semibold text-gray-700">Display home page content</span>
                        </label>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            @foreach (['en' => 'English', 'ar' => 'Arabic', 'fa' => 'Persian / Iran', 'ru' => 'Russian'] as $code => $label)
                                <div class="border border-gray-200 rounded-lg p-4 space-y-4">
                                    <h3 class="font-semibold text-gray-900">{{ $label }}</h3>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                                        <input type="text" name="title_{{ $code }}" value="{{ old('title_' . $code, $content->{'title_' . $code}) }}"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                        <textarea name="description_{{ $code }}" rows="5"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description_' . $code, $content->{'description_' . $code}) }}</textarea>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                            Save Content
                        </button>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Upload App</h3>
                    <p class="text-sm text-gray-500 mb-4">Choose the operating system and upload its app file. Uploading again for the same OS replaces the old file. Maximum file size: 40 MB.</p>
                    <form id="app-upload-form" method="POST" action="{{ route('admin.home-page.versions.store') }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-[240px_1fr_auto] gap-4 md:items-end">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Operating System</label>
                                <select name="platform" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    @foreach ($platforms as $value => $label)
                                        @php
                                            $existingVersion = $versions->firstWhere('platform', $value);
                                            $exists = $existingVersion && $existingVersion->file_path;
                                        @endphp
                                        <option value="{{ $value }}" @selected(old('platform') === $value)>
                                            {{ $label }} {{ $exists ? '(uploaded)' : '(empty)' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">App File</label>
                                <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                                    <label for="app-file-input"
                                        class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 cursor-pointer">
                                        Choose File
                                    </label>
                                    <input id="app-file-input" type="file" name="file" class="sr-only" required>
                                    <span id="selected-file-name" class="text-sm text-gray-500">No file selected</span>
                                </div>
                            </div>

                            <button id="app-upload-button" type="submit"
                                class="inline-flex items-center justify-center px-4 py-2 bg-gray-900 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-800 disabled:opacity-60">
                                Upload Selected File
                            </button>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <button id="normal-upload-button" type="submit" data-normal-submit="1"
                                class="inline-flex items-center justify-center px-3 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 disabled:opacity-60">
                                Upload Without Progress
                            </button>
                            <span class="text-xs text-gray-500">Use this if the progress upload does not finish.</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-5 gap-2">
                            @foreach ($platforms as $value => $label)
                                @php
                                    $existingVersion = $versions->firstWhere('platform', $value);
                                    $exists = $existingVersion && $existingVersion->file_path;
                                @endphp
                                <div class="rounded-lg border px-3 py-2 {{ $exists ? 'border-green-200 bg-green-50 text-green-800' : 'border-gray-200 bg-gray-50 text-gray-500' }}">
                                    <div class="text-xs font-bold uppercase tracking-wide">{{ $label }}</div>
                                    <div class="text-xs mt-1">{{ $exists ? 'Uploaded' : 'Not uploaded' }}</div>
                                </div>
                            @endforeach
                        </div>

                        <div id="upload-progress-wrap" class="hidden">
                            <div class="flex items-center justify-between text-xs font-semibold text-gray-600 mb-1">
                                <span id="upload-progress-label">Ready to upload</span>
                                <span id="upload-progress-percent">0%</span>
                            </div>
                            <div class="h-3 rounded-full bg-gray-200 overflow-hidden">
                                <div id="upload-progress-bar" class="h-full rounded-full bg-indigo-600 transition-all" style="width: 0%;"></div>
                            </div>
                        </div>

                        <div id="upload-error" class="hidden rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700"></div>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 overflow-x-auto">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Uploaded Apps</h3>
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">OS</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Version</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">File</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Home Page</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Uploaded</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($versions as $version)
                                <tr>
                                    <td class="px-4 py-3 font-semibold text-gray-900">{{ $version->platform_label }}</td>
                                    <td class="px-4 py-3">{{ $version->version }}</td>
                                    <td class="px-4 py-3">
                                        @if ($version->download_url)
                                            <a href="{{ $version->download_url }}" target="_blank" class="text-indigo-600 font-semibold">
                                                {{ $version->file_name ?? basename($version->file_path) }}
                                            </a>
                                        @else
                                            <span class="text-gray-500">No file uploaded</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $version->is_visible && $version->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                            {{ $version->is_visible && $version->status === 'active' ? 'Visible' : 'Hidden' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">{{ $version->updated_at }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-2">
                                            <form method="POST" action="{{ route('admin.home-page.versions.toggle-visibility', $version) }}">
                                                @csrf
                                                <button type="submit"
                                                    class="inline-flex items-center px-3 py-2 {{ $version->is_visible ? 'bg-amber-500 hover:bg-amber-600' : 'bg-green-600 hover:bg-green-700' }} border border-transparent rounded-md text-xs font-semibold text-white">
                                                    {{ $version->is_visible ? 'Hide' : 'Show' }}
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('admin.home-page.versions.destroy', $version) }}" onsubmit="return confirm('Delete this uploaded app?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="inline-flex items-center px-3 py-2 bg-red-600 border border-transparent rounded-md text-xs font-semibold text-white hover:bg-red-700">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-gray-500">No app files uploaded.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('app-upload-form');
            const button = document.getElementById('app-upload-button');
            const fileInput = document.getElementById('app-file-input');
            const normalButton = document.getElementById('normal-upload-button');
            const selectedFileName = document.getElementById('selected-file-name');
            const wrap = document.getElementById('upload-progress-wrap');
            const bar = document.getElementById('upload-progress-bar');
            const percent = document.getElementById('upload-progress-percent');
            const label = document.getElementById('upload-progress-label');
            const errorBox = document.getElementById('upload-error');
            const maxUploadBytes = 40 * 1024 * 1024;

            if (!form) {
                return;
            }

            button.disabled = true;
            normalButton.disabled = true;

            if (fileInput) {
                fileInput.addEventListener('change', function () {
                    const file = fileInput.files && fileInput.files[0] ? fileInput.files[0] : null;
                    selectedFileName.textContent = file ? file.name : 'No file selected';
                    button.disabled = !file;
                    normalButton.disabled = !file;
                    errorBox.classList.add('hidden');

                    if (file) {
                        wrap.classList.remove('hidden');
                        label.textContent = 'Ready to upload';
                        percent.textContent = '0%';
                        bar.style.width = '0%';

                        if (file.size > maxUploadBytes) {
                            button.disabled = true;
                            normalButton.disabled = true;
                            label.textContent = 'File is too large';
                            errorBox.textContent = 'This server currently accepts uploads up to 40 MB. Choose a smaller file or increase PHP upload_max_filesize and post_max_size on the server.';
                            errorBox.classList.remove('hidden');
                        }
                    } else {
                        wrap.classList.add('hidden');
                    }
                });
            }

            form.addEventListener('submit', function (event) {
                if (event.submitter && event.submitter.dataset.normalSubmit === '1') {
                    return;
                }

                event.preventDefault();

                if (!fileInput.files || !fileInput.files.length) {
                    selectedFileName.textContent = 'Please choose a file first';
                    button.disabled = true;
                    return;
                }

                const data = new FormData(form);
                const request = new XMLHttpRequest();

                button.disabled = true;
                errorBox.classList.add('hidden');
                wrap.classList.remove('hidden');
                label.textContent = 'Uploading...';
                percent.textContent = '0%';
                bar.style.width = '0%';

                request.upload.addEventListener('progress', function (progressEvent) {
                    if (!progressEvent.lengthComputable) {
                        label.textContent = 'Uploading...';
                        return;
                    }

                    const value = Math.min(99, Math.round((progressEvent.loaded / progressEvent.total) * 100));
                    label.textContent = value >= 99 ? 'Saving on server...' : 'Uploading to server...';
                    percent.textContent = value + '%';
                    bar.style.width = value + '%';
                });

                request.addEventListener('load', function () {
                    if (request.status >= 200 && request.status < 400) {
                        label.textContent = 'Upload complete. Refreshing...';
                        percent.textContent = '100%';
                        bar.style.width = '100%';
                        window.location.reload();
                        return;
                    }

                    button.disabled = false;
                    let message = 'Upload failed. Please try again.';
                    try {
                        const response = JSON.parse(request.responseText);
                        if (response.message) {
                            message = response.message;
                        }
                        if (response.errors) {
                            message = Object.values(response.errors).flat().join(' ');
                        }
                    } catch (error) {
                        if (request.status === 413) {
                            message = 'The file is larger than the server upload limit.';
                        }
                    }

                    label.textContent = 'Upload failed.';
                    errorBox.textContent = message;
                    errorBox.classList.remove('hidden');
                });

                request.addEventListener('error', function () {
                    button.disabled = false;
                    label.textContent = 'Upload failed. Please try again.';
                    errorBox.textContent = 'The upload request could not reach the server.';
                    errorBox.classList.remove('hidden');
                });

                request.open('POST', form.action);
                request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                request.send(data);
            });
        });
    </script>
</x-app-layout>
