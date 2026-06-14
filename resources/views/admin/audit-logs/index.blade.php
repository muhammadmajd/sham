<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Audit Logs
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="text-sm text-gray-500">
                        Review administrative and system actions
                    </div>

                    <form method="POST" action="{{ route('admin.audit-logs.clear') }}"
                        onsubmit="return confirm('Clear all audit logs? This cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="inline-flex items-center justify-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                            Clear Logs
                        </button>
                    </form>
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
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">User</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Action</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Entity Type</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Entity ID</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Meta</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Created At</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($logs as $log)
                                <tr class="align-top">
                                    <td class="px-4 py-3">{{ $log->id }}</td>
                                    <td class="px-4 py-3">
                                        @if($log->user)
                                            <div class="font-medium text-gray-900">{{ $log->user->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $log->user->email }}</div>
                                        @else
                                            <span class="text-gray-500">System / Unknown</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-700">
                                            {{ $log->action }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">{{ $log->entity_type }}</td>
                                    <td class="px-4 py-3">{{ $log->entity_id }}</td>
                                    <td class="px-4 py-3">
                                        <pre class="text-xs bg-gray-50 rounded-lg p-3 border border-gray-200 overflow-x-auto whitespace-pre-wrap">{{ is_array($log->meta) ? json_encode($log->meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $log->meta }}</pre>
                                    </td>
                                    <td class="px-4 py-3">{{ $log->created_at }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-6 text-center text-gray-500">
                                        No logs found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-6">
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
