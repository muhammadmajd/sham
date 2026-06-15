<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Device
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

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
                    <form method="POST" action="{{ route('admin.devices.update', $device) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Device UID</label>
                                <input type="text" name="device_uid"
                                    value="{{ old('device_uid', $device->device_uid) }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">User</label>
                                <select name="user_id"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">-- None --</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}"
                                            {{ (string) old('user_id', $device->user_id) === (string) $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Platform</label>
                                <input type="text" name="platform" value="{{ old('platform', $device->platform) }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Xray Client UUID</label>
                                <input type="text" name="xray_client_uuid"
                                    value="{{ old('xray_client_uuid', $device->xray_client_uuid) }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Xray Email</label>
                                <input type="text" name="xray_email"
                                    value="{{ old('xray_email', $device->xray_email) }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Last Seen At</label>
                                <input type="datetime-local" name="last_seen_at"
                                    value="{{ old('last_seen_at', isset($device->last_seen_at) && $device->last_seen_at ? \Illuminate\Support\Carbon::parse($device->last_seen_at)->format('Y-m-d\TH:i') : '') }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Download Bytes</label>
                                <input type="number" name="download_bytes"
                                    value="{{ old('download_bytes', $device->download_bytes) }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Upload Bytes</label>
                                <input type="number" name="upload_bytes"
                                    value="{{ old('upload_bytes', $device->upload_bytes) }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Active</label>
                                <select name="active"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="1"
                                        {{ old('active', (int) $device->active) == 1 ? 'selected' : '' }}>Yes</option>
                                    <option value="0"
                                        {{ old('active', (int) $device->active) == 0 ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                        </div>


                        <div class="flex items-center gap-3">
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                Save
                            </button>

                            <a href="{{ route('admin.devices.index') }}"
                                class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900">Attach User</h3>

                    <form method="POST" action="{{ route('admin.devices.attach-user', $device) }}" class="space-y-4">
                        @csrf

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">User</label>
                            <select name="user_id" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">-- Select User --</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-slate-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-slate-800">
                            Attach User
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
