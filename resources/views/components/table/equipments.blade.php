<div class="w-full overflow-x-auto">
    <div class="min-w-full inline-block align-middle">
        <div class="overflow-hidden shadow sm:rounded-lg" id="equipment-list">
            <table
                class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs sm:text-sm text-center text-gray-600 dark:text-gray-300">
                <thead
                    class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-700 dark:text-gray-400 whitespace-nowrap">
                    <tr>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="no">No</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="name">Name</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="sn">Serial Number</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="qty">Qty</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="location">Location</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="status">Status</th>
                        @if (!Auth::user()->role_id === 5)
                            <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="transaction">
                                Transaction ID</th>
                        @endif
                        <th class="px-4 py-2 md:px-6 md:py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="list">
                    @forelse ($equipments as $equipment)
                        <tr
                            class="border-b dark:border-gray-700 {{ $loop->odd ? 'bg-white dark:bg-gray-900' : 'bg-gray-50 dark:bg-gray-800' }}">
                            <td class="px-4 py-2 md:px-6 md:py-3 no">{{ $loop->iteration }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 name capitalize">
                                {{ strtolower($equipment->item->name ?? '-') }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 sn capitalize">{{ $equipment->serial_number ?? '-' }}
                            </td>
                            <td class="px-4 py-2 md:px-6 md:py-3 qty">{{ $equipment->transaction->qty ?? '-' }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 location capitalize">
                                {{ strtolower($equipment->store->name ?? '-') }}
                            </td>
                            <td class="px-4 py-2 md:px-6 md:py-3 status">
                                @php
                                    $status = strtolower($equipment->status);
                                    $color = match ($status) {
                                        'available' => 'bg-green-100 text-green-800',
                                        'maintenance' => 'bg-red-100 text-red-800',
                                        'used' => 'bg-yellow-100 text-yellow-600',
                                        default => 'bg-gray-100 text-gray-600',
                                    };
                                @endphp
                                <span class="px-3 py-1 text-xs font-medium rounded-md {{ $color }}">
                                    {{ ucfirst($equipment->status) }}
                                </span>
                            </td>

                            @if (!Auth::user()->role_id === 5)
                                <td class="px-4 py-2 md:px-6 md:py-3 transaction">
                                    <a href="{{ route('transactions.show', $equipment->transactions_id) }}"
                                        onclick="showFullScreenLoader();"
                                        class="text-sm text-blue-600 hover:underline">#{{ $equipment->transactions_id }}</a>
                                </td>
                            @endif
                            <td class="px-4 py-2 md:px-6 md:py-3">
                                <div class="flex flex-col sm:flex-row items-center justify-center gap-1">
                                    @can('equipments.migrate')
                                        <x-buttons.action-button text="Migrate" color="green" class=""
                                            href="{{ route('equipments.migrate.form', $equipment->id) }}"
                                            onclick="showFullScreenLoader()" />
                                    @endcan
                                    <x-buttons.action-button text="Detail" color="purple" class=""
                                        href="{{ route('equipments.show', $equipment->id) }}"
                                        onclick="showFullScreenLoader()" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-xs">No equipment found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@if ($showPagination)
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
@endif
@push('scripts')
    <script>
        const equipmentList = new List('equipment-list', {
            valueNames: ['no', 'name', 'sn', 'location', 'status', 'transaction', 'qty']
        });
    </script>
@endpush
