<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Servers
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="text-sm text-gray-500">
                        Manage VPN servers and connection settings
                    </div>

                    <a href="{{ route('admin.servers.create') }}"
                       class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                        Create Server
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
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Name</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Host</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Port</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Country</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Type</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Payment</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Public</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Available</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($servers as $server)
                                <tr>
                                    <td class="px-4 py-3">{{ $server->id }}</td>
                                    <td class="px-4 py-3">{{ $server->name }}</td>
                                    <td class="px-4 py-3">{{ $server->host }}</td>
                                    <td class="px-4 py-3">{{ $server->port }}</td>
                                    <td class="px-4 py-3">{{ $server->country }}</td>
                                    <td class="px-4 py-3">{{ $server->ptype }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $server->server_Payment_type === 'paid' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-700' }}">
                                            {{ $server->server_Payment_type }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($server->public)
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">Yes</span>
                                        @else
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-700">No</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($server->available)
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">Yes</span>
                                        @else
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700">No</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-2">
                                            <a href="{{ route('admin.servers.edit', $server) }}"
                                               class="inline-flex items-center px-3 py-2 bg-gray-800 border border-transparent rounded-md text-xs font-semibold text-white hover:bg-gray-700">
                                                Edit
                                            </a>

                                            <form method="POST" action="{{ route('admin.servers.destroy', $server) }}"
                                                  onsubmit="return confirm('Delete server?')">
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
                                    <td colspan="10" class="px-4 py-6 text-center text-gray-500">
                                        No servers found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-6">
                        {{ $servers->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
