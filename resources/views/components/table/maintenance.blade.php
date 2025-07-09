<div class="w-full overflow-x-auto">
    <div class="min-w-full inline-block align-middle">
        <div class="overflow-hidden shadow sm:rounded-lg" id="maintenance-list">
            <table
                class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs sm:text-sm text-center text-gray-600 dark:text-gray-300">
                <thead
                    class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-700 dark:text-gray-400 whitespace-nowrap">
                    <tr>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="no">No</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="name">Equipment</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="model">S/N</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="store">Store</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="date">Date</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="freq">Frequency</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="status">Status</th>
                        <th class="px-4 py-2 md:px-6 md:py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="list whitespace-nowrap">
                    @forelse ($maintenances as $maintenance)
                        <tr
                            class="border-b dark:border-gray-700 {{ $loop->odd ? 'bg-white dark:bg-gray-900' : 'bg-gray-50 dark:bg-gray-800' }}">
                            <td class="px-4
                            py-2 md:px-6 md:py-3 no">{{ $loop->iteration }}
                            </td>
                            <td class="px-4 py-2 md:px-6 md:py-3 name">
                                {{ ucfirst(strtolower($maintenance->equipment->item->name ?? '-')) }}
                            </td>
                            <td class="px-4 py-2 md:px-6 md:py-3 model">
                                {{ $maintenance->equipment->serial_number ?? '-' }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 store">
                                {{ $maintenance->equipment->store->name ?? '-' }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 date">
                                {{ \Carbon\Carbon::parse($maintenance->maintenance_date)->format('d M Y') }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 freq">{{ ucfirst($maintenance->frequensi) }}</td>
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
                                <div class="flex flex-col sm:flex-row items-center justify-center gap-1">
                                    @if ($maintenance->status === 'maintenance')
                                        @can('maintenance.proses')
                                            <form action="{{ route('maintenances.proses', $maintenance->id) }}"
                                                method="GET" onsubmit="return confirmAndLoad('Are you sure to process?')">
                                                <x-buttons.action-button text="Proses" color="green" />
                                            </form>
                                        @endcan
                                    @elseif ($maintenance->status === 'completed')
                                        @can('maintenance.edit')
                                            <x-buttons.action-button text="Edit" color="blue"
                                                href="{{ route('maintenances.editCompleted', $maintenance->id) }}"
                                                onclick="showFullScreenLoader();" />
                                        @endcan
                                    @elseif ($maintenance->status === 'in progress')
                                        @can('maintenance.confirm')
                                            <x-buttons.action-button text="Confirm" color="blue"
                                                href="{{ route('maintenances.confirm', $maintenance->id) }}"
                                                onclick="showFullScreenLoader();" />
                                        @endcan
                                    @elseif ($maintenance->status === 'resolved')
                                        @can('maintenance.closed')
                                            <form action="{{ route('maintenances.closed', $maintenance->id) }}"
                                                method="GET" onsubmit="return confirmAndLoad('Are you sure to closed?')">
                                                <x-buttons.action-button text="Closed" color="red" />
                                            </form>
                                        @endcan
                                    @else
                                        @can('schedulemaintenance.edit')
                                            <x-buttons.action-button text="Edit" color="blue"
                                                href="{{ route('maintenances.edit', $maintenance->id) }}"
                                                onclick="showFullScreenLoader();" />
                                        @endcan
                                    @endif

                                    <x-buttons.action-button text="Detail" color="purple"
                                        href="{{ route('maintenances.show', $maintenance->id) }}"
                                        onclick="showFullScreenLoader();" />
                                </div>
                            </td>
                            {{-- <td class="px-4 py-2 md:px-6 md:py-3">
                                <div class="flex flex-col sm:flex-row items-center justify-center gap-1">
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
                            </td> --}}
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-xs">No maintenance data found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if ($showPagination)
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
            @endif

        </div>
    </div>
</div>

@push('scripts')
    <script>
        const maintenanceList = new List('maintenance-list', {
            valueNames: ['no', 'name', 'model', 'store', 'date', 'freq', 'status']
        });
    </script>
@endpush
