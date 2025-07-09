<div class="w-full overflow-x-auto">
    <div class="min-w-full inline-block align-middle">
        <div class="overflow-hidden shadow sm:rounded-lg" id="items-list">
            <table
                class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs sm:text-sm text-center text-gray-600 dark:text-gray-300">
                <thead
                    class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-700 dark:text-gray-400 whitespace-nowrap">
                    <tr>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="no">No</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="id">Items ID</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="name">Name</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="type">Type</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="brand">Brand</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="model">Model</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="category">Category</th>
                        <th class="px-4 py-2 md:px-6 md:py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="list">
                    @forelse ($items as $item)
                        <tr
                            class="border-b dark:border-gray-700 {{ $loop->odd ? 'bg-white dark:bg-gray-900' : 'bg-gray-50 dark:bg-gray-800' }}">
                            <td class="px-4 py-2 md:px-6 md:py-3 capitalize no">{{ $loop->iteration }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 capitalize id">{{ $item->id }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 capitalize name">{{ strtolower($item->name) }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 capitalize type">{{ strtolower($item->type) }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 capitalize brand">{{ strtolower($item->brand) }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 uppercase model">{{ $item->model }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 capitalize category">{{ strtolower($item->category) }}
                            </td>
                            <td class="px-2 py-2 md:px-4 md:py-3">
                                <div class="flex flex-col sm:flex-row items-center justify-center gap-1">
                                    @can('inventoryitems.edit')
                                        <x-buttons.action-button text="Edit" color="blue" class=""
                                            href="{{ route('items.edit', $item->id) }}" onclick="showFullScreenLoader();" />
                                    @endcan

                                    @can('inventoryitems.delete')
                                        <form action="{{ route('items.destroy', $item->id) }}" method="POST"
                                            onsubmit="return confirmAndLoad('Are you sure to deleted?')"
                                            class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <x-buttons.action-button text="Delete" color="red" class="" />
                                        </form>
                                    @endcan

                                    <x-buttons.action-button text="Detail" color="purple" class="" href="#"
                                        onclick="showFullScreenLoader();" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-xs">Tidak ada Item</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Pagination + Per Page --}}
<div class="mt-6 px-4 pb-3 flex flex-col sm:flex-row justify-between items-center gap-4">
    <div>
        {{ $items->appends(['per_page' => $perPage, 'search' => $search])->links() }}
    </div>
    <div class="flex items-center gap-4 flex-wrap justify-end">
        <form method="GET" action="{{ route('items.index') }}" onsubmit="showFullScreenLoader();">
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

@push('scripts')
    <script>
        const itemList = new List('items-list', {
            valueNames: ['no', 'id', 'name', 'type', 'brand', 'model', 'category']
        });
    </script>
@endpush
