<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Plans
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
                            placeholder="Search plan name..."
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                    </form>

                    <a href="{{ route('admin.plans.create') }}"
                       class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                        Create Plan
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
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Key</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Name</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Price Cents</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Currency</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Interval</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Traffic Limit</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($plans as $plan)
                                <tr>
                                    <td class="px-4 py-3">{{ $plan->id }}</td>
                                    <td class="px-4 py-3">{{ $plan->key }}</td>
                                    <td class="px-4 py-3">{{ $plan->name }}</td>
                                    <td class="px-4 py-3">{{ $plan->price_cents }}</td>
                                    <td class="px-4 py-3">{{ $plan->currency }}</td>
                                    <td class="px-4 py-3">{{ $plan->interval }}</td>
                                    <td class="px-4 py-3">{{ $plan->traffic_limit }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-2">
                                            <a href="{{ route('admin.plans.edit', $plan) }}"
                                               class="inline-flex items-center px-3 py-2 bg-gray-800 border border-transparent rounded-md text-xs font-semibold text-white hover:bg-gray-700">
                                                Edit
                                            </a>

                                            <form method="POST" action="{{ route('admin.plans.destroy', $plan) }}"
                                                  onsubmit="return confirm('Delete plan?')">
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
                                    <td colspan="8" class="px-4 py-6 text-center text-gray-500">
                                        No plans found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-6">
                        {{ $plans->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
