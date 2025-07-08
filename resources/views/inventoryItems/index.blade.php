<x-app-layout>
    <x-dashboard.sidebar>
        <x-alert-information></x-alert-information>
        <!-- Upload Excel Modal -->
        @include('inventoryitems.upload-modal')

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mt-10 w-full gap-4">
            <h2 class="font-bold text-xl sm:text-2xl whitespace-nowrap truncate">
                Items List
            </h2>

            <div class="flex flex-wrap gap-3 items-center">
                <form method="GET" action="{{ route('items.index') }}" class="flex gap-2"
                    onsubmit="showFullScreenLoader();">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..."
                        class="text-xs sm:text-sm px-2 py-1.5 sm:px-3 sm:py-2 rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-purple-500 w-36 sm:w-44" />
                    <input type="hidden" name="per_page" value="{{ $perPage ?? 5 }}">

                    <button type="submit" onsubmit="showFullScreenLoader();"
                        class="text-xs sm:text-sm px-3 py-1.5 sm:px-3 sm:py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition">
                        Search
                    </button>
                </form>
                @can('inventoryitems.create')
                    <a href="{{ route('items.create') }}" onclick="showFullScreenLoader();"
                        class="text-xs sm:text-sm px-3 py-1.5 sm:px-4 sm:py-2 text-white bg-purple-600 hover:bg-purple-700 focus:ring-4 focus:ring-purple-300 font-medium rounded-md focus:outline-none dark:bg-purple-500 dark:hover:bg-purple-600 dark:focus:ring-purple-700 text-center">
                        Create New Item
                    </a>
                @endcan
                @can('inventoryitems.uploadExcel')
                    <button onclick="document.getElementById('uploadModal').classList.remove('hidden')"
                        class="text-xs sm:text-sm px-3 py-1.5 sm:px-4 sm:py-2 text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-300 font-medium rounded-md focus:outline-none dark:bg-green-500 dark:hover:bg-green-600 dark:focus:ring-green-700 text-center">
                        Upload Excel
                    </button>
                @endcan
            </div>
        </div>

        <hr class="h-[3px] my-8 bg-gray-200 border-0 dark:bg-gray-700 w-full">

        <div class="relative overflow-x-auto shadow-md sm:rounded-lg mt-5" id="items-list">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400 text-center">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th class="px-6 py-3 cursor-pointer sort" data-sort="no">No</th>
                        <th class="px-6 py-3 cursor-pointer sort" data-sort="id">Items ID</th>
                        <th class="px-6 py-3 cursor-pointer sort" data-sort="name">Name</th>
                        <th class="px-6 py-3 cursor-pointer sort" data-sort="type">Type</th>
                        <th class="px-6 py-3 cursor-pointer sort" data-sort="brand">Brand</th>
                        <th class="px-6 py-3 cursor-pointer sort" data-sort="model">Model</th>
                        <th class="px-6 py-3 cursor-pointer sort" data-sort="category">Category</th>
                        <th class="px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="list">
                    @forelse ($items as $item)
                        <tr
                            class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700">
                            <td class="px-6 py-4 no">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4 id">{{ $item->id }}</td>
                            <td class="px-6 py-4 name">{{ $item->name }}</td>
                            <td class="px-6 py-4 type">{{ $item->type }}</td>
                            <td class="px-6 py-4 brand">{{ $item->brand }}</td>
                            <td class="px-6 py-4 model">{{ $item->model }}</td>
                            <td class="px-6 py-4 category">{{ $item->category }}</td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-2 justify-center">
                                    @can('inventoryitems.edit')
                                        <x-buttons.action-button text="Edit" color="blue"
                                            href="{{ route('items.edit', $item->id) }}"
                                            onclick="showFullScreenLoader();" />
                                    @endcan

                                    @can('inventoryitems.delete')
                                        <form action="{{ route('items.destroy', $item->id) }}" method="POST"
                                            onsubmit="return confirmAndLoad('Are you sure to deleted?')"
                                            class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <x-buttons.action-button text="Delete" color="red" />
                                        </form>
                                    @endcan

                                    <x-buttons.action-button text="Detail" color="purple" href="#"
                                        onclick="showFullScreenLoader();" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">Tidak ada Item</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

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
        </div>

        @push('scripts')
            <script>
                const itemList = new List('items-list', {
                    valueNames: ['no', 'id', 'name', 'type', 'brand', 'model', 'category']
                });
            </script>
        @endpush
    </x-dashboard.sidebar>
</x-app-layout>
