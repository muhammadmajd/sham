<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Devices
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <form method="GET" class="w-full md:w-96">
                        <input
                            type="text"
                            name="search"
                            value="{{ $search }}"
                            placeholder="Search device uid, platform, xray info, user..."
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                    </form>

                    <a href="{{ route('admin.devices.create') }}"
                       class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                        Create Device
                    </a>
                </div>
            </div>

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
                <div class="p-6 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">ID</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Device UID</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">User</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Platform</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Xray Email</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Download</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Upload</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Last Seen</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($devices as $device)
                                <tr>
                                    <td class="px-4 py-3">{{ $device->id }}</td>
                                    <td class="px-4 py-3">{{ $device->device_uid }}</td>
                                    <td class="px-4 py-3">
                                        @if($device->user)
                                            <div class="font-medium text-gray-900">{{ $device->user->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $device->user->email }}</div>
                                        @else
                                            <span class="text-gray-500">Not attached</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">{{ $device->platform }}</td>
                                    <td class="px-4 py-3">{{ $device->xray_email }}</td>
                                    <td class="px-4 py-3">{{ $device->download_bytes }}</td>
                                    <td class="px-4 py-3">{{ $device->upload_bytes }}</td>
                                    <td class="px-4 py-3">{{ $device->last_seen_at }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-2">
                                            <a href="{{ route('admin.devices.edit', $device) }}"
                                               class="inline-flex items-center px-3 py-2 bg-gray-800 border border-transparent rounded-md text-xs font-semibold text-white hover:bg-gray-700">
                                                Edit
                                            </a>

                                            <form method="POST" action="{{ route('admin.devices.reset-traffic', $device) }}">
                                                @csrf
                                                <button type="submit"
                                                    class="inline-flex items-center px-3 py-2 bg-amber-500 border border-transparent rounded-md text-xs font-semibold text-white hover:bg-amber-600">
                                                    Reset Traffic
                                                </button>
                                            </form>

                                            @if($device->user_id)
                                                <form method="POST" action="{{ route('admin.devices.detach-user', $device) }}">
                                                    @csrf
                                                    <button type="submit"
                                                        class="inline-flex items-center px-3 py-2 bg-slate-600 border border-transparent rounded-md text-xs font-semibold text-white hover:bg-slate-700">
                                                        Detach User
                                                    </button>
                                                </form>
                                            @endif

                                            <form method="POST" action="{{ route('admin.devices.destroy', $device) }}"
                                                  onsubmit="return confirm('Delete device?')">
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
                                    <td colspan="9" class="px-4 py-6 text-center text-gray-500">
                                        No devices found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-6">
                        {{ $devices->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
