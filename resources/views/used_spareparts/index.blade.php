<x-app-layout>
    <x-dashboard.sidebar>
        <x-alert-information></x-alert-information>

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mt-10 w-full gap-4">
            <h2 class="font-bold text-xl sm:text-2xl whitespace-nowrap truncate">
                Used Spareparts List
            </h2>

            <div class="flex flex-wrap gap-3 items-center">
                {{-- PERBAIKAN: Form ini sekarang memiliki ID untuk JS --}}
                <form id="filterForm" method="GET" action="{{ route('sparepartused.index') }}"
                    onsubmit="showFullScreenLoader();" class="flex gap-2 items-center">

                    {{-- PENGGUNAAN KOMPONEN DATE FILTER DROPDOWN DI SINI --}}
                    <x-date-filter-dropdown :action="route('sparepartused.index')" :startDate="request('start_date')" :endDate="request('end_date')" formId="filterForm" />

                    {{-- Tombol Export di dalam form --}}
                    <a href="{{ route('sparepartused.export', array_merge(request()->query(), ['export' => 1])) }}"
                        class="text-xs sm:text-sm px-3 py-1.5 sm:px-4 sm:py-2 text-white bg-gray-600 hover:bg-gray-700 focus:ring-4 focus:ring-gray-300 font-medium rounded-md focus:outline-none dark:bg-gray-500 dark:hover:bg-gray-600 dark:focus:ring-gray-700 text-center">
                        Excel
                    </a>

                    {{-- Input search yang sudah ada --}}
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by item..."
                        class="text-xs sm:text-sm px-2 py-1.5 sm:px-3 sm:py-2 rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-purple-500 w-36 sm:w-44" />
                    <input type="hidden" name="per_page" value="{{ $perPage ?? 5 }}">
                    <button type="submit" onsubmit="showFullScreenLoader();"
                        class="text-xs sm:text-sm px-3 py-1.5 sm:px-3 sm:py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition">
                        Search
                    </button>
                </form>
            </div>
        </div>

        <hr class="h-[3px] my-8 bg-gray-200 border-0 dark:bg-gray-700 w-full">

        <div class="relative overflow-x-auto shadow-md sm:rounded-lg mt-5" id="used-spareparts-list">
            <input class="search hidden" />
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400 text-center">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-4 cursor-pointer sort" data-sort="item">No</th>
                        <th scope="col" class="px-6 py-4 cursor-pointer sort" data-sort="date">date</th>
                        <th scope="col" class="px-6 py-4 cursor-pointer sort" data-sort="item">Item</th>
                        <th scope="col" class="px-6 py-4 cursor-pointer sort" data-sort="qty">QTY</th>
                        <th scope="col" class="px-6 py-4 cursor-pointer sort" data-sort="type">Type</th>
                        <th scope="col" class="px-6 py-4 cursor-pointer sort" data-sort="reference">Reference</th>
                        <th scope="col" class="px-6 py-4 cursor-pointer sort" data-sort="note">Note</th>
                    </tr>
                </thead>
                <tbody class="list">
                    @forelse ($useds as $used)
                        <tr
                            class="{{ $loop->odd ? 'bg-white' : 'bg-gray-50' }} border-b dark:bg-gray-900 dark:border-gray-700">
                            <td class="px-6 py-3 date">{{ $loop->iteration }}</td>
                            <td class="px-6 py-3 date">{{ $used->created_at->format('d M Y') }}</td>
                            <td class="px-6 py-3 item">{{ $used->sparepart->item->name ?? '-' }} -
                                {{ $used->sparepart->item->model ?? '-' }}</td>
                            <td class="px-6 py-3 qty">-{{ $used->qty }}</td>
                            <td class="px-6 py-3 type">
                                {{ $used->maintenance_id ? 'Maintenance' : 'Incident' }}
                            </td>
                            <td class="px-6 py-3 reference">
                                @if ($used->maintenance_id)
                                    <a href="{{ route('maintenances.show', $used->maintenance_id) }}" target="_blank"
                                        class="font-medium text-blue-600 dark:text-blue-500 hover:underline">
                                        Maintenance #{{ $used->maintenance_id }}
                                    </a>
                                @elseif ($used->incident_id)
                                    <a href="{{ route('incidents.show', $used->incident_id) }}" target="_blank"
                                        class="font-medium text-blue-600 dark:text-blue-500 hover:underline">
                                        Incident #{{ $used->incident_id }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-3 note italic">{{ $used->note ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">No used spareparts found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-6 px-4 pb-3 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div>
                    {{-- PERBAIKAN: appends semua parameter filter --}}
                    {{ $useds->appends(request()->query())->links() }}
                </div>
                <div class="flex items-center gap-4 flex-wrap justify-end">
                    {{-- PERBAHAN: onchange form --}}
                    <form method="GET" action="{{ route('sparepartused.index') }}"
                        onsubmit="showFullScreenLoader();">
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        <input type="hidden" name="sort" value="{{ request('sort') }}">
                        <input type="hidden" name="order" value="{{ request('order') }}">
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
            <script src="//cdnjs.cloudflare.com/ajax/libs/list.js/2.3.1/list.min.js"></script>
            <script>
                const usedSparepartsList = new List('used-spareparts-list', {
                    valueNames: ['date', 'item', 'qty', 'type', 'reference', 'note']
                });
            </script>
        @endpush

    </x-dashboard.sidebar>
</x-app-layout>
