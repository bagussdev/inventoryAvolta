<x-app-layout>
    <x-dashboard.sidebar>
        <x-alert-information />

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mt-5 sm:mt-10 w-full gap-4">
            <h2 class="font-bold text-xl sm:text-2xl whitespace-nowrap truncate">
                Log Notification
            </h2>

            <div class="flex flex-wrap gap-3 items-center">
                <form id="filterForm" method="GET" action="{{ route('notifications.index') }}"
                    class="flex gap-2 w-full sm:w-auto" onsubmit="showFullScreenLoader();">

                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..."
                        class="text-xs sm:text-sm px-2 py-1.5 sm:px-3 sm:py-2 rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-purple-500 w-full sm:w-44" />
                    <input type="hidden" name="per_page" value="{{ $perPage ?? 10 }}">
                    <button type="submit"
                        class="text-xs sm:text-sm px-3 py-1.5 sm:px-3 sm:py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition">
                        Search
                    </button>
                </form>
            </div>
        </div>

        <hr class="h-[3px] my-8 bg-gray-200 border-0 dark:bg-gray-700 w-full">

        <div class="w-full overflow-x-auto">
            <div class="min-w-full inline-block align-middle">
                <div class="overflow-hidden shadow sm:rounded-lg" id="notification-list">
                    <table
                        class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs sm:text-sm text-center text-gray-600 dark:text-gray-300">
                        <thead
                            class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-700 dark:text-gray-400 whitespace-nowrap">
                            <tr>
                                <th class="px-4 py-2 cursor-pointer sort" data-sort="no">No</th>
                                <th class="px-4 py-2 cursor-pointer sort" data-sort="time">Time</th>
                                <th class="px-4 py-2 cursor-pointer sort" data-sort="sender">Sender</th>
                                <th class="px-4 py-2 cursor-pointer sort" data-sort="role">Role</th>
                                <th class="px-4 py-2 cursor-pointer sort" data-sort="department">Department</th>
                                <th class="px-4 py-2 cursor-pointer sort" data-sort="store">Store</th>
                                <th class="px-4 py-2 cursor-pointer sort" data-sort="type">Type</th>
                                <th class="px-4 py-2 cursor-pointer sort" data-sort="message">Message</th>
                                <th class="px-4 py-2 cursor-pointer sort" data-sort="reference">Reference</th>
                            </tr>
                        </thead>
                        <tbody
                            class="list whitespace-nowrap bg-white divide-y divide-gray-200 dark:divide-gray-700 dark:bg-gray-900">
                            @forelse ($notifications as $notif)
                                <tr>
                                    <td class="px-4 py-2 no">{{ $loop->iteration + ($notifications->firstItem() - 1) }}
                                    </td>
                                    <td class="px-4 py-2 time">{{ $notif->created_at->format('d M Y H:i') }}</td>
                                    <td class="px-4 py-2 sender">{{ $notif->triggeredBy->name ?? '-' }}</td>
                                    <td class="px-4 py-2 role">{{ $notif->role->name ?? '-' }}</td>
                                    <td class="px-4 py-2 department">{{ $notif->department->name ?? '-' }}</td>
                                    <td class="px-4 py-2 store">{{ $notif->store->name ?? '-' }}</td>
                                    <td
                                        class="px-4 py-2 title font-semibold text-purple-600 dark:text-purple-400 text-left">
                                        {{ $notif->title }}</td>
                                    <td class="px-4 py-2 message text-left">{!! $notif->message !!}</td>
                                    <td class="px-4 py-2 reference">
                                        @php
                                            $refType = ucfirst($notif->reference_type);
                                            $refId = $notif->reference_id;

                                            $refLink = match ($notif->reference_type) {
                                                'incidents' => route('incidents.show', $refId),
                                                'requests' => route('requests.show', $refId),
                                                'maintenances' => route('maintenances.show', $refId),
                                                'transactions' => route('transactions.show', $refId),
                                                'items' => route('items.show', $refId),
                                                'equipments' => route('equipments.show', $refId),
                                                default => null,
                                            };
                                        @endphp

                                        @if ($refLink)
                                            <a href="{{ $refLink }}" target="_blank"
                                                class="text-blue-600 underline text-xs">
                                                {{ $refType }} #{{ $refId }}
                                            </a>
                                        @else
                                            <span class="text-gray-400 text-xs">-</span>
                                        @endif
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-xs">No notifications found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <x-per-page-selector :items="$notifications" route="notifications.index" :perPage="$perPage" :search="request('search')"
            :showPagination="true" />

        @push('scripts')
            <script>
                const notifList = new List('notification-list', {
                    valueNames: ['no', 'time', 'sender', 'reference', 'role', 'department', 'store', 'type', 'title',
                        'message'
                    ]
                });
            </script>
        @endpush

    </x-dashboard.sidebar>
</x-app-layout>
