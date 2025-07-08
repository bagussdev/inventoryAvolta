<x-app-layout>
    <x-dashboard.sidebar>
        <x-alert-information></x-alert-information>

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mt-10 w-full gap-4">
            <h2 class="font-bold text-xl sm:text-2xl whitespace-nowrap truncate">
                Outlet List
            </h2>

            <div class="flex flex-wrap gap-3 items-center">
                <form method="GET" action="{{ route('outlets.index') }}" class="flex gap-2"
                    onsubmit="showFullScreenLoader();">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..."
                        class="text-xs sm:text-sm px-2 py-1.5 sm:px-3 sm:py-2 rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-purple-500 w-36 sm:w-44" />
                    <input type="hidden" name="per_page" value="{{ $perPage ?? 5 }}">
                    <button type="submit" onsubmit="showFullScreenLoader();"
                        class="text-xs sm:text-sm px-3 py-1.5 sm:px-3 sm:py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition">
                        Search
                    </button>
                </form>
                @can('outletlist.create')
                    <a href="{{ route('outlets.create') }}" onclick="showFullScreenLoader();"
                        class="text-xs sm:text-sm px-3 py-1.5 sm:px-4 sm:py-2 text-white bg-purple-600 hover:bg-purple-700 focus:ring-4 focus:ring-purple-300 font-medium rounded-md focus:outline-none dark:bg-purple-500 dark:hover:bg-purple-600 dark:focus:ring-purple-700 text-center">
                        Create New Outlet
                    </a>
                @endcan
            </div>
        </div>

        <hr class="h-[3px] my-8 bg-gray-200 border-0 dark:bg-gray-700 w-full">

        <div class="relative overflow-x-auto shadow-md sm:rounded-lg mt-5" id="outlet-list">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead
                    class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 text-center">
                    <tr>
                        <th class="px-6 py-3 cursor-pointer sort" data-sort="no">No</th>
                        <th class="px-6 py-3 cursor-pointer sort" data-sort="name">Name</th>
                        <th class="px-6 py-3 cursor-pointer sort" data-sort="site_code">Site Code</th>
                        <th class="px-6 py-3 cursor-pointer sort" data-sort="since">Since</th>
                        <th class="px-6 py-3 cursor-pointer sort" data-sort="location">Location</th>
                        <th class="px-6 py-3 cursor-pointer sort" data-sort="status">Status</th>
                        <th class="px-6 py-3">Action</th>
                    </tr>
                </thead>
                <tbody class="list">
                    @forelse ($stores as $store)
                        <tr
                            class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700 text-center">
                            <td class="px-6 py-4 no">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4 name">{{ $store->name }}</td>
                            <td class="px-6 py-4 site_code">{{ $store->site_code }}</td>
                            <td class="px-6 py-4 since">{{ \Carbon\Carbon::parse($store->since)->format('d M Y') }}</td>
                            <td class="px-6 py-4 location">{{ $store->location }}</td>
                            <td class="px-6 py-4 status">
                                @if ($store->status == 'Y')
                                    <span
                                        class="inline-block px-3 py-1 text-sm font-medium rounded bg-green-100 text-green-800">Active</span>
                                @else
                                    <span
                                        class="inline-block px-3 py-1 text-sm font-medium rounded bg-red-100 text-red-700">Non-Active</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-2 justify-center">
                                    @can('outletcontrol')
                                        @if ($store->status == 'Y')
                                            <a href="{{ route('outlets.deactive', $store->id) }}"
                                                onclick="showFullScreenLoader();"
                                                class="px-4 py-1 bg-red-500 text-white rounded-md hover:bg-red-600 text-sm w-full sm:w-auto text-center">
                                                Non-Active
                                            </a>
                                        @else
                                            <a href="{{ route('outlets.active', $store->id) }}"
                                                onclick="showFullScreenLoader();"
                                                class="px-4 py-1 bg-green-500 text-white rounded-md hover:bg-green-600 text-sm w-full sm:w-auto text-center">
                                                Active
                                            </a>
                                        @endif
                                    @endcan
                                    @can('outletlist.edit')
                                        <a href="{{ route('outlets.edit', $store->id) }}" onclick="showFullScreenLoader();"
                                            class="px-4 py-1 bg-blue-500 text-white rounded-md hover:bg-blue-600 text-sm w-full sm:w-auto text-center">
                                            Edit
                                        </a>
                                    @endcan
                                    <a href="{{ route('outlets.show', $store->id) }}" onclick="showFullScreenLoader();"
                                        class="px-4 py-1 bg-purple-500 text-white rounded-md hover:bg-purple-600 text-sm w-full sm:w-auto text-center">
                                        Detail
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">Tidak ada Outlet</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-6 px-4 pb-3 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div>
                    {{ $stores->appends(['per_page' => $perPage, 'search' => $search])->links() }}
                </div>
                <div class="flex items-center gap-4 flex-wrap justify-end">
                    <form method="GET" action="{{ route('outlets.index') }}">
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
                const outletList = new List('outlet-list', {
                    valueNames: ['no', 'name', 'site_code', 'since', 'location', 'status']
                });
            </script>
        @endpush
    </x-dashboard.sidebar>
</x-app-layout>
