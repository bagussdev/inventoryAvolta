<x-app-layout>
    <x-dashboard.sidebar>
        <x-alert-information />

        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mt-5 sm:mt-10 w-full gap-4">
            <h2 class="font-bold text-xl sm:text-2xl whitespace-nowrap truncate">
                Deleted Items
            </h2>
            <div class="flex flex-wrap gap-3 items-center">
                <!-- Search -->
                <form method="GET" action="{{ route('deleteditems.index') }}" class="flex gap-2"
                    onsubmit="showFullScreenLoader();">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..."
                        class="text-xs sm:text-sm px-2 py-1.5 sm:px-3 sm:py-2 rounded-md border border-gray-300
                               dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-purple-500 w-36 sm:w-44" />
                    <button type="submit"
                        class="text-xs sm:text-sm px-3 py-1.5 sm:px-3 sm:py-2 bg-purple-600 text-white rounded-md
                               hover:bg-purple-700 transition">
                        Search
                    </button>
                </form>
            </div>
        </div>

        <hr class="h-[3px] my-8 bg-gray-200 border-0 dark:bg-gray-700 w-full">

        <!-- Table -->
        <div class="w-full overflow-x-auto">
            <div class="min-w-full inline-block align-middle">
                <div class="overflow-hidden shadow rounded-lg" id="deletedList">
                    <table
                        class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs sm:text-sm text-center text-gray-700 dark:text-gray-300 whitespace-nowrap">
                        <thead class="bg-gray-100 dark:bg-gray-700 text-xs uppercase text-gray-700 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-3 sort cursor-pointer" data-sort="number">No <span
                                        class="sort-icon"></span></th>
                                <th class="px-4 py-3 sort cursor-pointer" data-sort="type">Type <span
                                        class="sort-icon"></span></th>
                                <th class="px-4 py-3 sort cursor-pointer" data-sort="hostname">Hostname <span
                                        class="sort-icon"></span></th>
                                <th class="px-4 py-3 sort cursor-pointer" data-sort="serialnumber">SN <span
                                        class="sort-icon"></span></th>
                                <th class="px-4 py-3 sort cursor-pointer" data-sort="model">Model <span
                                        class="sort-icon"></span></th>
                                <th class="px-4 py-3 sort cursor-pointer" data-sort="brand">Brand <span
                                        class="sort-icon"></span></th>
                                <th class="px-4 py-3 sort cursor-pointer" data-sort="user">User <span
                                        class="sort-icon"></span></th>
                                <th class="px-4 py-3 sort cursor-pointer" data-sort="location">Location <span
                                        class="sort-icon"></span></th>
                                <th class="px-4 py-3 sort cursor-pointer" data-sort="status">Status <span
                                        class="sort-icon"></span></th>
                                <th class="px-4 py-3">Deleted At</th>
                                <th class="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="deletedBody"
                            class="list bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700"
                            data-base-offset="{{ $baseOffset }}">
                            @forelse ($items as $item)
                                <tr data-id="{{ $item->id }}">
                                    <td class="px-4 py-3 number"></td>
                                    <td class="px-4 py-3 type">{{ ucfirst($item->type) }}</td>
                                    <td class="px-4 py-3 hostname">{{ $item->hostname }}</td>
                                    <td class="px-4 py-3 serialnumber">{{ $item->serialnumber }}</td>
                                    <td class="px-4 py-3 model">{{ $item->model }}</td>
                                    <td class="px-4 py-3 brand">{{ $item->brand }}</td>
                                    <td class="px-4 py-3 user">{{ $item->user ?? '-' }}</td>
                                    <td class="px-4 py-3 location">
                                        {{ $item->location ? App\Models\Store::find($item->location)->name ?? '-' : '-' }}
                                    </td>
                                    <td class="px-4 py-3 status">
                                        @php
                                            $statusLabels = [
                                                'available' => 'Available',
                                                'in_use' => 'In Use',
                                                'broken' => 'Broken',
                                                'scrap' => 'Scrap',
                                            ];
                                            $statusColors = [
                                                'available' => 'bg-green-100 text-green-800',
                                                'in_use' => 'bg-blue-100 text-blue-800',
                                                'broken' => 'bg-red-100 text-red-800',
                                                'scrap' => 'bg-gray-200 text-gray-800',
                                            ];
                                            $badgeColor = $statusColors[$item->status] ?? 'bg-gray-100 text-gray-800';
                                            $label = $statusLabels[$item->status] ?? ucfirst($item->status);
                                        @endphp
                                        <span
                                            class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-md {{ $badgeColor }}">
                                            {{ $label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">{{ $item->deleted_at }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-row justify-center items-center gap-1">
                                            <!-- Restore -->
                                            <form
                                                action="{{ route('deleteditems.restore', ['type' => $item->type, 'id' => $item->id]) }}"
                                                method="POST" onsubmit="return confirmAndLoad('Restore this item?')">
                                                @csrf
                                                @method('PATCH')
                                                <x-buttons.action-button text="Restore" color="green" />
                                            </form>

                                            <!-- Force Delete -->
                                            <form
                                                action="{{ route('deleteditems.forceDelete', ['type' => $item->type, 'id' => $item->id]) }}"
                                                method="POST"
                                                onsubmit="return confirmAndLoad('Permanently delete this item?')">
                                                @csrf
                                                @method('DELETE')
                                                <x-buttons.action-button text="Delete" color="red" />
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                        No deleted items found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <x-per-page-selector :route="'deleteditems.index'" :perPage="$perPage" :search="$search" :items="$items" />
    </x-dashboard.sidebar>

    @push('scripts')
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const tbody = document.getElementById('deletedBody');
                if (!tbody) return;

                const list = new List('deletedList', {
                    valueNames: ['number', 'type', 'hostname', 'serialnumber', 'model', 'brand', 'user',
                        'location', 'status'
                    ],
                });

                // Tri-state sort
                const headers = document.querySelectorAll('th.sort');
                const states = {};

                function applyIcon(h, st) {
                    const icon = h.querySelector('.sort-icon');
                    icon.textContent = st === 1 ? '↑' : st === 2 ? '↓' : '';
                }
                headers.forEach(h => {
                    const key = h.dataset.sort;
                    states[key] = 0;
                    h.addEventListener('click', () => {
                        states[key] = (states[key] + 1) % 3;
                        headers.forEach(o => {
                            if (o !== h) {
                                states[o.dataset.sort] = 0;
                                applyIcon(o, 0);
                            }
                        });
                        applyIcon(h, states[key]);
                        if (states[key] === 1) list.sort(key, {
                            order: 'asc'
                        });
                        else if (states[key] === 2) list.sort(key, {
                            order: 'desc'
                        });
                        else list.sort('', {
                            order: 'asc'
                        });
                        renumber();
                    });
                });

                function renumber() {
                    const rows = tbody.querySelectorAll('tr[data-id]');
                    let i = 0;
                    const baseOffset = parseInt(tbody.dataset.baseOffset || '0', 10);
                    rows.forEach(tr => {
                        const cell = tr.querySelector('.number');
                        if (cell) cell.textContent = (baseOffset + (++i)).toString();
                    });
                    list.reIndex();
                }

                renumber();
            });
        </script>
    @endpush
</x-app-layout>
