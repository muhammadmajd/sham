<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Subscriptions
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="text-sm text-gray-500">
                        Manage user subscriptions and assigned plans
                    </div>

                    <a href="{{ route('admin.subscriptions.create') }}"
                       class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                        Assign Subscription
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
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">User</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Plan</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Started At</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Ends At</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($subscriptions as $subscription)
                                <tr>
                                    <td class="px-4 py-3">{{ $subscription->id }}</td>
                                    <td class="px-4 py-3">
                                        @if($subscription->user)
                                            <div class="font-medium text-gray-900">{{ $subscription->user->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $subscription->user->email }}</div>
                                        @else
                                            <span class="text-gray-500">No user</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        {{ $subscription->plan?->name ?? 'No plan' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        @php
                                            $status = strtolower((string) $subscription->status);
                                            $statusClasses = match($status) {
                                                'active' => 'bg-green-100 text-green-700',
                                                'expired' => 'bg-red-100 text-red-700',
                                                'canceled', 'cancelled' => 'bg-red-100 text-red-700',
                                                'pending' => 'bg-amber-100 text-amber-700',
                                                default => 'bg-gray-100 text-gray-700',
                                            };
                                        @endphp

                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusClasses }}">
                                            {{ $subscription->status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">{{ $subscription->started_at }}</td>
                                    <td class="px-4 py-3">{{ $subscription->ends_at }}</td>
                                    <td class="px-4 py-3">{{ $subscription->notes }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-6 text-center text-gray-500">
                                        No subscriptions found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-6">
                        {{ $subscriptions->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
