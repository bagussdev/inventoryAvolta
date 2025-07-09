<div class="w-full overflow-x-auto">
    <div class="min-w-full inline-block align-middle">
        <div class="overflow-hidden shadow sm:rounded-lg" id="outlet-list">
            <table
                class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs sm:text-sm text-center text-gray-600 dark:text-gray-300">
                <thead
                    class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-700 dark:text-gray-400 whitespace-nowrap">
                    <tr>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="no">No</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="name">Name</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="site_code">Site Code</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="since">Since</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="location">Location</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="status">Status</th>
                        <th class="px-4 py-2 md:px-6 md:py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="list whitespace-nowrap">
                    @forelse ($stores as $store)
                        <tr
                            class="border-b dark:border-gray-700 {{ $loop->odd ? 'bg-white dark:bg-gray-900' : 'bg-gray-50 dark:bg-gray-800' }}">
                            <td class="px-4 py-2 md:px-6 md:py-3 no">{{ $loop->iteration }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 name">{{ $store->name }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 site_code">{{ $store->site_code }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 since">
                                {{ \Carbon\Carbon::parse($store->since)->format('d M Y') }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 location">{{ $store->location }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 status">
                                @if ($store->status == 'Y')
                                    <span
                                        class="px-3 py-1 text-xs font-medium rounded-md bg-green-100 text-green-800">Active</span>
                                @else
                                    <span
                                        class="px-3 py-1 text-xs font-medium rounded-md bg-red-100 text-red-700">Non-Active</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 md:px-6 md:py-3">
                                <div class="flex flex-col sm:flex-row items-center justify-center gap-1">
                                    @can('outletcontrol')
                                        @if ($store->status == 'Y')
                                            <x-buttons.action-button text="Non-Active" color="red"
                                                href="{{ route('outlets.deactive', $store->id) }}"
                                                onclick="showFullScreenLoader();" />
                                        @else
                                            <x-buttons.action-button text="Active" color="green"
                                                href="{{ route('outlets.active', $store->id) }}"
                                                onclick="showFullScreenLoader();" />
                                        @endif
                                    @endcan

                                    @can('outletlist.edit')
                                        <x-buttons.action-button text="Edit" color="blue"
                                            href="{{ route('outlets.edit', $store->id) }}"
                                            onclick="showFullScreenLoader();" />
                                    @endcan

                                    <x-buttons.action-button text="Detail" color="purple"
                                        href="{{ route('outlets.show', $store->id) }}"
                                        onclick="showFullScreenLoader();" />
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-xs">No outlet data found.</td>
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
            {{ $stores->appends(['per_page' => $perPage, 'search' => $search])->links() }}
        </div>
        <div class="flex items-center gap-4 flex-wrap justify-end">
            <form method="GET" action="{{ route('outlets.index') }}">
                <input type="hidden" name="search" value="{{ request('search') }}">
                <div class="flex items-center gap-1">
                    <label for="per_page" class="text-sm text-gray-600 dark:text-gray-300">Show</label>
                    <select name="per_page" id="per_page" onchange="this.form.submit()"
                        class="text-sm w-16 px-2 py-1 rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-purple-500">
                        <option value="5" {{ $perPage == 5 ? 'selected' : '' }}>5</option>
                        <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                    </select>
                    <span class="text-sm text-gray-600 dark:text-gray-400">per page</span>
                </div>
            </form>
        </div>
    </div>
@endif
@push('scripts')
    <script>
        const outletList = new List('outlet-list', {
            valueNames: ['no', 'name', 'site_code', 'since', 'location', 'status']
        });
    </script>
@endpush
