<x-app-layout>
    <x-dashboard.sidebar>
        <x-alert-information></x-alert-information>

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mt-10 w-full gap-4">
            <h2 class="font-bold text-xl sm:text-2xl whitespace-nowrap truncate">
                Incident List
            </h2>

            <div class="flex flex-wrap gap-3 items-center">
                <form id="filterForm" method="GET" action="{{ route('incidents.completed') }}"
                    class="flex gap-2 items-center" onsubmit="showFullScreenLoader();">
                    <x-date-filter-dropdown :action="route('incidents.completed')" :startDate="request('start_date')" :endDate="request('end_date')" formId="filterForm" />

                    <a href="{{ route('incidents.exportCompleted', array_merge(request()->query(), ['export' => 1])) }}"
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

        <div class="relative overflow-x-auto shadow-md sm:rounded-lg mt-5" id="incident-list">
            <input class="search hidden" />
            <table
                class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs sm:text-sm text-center text-gray-600 dark:text-gray-300">
                <thead
                    class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-700 dark:text-gray-400 whitespace-nowrap">
                    <tr>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="no">No</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="id">Incident Id</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="report">Reported By</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="department">Report To</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="equipment">Equipment</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="location">Location</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="date">Date</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="staff">PIC Staff</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="status">Status</th>
                        <th class="px-4 py-2 md:px-6 md:py-3">Action</th>
                    </tr>
                </thead>
                <tbody class="list whitespace-nowrap">
                    @forelse ($incidents as $incident)
                        <tr
                            class="border-b dark:border-gray-700 {{ $loop->odd ? 'bg-white dark:bg-gray-900' : 'bg-gray-50 dark:bg-gray-800' }}">
                            <td class="px-4
                            py-2 md:px-6 md:py-3 no">{{ $loop->iteration }}
                            </td>
                            <td class="px-4 py-2 md:px-6 md:py-3 id">{{ $incident->unique_id ?? '-' }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 report">{{ $incident->user->name ?? '-' }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 department">{{ $incident->department->name ?? '-' }}
                            </td>
                            <td class="px-4 py-2 md:px-6 md:py-3 equipment">
                                {{ ucfirst(strtolower($incident->equipment->item->name ?? '-')) }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 location">{{ $incident->store->site_code ?? '-' }}
                            </td>
                            <td class="px-4 py-2 md:px-6 md:py-3 date">
                                {{ \Carbon\Carbon::parse($incident->created_at)->format('d M Y') }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 staff">{{ $incident->picUser->name ?? '-' }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 status">
                                @php
                                    $status = strtolower($incident->status);
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
                                    {{ ucfirst($incident->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-2 md:px-6 md:py-3">
                                <div class="flex justify-center gap-2">
                                    <a href="{{ route('incidents.showCompletedDetail', $incident->id) }}"
                                        onclick="showFullScreenLoader();"
                                        class="text-xs sm:text-sm px-3 py-1.5 sm:px-4 sm:py-2 bg-purple-500 text-white rounded-md hover:bg-purple-600">
                                        Detail
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4 text-xs">No incident data found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-6 px-4 pb-3 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div>
                    {{ $incidents->appends(request()->query())->links() }}
                </div>
                <div class="flex items-center gap-4 flex-wrap justify-end">
                    <form method="GET" action="{{ route('incidents.completed') }}"
                        onsubmit="showFullScreenLoader();">
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                        <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                        <div class="flex items-center gap-1">
                            <label for="per_page" class="text-sm text-gray-600 dark:text-gray-300">Show</label>
                            <select name="per_page" id="per_page" onchange="this.form.submit()"
                                class="text-sm w-16 px-2 py-1 rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-purple-500">
                                <option value="5" {{ ($perPage ?? 5) == 5 ? 'selected' : '' }}>5</option>
                                <option value="10" {{ ($perPage ?? 5) == 10 ? 'selected' : '' }}>10</option>
                                <option value="20" {{ ($perPage ?? 5) == 20 ? 'selected' : '' }}>20</option>
                                <option value="50" {{ ($perPage ?? 5) == 50 ? 'selected' : '' }}>50</option>
                            </select>
                            <span class="text-sm text-gray-600 dark:text-gray-400">per page</span>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @push('scripts')
            <script>
                const incidentList = new List('incident-list', {
                    valueNames: ['id', 'no', 'department', 'location', 'report', 'date', 'status', 'equipment', 'staff']
                });
            </script>
        @endpush

    </x-dashboard.sidebar>
</x-app-layout>
