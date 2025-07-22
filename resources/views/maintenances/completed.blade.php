<x-app-layout>
    <x-dashboard.sidebar>
        <x-alert-information></x-alert-information>

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mt-5 sm:mt-10 w-full gap-4">
            <h2 class="font-bold text-xl sm:text-2xl whitespace-nowrap truncate">
                Maintenance Completed List
            </h2>

            <div class="flex flex-wrap gap-3 items-center">
                {{-- PERBAIKAN: Action form mengarah ke route completed --}}
                <form id="filterForm" method="GET" action="{{ route('maintenances.completed') }}"
                    class="flex gap-2 w-full sm:w-auto" onsubmit="showFullScreenLoader();">
                    {{-- PENGGUNAAN KOMPONEN DATE FILTER DROPDOWN DI SINI --}}
                    <x-date-filter-dropdown :action="route('maintenances.completed')" :startDate="request('start_date')" :endDate="request('end_date')" formId="filterForm" />

                    {{-- Export to Excel Button --}}
                    <a href="{{ route('maintenances.completed.export', array_merge(request()->query(), ['export' => 1])) }}"
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

        <div class="w-full overflow-x-auto">
            <div class="min-w-full inline-block align-middle">
                <div class="overflow-hidden shadow sm:rounded-lg" id="maintenance-list">
                    <table
                        class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs sm:text-sm text-center text-gray-600 dark:text-gray-300">
                        <thead
                            class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-700 dark:text-gray-400 whitespace-nowrap">
                            <tr>
                                <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="no">No</th>
                                <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="no">Maintenance ID
                                </th>
                                <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="name">Equipment
                                </th>
                                <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="model">S/N</th>
                                <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="store">Store</th>
                                <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="date">Date</th>
                                <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="freq">Frequency
                                </th>
                                <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="status">Resolved At
                                </th>
                                <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="status">PIC Staff
                                </th>
                                <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="status">Status</th>
                                <th class="px-4 py-2 md:px-6 md:py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody
                            class="list whitespace-nowrap bg-white divide-y divide-gray-200 dark:divide-gray-700 dark:bg-gray-900">
                            @forelse ($maintenances as $maintenance)
                                <tr class="border-b dark:border-gray-700 {{ $loop->odd ? 'bg-white dark:bg-gray-900' : 'bg-gray-50 dark:bg-gray-800' }}">
                                    <td class="px-4 py-2 md:px-6 md:py-3 no">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-2 md:px-6 md:py-3 no">
                                        <a href="{{ route('maintenances.show', $maintenance->id) }}"
                                            class="text-blue-600 hover:underline">
                                            MNT-000{{ ucfirst(strtolower($maintenance->id ?? '-')) }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-2 md:px-6 md:py-3 name">
                                        {{ ucwords(
                                            strtolower(
                                                optional(optional($maintenance->equipment)->item)->name .
                                                    (!empty($maintenance->equipment->alias) || !empty($maintenance->item_description)
                                                        ? ' - ' . ($maintenance->equipment->alias ?? $maintenance->item_description)
                                                        : ''),
                                            ),
                                        ) }}
                                    </td>
                                    <td class="px-4 py-2 md:px-6 md:py-3 model">
                                        {{ $maintenance->equipment->serial_number ?? '-' }}</td>
                                    <td class="px-4 py-2 md:px-6 md:py-3 store">
                                        {{ $maintenance->equipment->store->name ?? '-' }}</td>
                                    <td class="px-4 py-2 md:px-6 md:py-3 date">
                                        {{ \Carbon\Carbon::parse($maintenance->maintenance_date)->format('d M Y') }}
                                    </td>
                                    <td class="px-4 py-2 md:px-6 md:py-3 freq">{{ ucfirst($maintenance->frequensi) }}
                                    </td>

                                    <td class="px-4 py-2 md:px-6 md:py-3 date">
                                        {{ \Carbon\Carbon::parse($maintenance->resolved_at)->format('d M Y H:i') }}
                                    </td>
                                    <td class="px-4 py-2 md:px-6 md:py-3 date">
                                        {{ $maintenance->staff->name }}
                                    </td>
                                    <td class="px-4 py-2 md:px-6 md:py-3 status">
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
                                        <span class="px-3 py-1 text-xs font-medium rounded-md {{ $color }}">
                                            {{ ucfirst($maintenance->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 md:px-6 md:py-3">
                                        <div class="flex justify-center gap-2">
                                            <a href="{{ route('maintenances.showCompletedDetail', $maintenance->id) }}"
                                                onclick="showFullScreenLoader();"
                                                class="text-xs sm:text-sm px-3 py-1.5 sm:px-4 sm:py-2 bg-purple-500 text-white rounded-md hover:bg-purple-600">Detail</a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center py-4 text-xs">No incident data found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <x-per-page-selector :items="$maintenances" route="maintenances.completed" :perPage="$perPage" :search="$search"
            :showPagination="true" />

        @push('scripts')
            <script>
                const maintenanceList = new List('maintenance-list', {
                    valueNames: ['no', 'name', 'model', 'store', 'date', 'freq', 'status']
                });
            </script>
        @endpush

    </x-dashboard.sidebar>
</x-app-layout>
