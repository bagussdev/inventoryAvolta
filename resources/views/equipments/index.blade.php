<x-app-layout>
    <x-dashboard.sidebar>
        <x-alert-information></x-alert-information>

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mt-10 w-full gap-4">
            <h2 class="font-bold text-xl sm:text-2xl whitespace-nowrap truncate">
                Equipment List
            </h2>

            <form method="GET" action="{{ route('equipments.index') }}" class="flex gap-2"
                onsubmit="showFullScreenLoader();">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..."
                    class="text-xs sm:text-sm px-2 py-1.5 sm:px-3 sm:py-2 rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-purple-500 w-36 sm:w-44" />
                <button type="submit"
                    class="text-xs sm:text-sm px-3 py-1.5 sm:px-3 sm:py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition">
                    Search
                </button>
            </form>
        </div>

        <hr class="h-[3px] my-8 bg-gray-200 border-0 dark:bg-gray-700 w-full">

        <div class="relative overflow-x-auto shadow-md sm:rounded-lg mt-5" id="equipment-list">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400 text-center">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th class="px-6 py-3 cursor-pointer sort" data-sort="no">No</th>
                        <th class="px-6 py-3 cursor-pointer sort" data-sort="name">Name</th>
                        <th class="px-6 py-3 cursor-pointer sort" data-sort="sn">Serial Number</th>
                        <th class="px-6 py-3 cursor-pointer sort" data-sort="qty">Quantity</th>
                        <th class="px-6 py-3 cursor-pointer sort" data-sort="location">Location</th>
                        <th class="px-6 py-3 cursor-pointer sort" data-sort="status">Status</th>
                        @if (!Auth::user()->role_id === 5)
                            <th class="px-6 py-3 cursor-pointer sort" data-sort="transaction">Transaction ID</th>
                        @endif
                        <th class="px-6 py-3">Action</th>
                    </tr>
                </thead>
                <tbody class="list">
                    @forelse ($equipments as $equipment)
                        <tr
                            class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700">
                            <td class="px-6 py-4 no">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4 name">{{ $equipment->item->name ?? '-' }}</td>
                            <td class="px-6 py-4 sn">{{ $equipment->serial_number ?? '-' }}</td>
                            <td class="px-6 py-4 qty">{{ $equipment->transaction->qty ?? '-' }}</td>
                            <td class="px-6 py-4 location">{{ $equipment->store->name ?? '-' }}</td>

                            <td class="px-6 py-4 status">
                                @if ($equipment->status === 'available')
                                    <span
                                        class="px-3 py-1 text-sm font-semibold text-green-700 bg-green-200 rounded-md">{{ ucfirst($equipment->status) }}</span>
                                @elseif ($equipment->status === 'used')
                                    <span
                                        class="px-3 py-1 text-sm font-semibold text-teal-700 bg-teal-200 rounded-md">{{ ucfirst($equipment->status) }}</span>
                                @elseif ($equipment->status === 'maintenance')
                                    <span
                                        class="px-3 py-1 text-sm font-semibold text-red-800 bg-red-300 rounded-md">{{ ucfirst($equipment->status) }}</span>
                                @else
                                    <span
                                        class="px-3 py-1 text-sm font-semibold text-gray-500 bg-gray-100 rounded-md">Unknown
                                        {{ ucfirst($equipment->status) }}</span>
                                @endif
                            </td>
                            @if (!Auth::user()->role_id === 5)
                                <td class="px-6 py-4 transaction">
                                    <a href="{{ route('transactions.show', $equipment->transactions_id) }}"
                                        onclick="showFullScreenLoader();"
                                        class="text-sm text-blue-600 hover:underline">#{{ $equipment->transactions_id }}</a>
                                </td>
                            @endif
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-2 justify-center items-center">
                                    @can('equipments.migrate')
                                        <x-buttons.action-button text="Migrate" color="green"
                                            href="{{ route('equipments.migrate.form', $equipment->id) }}" />
                                    @endcan
                                    <x-buttons.action-button text="Detail" color="purple"
                                        href="{{ route('equipments.show', $equipment->id) }}" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">No equipment found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-6 px-4 pb-3 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div>
                    {{ $equipments->appends(['per_page' => $perPage, 'search' => $search])->links() }}
                </div>
                <div class="flex items-center gap-4 flex-wrap justify-end">
                    <form method="GET" action="{{ route('equipments.index') }}" onsubmit="showFullScreenLoader();">
                        <div class="flex items-center gap-1">
                            <label for="per_page" class="text-sm text-gray-600 dark:text-gray-300">Show</label>
                            <select name="per_page" id="per_page" onchange="this.form.submit()"
                                class="text-sm w-16 px-2 py-1 rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-purple-500">
                                <option value="5" {{ $perPage == 5 ? 'selected' : '' }}>5</option>
                                <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                                <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20</option>
                            </select>
                            <span class="text-sm text-gray-600 dark:text-gray-400">per page</span>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @push('scripts')
            <script>
                const equipmentList = new List('equipment-list', {
                    valueNames: ['no', 'name', 'sn', 'location', 'status', 'transaction', 'qty']
                });
            </script>
        @endpush

    </x-dashboard.sidebar>
</x-app-layout>
