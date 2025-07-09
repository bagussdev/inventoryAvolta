<div class="w-full overflow-x-auto">
    <div class="min-w-full inline-block align-middle">
        <div class="overflow-hidden shadow sm:rounded-lg" id="sparepart-list">
            <table
                class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs sm:text-sm md:text-base lg:text-[15px] text-center text-gray-600 dark:text-gray-300">
                <thead
                    class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-700 dark:text-gray-400 whitespace-nowrap">
                    <tr>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="no">No</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="name">Name</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="model">Model</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="brand">Brand</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="supplier">Supplier</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="stock">Stock</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="status">Status</th>
                        <th class="px-4 py-2 md:px-6 md:py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="list">
                    @forelse ($spareparts as $sparepart)
                        <tr
                            class="border-b dark:border-gray-700 {{ $loop->odd ? 'bg-white dark:bg-gray-900' : 'bg-gray-50 dark:bg-gray-800' }}">
                            <td class="px-4
                            py-2 md:px-6 md:py-3 no">{{ $loop->iteration }}
                            </td>
                            <td class="px-4 py-2 md:px-6 md:py-3 name capitalize">
                                {{ strtolower($sparepart->item->name ?? '-') }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 model uppercase">{{ $sparepart->item->model ?? '-' }}
                            </td>
                            <td class="px-4 py-2 md:px-6 md:py-3 brand capitalize">
                                {{ strtolower($sparepart->item->brand ?? '-') }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 supplier capitalize">
                                {{ strtolower($sparepart->transaction->supplier ?? '-') }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 stock">{{ $sparepart->qty }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 status">
                                @if ($sparepart->status === 'empty')
                                    <span
                                        class="inline-block px-3 py-1 text-xs font-semibold rounded bg-red-100 text-red-800">Empty</span>
                                @elseif ($sparepart->status === 'low')
                                    <span
                                        class="inline-block px-3 py-1 text-xs font-semibold rounded bg-orange-100 text-orange-800">Low</span>
                                @else
                                    <span
                                        class="inline-block px-3 py-1 text-xs font-semibold rounded bg-green-100 text-green-800">Available</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 md:px-6 md:py-3">
                                <x-buttons.action-button text="Detail" color="purple" class=""
                                    onclick="showFullScreenLoader();"
                                    href="{{ route('spareparts.show', $sparepart->id) }}" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-xs">No spareparts found.</td>
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
            {{ $spareparts->appends(['per_page' => $perPage, 'search' => $search])->links() }}
        </div>
        <div class="flex items-center gap-4 flex-wrap justify-end">
            <form method="GET" action="{{ route('spareparts.index') }}" onsubmit="showFullScreenLoader();">
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
        const sparepartList = new List('sparepart-list', {
            valueNames: ['no', 'name', 'supplier', 'stock', 'status']
        });
    </script>
@endpush
