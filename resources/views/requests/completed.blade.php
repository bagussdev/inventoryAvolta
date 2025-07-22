<x-app-layout>
    <x-dashboard.sidebar>
        <x-alert-information></x-alert-information>

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mt-5 sm:mt-10 w-full gap-4">
            <h2 class="font-bold text-xl sm:text-2xl whitespace-nowrap truncate">
                Request List
            </h2>

            <div class="flex flex-wrap gap-3 items-center">
                <form id="filterForm" method="GET" action="{{ route('requests.completed') }}"
                    class="flex gap-2 items-center" onsubmit="showFullScreenLoader();">
                    <x-date-filter-dropdown :action="route('requests.completed')" :startDate="request('start_date')" :endDate="request('end_date')" formId="filterForm" />

                    <a href="{{ route('requests.exportCompleted', array_merge(request()->query(), ['export' => 1])) }}"
                        class="text-xs sm:text-sm px-3 py-1.5 sm:px-4 sm:py-2 text-white bg-gray-600 hover:bg-gray-700 focus:ring-4 focus:ring-gray-300 font-medium rounded-md focus:outline-none dark:bg-gray-500 dark:hover:bg-gray-600 dark:focus:ring-gray-700 text-center">
                        Excel
                    </a>

                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..."
                        class="text-xs sm:text-sm px-2 py-1.5 sm:px-3 sm:py-2 rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-purple-500 w-full sm:w-44" />
                    <input type="hidden" name="per_page" value="{{ $perPage ?? 5 }}">
                    <button type="submit"
                        class="text-xs sm:text-sm px-3 py-1.5 sm:px-3 sm:py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition">
                        Search
                    </button>
                </form>
            </div>
        </div>

        <hr class="h-[3px] my-8 bg-gray-200 border-0 dark:bg-gray-700 w-full">

        <div class="relative overflow-x-auto shadow-md sm:rounded-lg mt-5" id="request-list">
            <table
                class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs sm:text-sm text-center text-gray-600 dark:text-gray-300">
                <thead
                    class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-700 dark:text-gray-400 whitespace-nowrap">
                    <tr>
                        <th class="px-5 py-4 md:px-6 md:py-5 cursor-pointer sort" data-sort="no">No</th>
                        <th class="px-5 py-4 md:px-6 md:py-5 cursor-pointer sort" data-sort="id">Request ID</th>
                        <th class="px-5 py-4 md:px-6 md:py-5 cursor-pointer sort" data-sort="report">User By</th>
                        <th class="px-5 py-4 md:px-6 md:py-5 cursor-pointer sort" data-sort="department">Report To</th>
                        <th class="px-5 py-4 md:px-6 md:py-5 cursor-pointer sort" data-sort="item">Item Req</th>
                        <th class="px-5 py-4 md:px-6 md:py-5 cursor-pointer sort" data-sort="item">qty</th>
                        <th class="px-5 py-4 md:px-6 md:py-5 cursor-pointer sort" data-sort="location">Location</th>
                        <th class="px-5 py-4 md:px-6 md:py-5 cursor-pointer sort" data-sort="date">Start</th>
                        <th class="px-5 py-4 md:px-6 md:py-5 cursor-pointer sort" data-sort="date">End</th>
                        <th class="px-5 py-4 md:px-6 md:py-5 cursor-pointer sort" data-sort="status">Status</th>
                        <th class="px-5 py-4 md:px-6 md:py-5">Action</th>
                    </tr>
                </thead>
                <tbody class="list whitespace-nowrap">
                    @forelse ($requests as $request)
                        <tr
                            class="border-b dark:border-gray-700 {{ $loop->odd ? 'bg-white dark:bg-gray-900' : 'bg-gray-50 dark:bg-gray-800' }}">
                            <td class="px-5 py-4 md:px-6 md:py-5 no">{{ $loop->iteration }}</td>
                            <td class="px-5 py-4 md:px-6 md:py-5 id">
                                <a href="{{ route('requests.show', $request->id) }}"
                                    class="text-blue-600 hover:underline">{{ $request->unique_id }}
                                </a>
                            </td>
                            <td class="px-5 py-4 md:px-6 md:py-5 report">{{ $request->user->name ?? '-' }}</td>
                            <td class="px-5 py-4 md:px-6 md:py-5 department">{{ $request->department->name ?? '-' }}
                            </td>
                            <td class="px-5 py-4 md:px-6 md:py-5 item">{{ $request->item_request }}</td>
                            <td class="px-5 py-4 md:px-6 md:py-5 item">{{ $request->qty }}</td>
                            <td class="px-5 py-4 md:px-6 md:py-5 location">{{ $request->store->site_code ?? '-' }}</td>
                            <td class="px-5 py-4 md:px-6 md:py-5 date">
                                {{ \Carbon\Carbon::parse($request->created_at)->format('d M Y') }}</td>
                            <td class="px-5 py-4 md:px-6 md:py-5 date">
                                {{ \Carbon\Carbon::parse($request->resolved_at)->format('d M Y') }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 status">
                                @php
                                    $status = strtolower($request->status);
                                    $color = match ($status) {
                                        'resolved', 'completed' => 'bg-green-100 text-green-800',
                                        'in progress' => 'bg-blue-100 text-blue-800',
                                        'pending' => 'bg-red-100 text-red-800',
                                        'waiting' => 'bg-yellow-100 text-yellow-600',
                                        default => 'bg-gray-100 text-gray-600',
                                    };
                                @endphp
                                <span
                                    class="inline-block px-3 py-1 text-xs font-medium rounded-md {{ $color }}">
                                    {{ ucfirst($request->status) }}
                                </span>
                            </td>
                            </td>
                            <td class="px-5 py-4 md:px-6 md:py-5">
                                <div class="flex flex-col sm:flex-row flex-wrap gap-2 justify-center items-center">
                                    <x-buttons.action-button text="Detail" color="purple"
                                        href="{{ route('requests.showCompletedDetail', $request->id) }}"
                                        onclick="showFullScreenLoader();" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="text-center">
                            <td colspan="11" class="py-6 text-gray-500 dark:text-gray-400 text-sm">
                            No request completed data available.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-per-page-selector :items="$requests" route="requests.completed" :perPage="$perPage" :showPagination="true" />
        @push('scripts')
            <script>
                const requestList = new List('request-list', {
                    valueNames: ['no', 'id', 'report', 'department', 'item', 'location', 'date', 'status']
                });
            </script>
        @endpush

    </x-dashboard.sidebar>
</x-app-layout>
