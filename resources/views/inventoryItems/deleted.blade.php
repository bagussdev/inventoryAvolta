<x-app-layout>
    <x-dashboard.sidebar>
        <x-alert-information></x-alert-information>

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

                    <button type="submit"
                        class="text-xs sm:text-sm px-3 py-1.5 sm:px-3 sm:py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition">
                        Search
                    </button>
                </form>
                @can('inventoryitems.delete')
                    <form action="{{ route('items.deleted.permanentAll') }}" method="POST"
                        onsubmit="return confirmAndLoad('Are you sure you want to permanently delete all items?')"
                        class="inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="text-xs sm:text-sm px-3 py-1.5 sm:px-4 sm:py-2 text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:ring-red-300 font-medium rounded-md focus:outline-none dark:bg-red-500 dark:hover:bg-red-600 dark:focus:ring-red-700 text-center">
                            Delete All Permanently
                        </button>
                    </form>
                @endcan

            </div>
        </div>

        <hr class="h-[3px] my-8 bg-gray-200 border-0 dark:bg-gray-700 w-full">

        <div class="relative overflow-x-auto shadow-md sm:rounded-lg mt-5">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead
                    class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 text-center">
                    <tr>
                        <th scope="col" class="px-6 py-3">No</th>
                        <th scope="col" class="px-6 py-3">Name</th>
                        <th scope="col" class="px-6 py-3">Type</th>
                        <th scope="col" class="px-6 py-3">Brand</th>
                        <th scope="col" class="px-6 py-3">Model</th>
                        <th scope="col" class="px-6 py-3">Category</th>
                        <th scope="col" class="px-6 py-3">Deleted At</th>
                        <th scope="col" class="px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr
                            class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700 text-center">
                            <th class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                {{ $loop->iteration }}
                            </th>
                            <td class="px-6 py-4">{{ $item->name }}</td>
                            <td class="px-6 py-4">{{ $item->type }}</td>
                            <td class="px-6 py-4">{{ $item->brand }}</td>
                            <td class="px-6 py-4">{{ $item->model }}</td>
                            <td class="px-6 py-4">{{ $item->category }}</td>
                            <td class="px-6 py-4">{{ $item->deleted_at }}</td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-2 justify-center">
                                    <form action="{{ route('items.restore', $item->id) }}" method="POST"
                                        onsubmit="return confirmAndLoad('Are you sure you want to restore this item?')"
                                        class="inline-block">
                                        @csrf
                                        <x-buttons.action-button text="Restore" color="green" />
                                    </form>
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
                    <form method="GET" action="{{ route('items.index') }}">
                        <div class="flex items-center gap-1">
                            <label for="per_page" class="text-sm text-gray-600 dark:text-gray-300">Show</label>
                            <select name="per_page" id="per_page" onchange="this.form.submit()"
                                class="text-sm w-16 px-2 py-1 rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-red-500">
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
    </x-dashboard.sidebar>
</x-app-layout>
