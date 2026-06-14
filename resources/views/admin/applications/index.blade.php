<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Applications
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <form method="GET" class="w-full md:w-96">
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search applications..."
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </form>

                    <a href="{{ route('admin.applications.create') }}"
                        class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                        Add Application
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
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">App ID</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Type</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($applications as $application)
                                <tr>
                                    <td class="px-4 py-3">{{ $application->id }}</td>
                                    <td class="px-4 py-3">{{ $application->name }}</td>
                                    <td class="px-4 py-3 font-mono text-xs">{{ $application->app_id }}</td>
                                    <td class="px-4 py-3">{{ $application->type }}</td>
                                    <td class="px-4 py-3">
                                        <form method="POST" action="{{ route('admin.applications.toggle-status', $application) }}">
                                            @csrf
                                            <button type="submit"
                                                class="group inline-flex items-center gap-2 rounded-full border border-transparent px-2 py-1 text-xs font-semibold transition hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 {{ $application->status === 'active' ? 'bg-green-50 text-green-700 focus:ring-green-500' : 'bg-red-50 text-red-700 focus:ring-red-500' }}"
                                                title="Toggle {{ $application->name }} status">
                                                <span class="relative inline-flex h-5 w-9 shrink-0 items-center rounded-full transition {{ $application->status === 'active' ? 'bg-green-600' : 'bg-red-600' }}">
                                                    <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition {{ $application->status === 'active' ? 'translate-x-4' : 'translate-x-1' }}"></span>
                                                </span>
                                                <span>
                                                    {{ ucfirst($application->status) }}
                                                </span>
                                            </button>
                                        </form>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-2">
                                            <a href="{{ route('admin.applications.edit', $application) }}"
                                                class="inline-flex items-center px-3 py-2 bg-gray-800 border border-transparent rounded-md text-xs font-semibold text-white hover:bg-gray-700">
                                                Edit
                                            </a>

                                            <form method="POST" action="{{ route('admin.applications.destroy', $application) }}"
                                                onsubmit="return confirm('Delete application?')">
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
                                    <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                                        No applications found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-6">
                        {{ $applications->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
