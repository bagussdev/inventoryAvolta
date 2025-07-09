<div class="w-full overflow-x-auto">
    <div class="min-w-full inline-block align-middle">
        <div class="overflow-hidden shadow sm:rounded-lg" id="used-spareparts-list">
            <table
                class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs sm:text-sm md:text-base lg:text-[15px] text-center text-gray-600 dark:text-gray-300">
                <thead
                    class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-700 dark:text-gray-400 whitespace-nowrap">
                    <tr>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="no">No</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="date">Date</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="item">Item</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="qty">QTY</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="type">Type</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="reference">Reference</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="note">Note</th>
                    </tr>
                </thead>
                <tbody class="list">
                    @forelse ($useds as $used)
                        <tr
                            class="border-b dark:border-gray-700 {{ $loop->odd ? 'bg-white dark:bg-gray-900' : 'bg-gray-50 dark:bg-gray-800' }}">
                            <td class="px-4
                            py-2 md:px-6 md:py-3 no">{{ $loop->iteration }}
                            </td>
                            <td class="px-4 py-2 md:px-6 md:py-3 date">{{ $used->created_at->format('d M Y H:i') }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 item">
                                {{ ucfirst(strtolower($used->sparepart->item->name ?? '-')) }} -
                                {{ $used->sparepart->item->model ?? '-' }}
                            </td>
                            <td class="px-4 py-2 md:px-6 md:py-3 qty">-{{ $used->qty }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 type">
                                {{ $used->maintenance_id ? 'Maintenance' : ($used->incident_id ? 'Incident' : 'Request') }}
                            </td>
                            <td class="px-4 py-2 md:px-6 md:py-3 reference">
                                @if ($used->maintenance_id)
                                    <a href="{{ route('maintenances.show', $used->maintenance_id) }}" target="_blank"
                                        class="font-medium text-blue-600 dark:text-blue-500 hover:underline">
                                        MNT0000{{ $used->maintenance_id }}
                                    </a>
                                @elseif ($used->incident_id)
                                    <a href="{{ route('incidents.show', $used->incident_id) }}" target="_blank"
                                        class="font-medium text-blue-600 dark:text-blue-500 hover:underline">
                                        {{ $used->incident?->unique_id ?? '-' }}
                                    </a>
                                @elseif ($used->request_id)
                                    <a href="{{ route('requests.show', $used->request_id) }}" target="_blank"
                                        class="font-medium text-blue-600 dark:text-blue-500 hover:underline">
                                        {{ $used->request?->unique_id ?? '-' }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-2 md:px-6 md:py-3 note italic">{{ $used->note ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-xs">No used spareparts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@if ($showPagination)
    {{-- Pagination + Per Page --}}
    <div class="mt-6 px-4 pb-3 flex flex-col sm:flex-row justify-between items-center gap-4">
        <div>
            {{ $useds->appends(request()->query())->links() }}
        </div>
        <div class="flex items-center gap-4 flex-wrap justify-end">
            <form method="GET" action="{{ route('sparepartused.index') }}" onsubmit="showFullScreenLoader();">
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
@endif
@push('scripts')
    <script>
        const usedSparepartsList = new List('used-spareparts-list', {
            valueNames: ['no', 'date', 'item', 'qty', 'type', 'reference', 'note']
        });
    </script>
@endpush
