<x-app-layout>
    <x-dashboard.sidebar>
        <x-alert-information></x-alert-information>

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mt-10 w-full gap-4">
            <h2 class="font-bold text-xl sm:text-2xl whitespace-nowrap truncate">
                Maintenance List
            </h2>

            <div class="flex flex-wrap gap-3 items-center">
                {{-- Form for Search, Filter & Pagination --}}
                <form id="filterForm" method="GET" action="{{ route('maintenances.index') }}"
                    class="flex gap-2 w-full sm:w-auto" onsubmit="showFullScreenLoader();">

                    {{-- PENGGUNAAN KOMPONEN DATE FILTER DROPDOWN DI SINI --}}
                    <x-date-filter-dropdown :action="route('maintenances.index')" :startDate="request('start_date')" :endDate="request('end_date')" formId="filterForm" />
                    <a href="{{ route('maintenances.export', array_merge(request()->query(), ['export' => 1])) }}"
                        class="text-xs sm:text-sm px-3 py-1.5 sm:px-4 sm:py-2 text-white bg-gray-600 hover:bg-gray-700 focus:ring-4 focus:ring-gray-300 font-medium rounded-md focus:outline-none dark:bg-gray-500 dark:hover:bg-gray-600 dark:focus:ring-gray-700 text-center">
                        Excel
                    </a>
                    {{-- Input search yang sudah ada --}}
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..."
                        class="text-xs sm:text-sm px-2 py-1.5 sm:px-3 sm:py-2 rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-purple-500 w-full sm:w-44" />
                    <input type="hidden" name="per_page" value="{{ $perPage ?? 5 }}">
                    <button type="submit" onsubmit="showFullScreenLoader();"
                        class="text-xs sm:text-sm px-3 py-1.5 sm:px-3 sm:py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition">
                        Search
                    </button>
                </form>
            </div>
        </div>

        <hr class="h-[3px] my-8 bg-gray-200 border-0 dark:bg-gray-700 w-full">

        <div class="relative overflow-x-auto shadow-md sm:rounded-lg mt-5" id="maintenance-list">
            <input class="search hidden" />
            <table class="min-w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400 text-center">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th class="px-5 py-4 cursor-pointer sort" data-sort="no">No</th>
                        <th class="px-5 py-4 cursor-pointer sort" data-sort="id">Maintenance Id</th>
                        <th class="px-5 py-4 cursor-pointer sort" data-sort="name">Equipment</th>
                        <th class="px-5 py-4 cursor-pointer sort" data-sort="model">Model</th>
                        <th class="px-5 py-4 cursor-pointer sort" data-sort="brand">Brand</th>
                        <th class="px-5 py-4 cursor-pointer sort" data-sort="store">Location</th>
                        <th class="px-5 py-4 cursor-pointer sort" data-sort="freq">Frequency</th>
                        <th class="px-5 py-4 cursor-pointer sort" data-sort="date">Maintenance Date</th>
                        <th class="px-5 py-4 cursor-pointer sort" data-sort="status">Status</th>
                        <th class="px-5 py-4 cursor-pointer sort">Action</th>
                    </tr>
                </thead>
                <tbody class="list">
                    @forelse ($maintenances as $maintenance)
                        <tr
                            class="border-b dark:border-gray-700 {{ $loop->odd ? 'bg-white dark:bg-gray-900' : 'bg-gray-50 dark:bg-gray-800' }}">
                            <td class="px-5 py-4 no">{{ $loop->iteration }}</td>
                            <td class="px-5 py-4 id">{{ $maintenance->id ?? '-' }}</td>
                            <td class="px-5 py-4 name">{{ $maintenance->equipment->item->name ?? '-' }}</td>
                            <td class="px-5 py-4 model">{{ $maintenance->equipment->item->model ?? '-' }}</td>
                            <td class="px-5 py-4 brand">{{ $maintenance->equipment->item->brand ?? '-' }}</td>
                            <td class="px-5 py-4 store">{{ $maintenance->equipment->store->site_code ?? '-' }}</td>
                            <td class="px-5 py-4 freq">{{ ucfirst($maintenance->frequensi) }}</td>
                            <td class="px-5 py-4 date">
                                {{ \Carbon\Carbon::parse($maintenance->maintenance_date)->format('d M Y') }}</td>
                            <td class="px-5 py-4 status">
                                @php
                                    $status = strtolower($maintenance->status);
                                    $color = match ($status) {
                                        'completed' => 'bg-green-100 text-green-800',
                                        'maintenance' => 'bg-red-100 text-red-600',
                                        'in progress' => 'bg-blue-100 text-blue-800',
                                        'resolved' => 'bg-purple-100 text-purple-800',
                                        default => 'bg-gray-100 text-gray-600',
                                    };
                                @endphp
                                <span
                                    class="inline-block px-2 py-0.5 sm:px-3 sm:py-1 text-[12px] sm:text-sm font-semibold rounded-md {{ $color }}">
                                    {{ ucfirst($maintenance->status) }}
                                </span>
                            </td>
                            <td class="px-3 py-2">
                                <div class="flex flex-col sm:flex-row flex-wrap gap-2 justify-center">
                                    @if ($maintenance->status === 'maintenance')
                                        <form action="{{ route('maintenances.proses', $maintenance->id) }}"
                                            method="GET" onsubmit="return confirmAndLoad('Are you sure to process?')">
                                            @can('maintenance.proses')
                                                <button type="submit"
                                                    class="px-4 py-1 bg-green-500 text-white rounded-md hover:bg-green-600 text-sm w-full sm:w-auto">Proses</button>
                                            @endcan
                                        </form>
                                    @elseif ($maintenance->status === 'completed')
                                        @can('maintenance.edit')
                                            <a href="{{ route('maintenances.editCompleted', $maintenance->id) }}"
                                                onclick="showFullScreenLoader();"
                                                class="px-4 py-1 bg-blue-500 text-white rounded-md hover:bg-blue-600 text-sm w-full sm:w-auto">Edit</a>
                                        @endcan
                                    @elseif ($maintenance->status === 'in progress')
                                        @can('maintenance.confirm')
                                            <a href="{{ route('maintenances.confirm', $maintenance->id) }}"
                                                onclick="showFullScreenLoader();"
                                                class="px-4 py-1 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm w-full sm:w-auto">Confirm</a>
                                        @endcan
                                    @elseif ($maintenance->status === 'resolved')
                                        @can('maintenance.closed')
                                            <form action="{{ route('maintenances.closed', $maintenance->id) }}"
                                                method="GET" onsubmit="return confirmAndLoad('Are you sure to closed?')">
                                                <button type="submit"
                                                    class="px-4 py-1 bg-red-500 text-white rounded-md hover:bg-red-600 text-sm w-full sm:w-auto">Closed</button>
                                            </form>
                                        @endcan
                                    @else
                                        @can('schedulemaintenance.edit')
                                            <a href="{{ route('maintenances.edit', $maintenance->id) }}"
                                                onclick="showFullScreenLoader();"
                                                class="px-4 py-1 bg-blue-500 text-white rounded-md hover:bg-blue-600 text-sm w-full sm:w-auto">Edit</a>
                                        @endcan
                                    @endif
                                    <a href="{{ route('maintenances.show', $maintenance->id) }}"
                                        onclick="showFullScreenLoader();"
                                        class="px-4 py-1 bg-purple-500 text-white rounded-md hover:bg-purple-600 text-sm w-full sm:w-auto">Detail</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">No maintenance data found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-6 px-4 pb-3 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div>
                    {{ $maintenances->appends(request()->query())->links() }}
                </div>
                <div class="flex items-center gap-4 flex-wrap justify-end">
                    <form method="GET" action="{{ route('maintenances.index') }}">
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
                const maintenanceList = new List('maintenance-list', {
                    valueNames: ['no', 'id', 'name', 'brand', 'store', 'freq', 'date', 'status', 'model']
                });
            </script>
        @endpush

    </x-dashboard.sidebar>
</x-app-layout>
