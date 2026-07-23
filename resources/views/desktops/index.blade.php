<x-app-layout>
    <x-dashboard.sidebar>
        <x-alert-information />

        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mt-5 sm:mt-10 w-full gap-4">
            <h2 class="font-bold text-xl sm:text-2xl whitespace-nowrap truncate">
                Desktop List
            </h2>
            <div class="flex flex-wrap gap-3 items-center">
                <!-- Search -->
                <form method="GET" action="{{ route('desktops.index') }}" class="flex gap-2"
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
                @can('desktopsmenu')
                    <a href="{{ route('desktops.export') }}"
                        class="text-xs sm:text-sm px-3 py-1.5 sm:px-4 sm:py-2 text-white bg-gray-600 hover:bg-gray-700 focus:ring-4 focus:ring-gray-300 font-medium rounded-md focus:outline-none dark:bg-gray-500 dark:hover:bg-gray-600 dark:focus:ring-gray-700 text-center">
                        Excel
                    </a>
                @endcan
                <!-- Create -->
                @can('desktops.create')
                    <a href="{{ route('desktops.create') }}" onclick="showFullScreenLoader();"
                        class="text-xs sm:text-sm px-3 py-1.5 sm:px-4 sm:py-2 text-white bg-purple-600 hover:bg-purple-700
                               focus:ring-4 focus:ring-purple-300 font-medium rounded-md focus:outline-none
                               dark:bg-purple-500 dark:hover:bg-purple-600 dark:focus:ring-purple-700 text-center">
                        Create New Desktop
                    </a>
                @endcan

                <!-- Upload Excel -->
                @can('desktops.uploadExcel')
                    <button onclick="document.getElementById('uploadModalDesktop').classList.remove('hidden')"
                        class="text-xs sm:text-sm px-3 py-1.5 sm:px-4 sm:py-2 text-white bg-green-600 hover:bg-green-700
                               focus:ring-4 focus:ring-green-300 font-medium rounded-md focus:outline-none
                               dark:bg-green-500 dark:hover:bg-green-600 dark:focus:ring-green-700 text-center">
                        Upload Excel
                    </button>
                @endcan
            </div>
        </div>

        <hr class="h-[3px] my-8 bg-gray-200 border-0 dark:bg-gray-700 w-full">

        <!-- Hostname Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <!-- Prev Hostname -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-medium text-gray-500 dark:text-gray-400">Prev Hostname</h3>
                    <p class="text-sm font-semibold text-gray-800 dark:text-white mt-1">
                        {{ $prevHostname ?? '-' }}
                    </p>
                </div>
                <span
                    class="px-2 py-1 text-[10px] bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded">
                    Desktop
                </span>
            </div>

            <!-- Next Hostname -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-medium text-gray-500 dark:text-gray-400">Next Hostname</h3>
                    <p class="text-sm font-semibold text-purple-600 dark:text-purple-400 mt-1">
                        {{ $nextHostname }}
                    </p>
                </div>
                <span class="px-2 py-1 text-[10px] bg-purple-100 text-purple-700 rounded">
                    Auto
                </span>
            </div>
        </div>

        <!-- Table -->
        <div class="w-full overflow-x-auto">
            <div class="min-w-full inline-block align-middle">
                <div class="overflow-hidden shadow rounded-lg" id="desktopList">
                    <table
                        class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs sm:text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap">
                        <thead
                            class="bg-gray-100 dark:bg-gray-700 text-xs uppercase text-gray-700 dark:text-gray-400 text-center">
                            <tr>
                                <th class="px-4 py-3">No</th>
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
                                <th class="px-4 py-3 sort cursor-pointer" data-sort="typewindows">Windows <span
                                        class="sort-icon"></span></th>
                                <th class="px-4 py-3 sort cursor-pointer" data-sort="osstatus">OS Status <span
                                        class="sort-icon"></span></th>
                                <th class="px-4 py-3 sort cursor-pointer" data-sort="iprealvnc">IP <span
                                        class="sort-icon"></span></th>
                                <th class="px-4 py-3 sort cursor-pointer" data-sort="status">Status <span
                                        class="sort-icon"></span></th>
                                <th class="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="desktopBody"
                            class="list bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700"
                            data-last-ts="{{ $latestTs }}" data-base-offset="{{ $baseOffset }}"
                            data-changes-url="{{ route('desktops.sync.changes', request()->only('search', 'per_page', 'page')) }}"
                            data-rows-url="{{ route('desktops.rows') }}">
                            @forelse ($desktops as $desktop)
                                <tr data-id="{{ $desktop->id }}">
                                    <td class="px-4 py-3 number"></td>
                                    <td class="px-4 py-3 hostname">{{ $desktop->hostname }}</td>
                                    <td class="px-4 py-3 serialnumber">{{ $desktop->serialnumber }}</td>
                                    <td class="px-4 py-3 model">{{ $desktop->model }}</td>
                                    <td class="px-4 py-3 brand text-center">{{ $desktop->brand }}</td>
                                    <td class="px-4 py-3 user">{{ $desktop->user ?? '-' }}</td>
                                    <td class="px-4 py-3 location">{{ $desktop->store->name ?? '-' }}</td>
                                    <td class="px-4 py-3 typewindows">{{ $desktop->typewindows ?? '-' }}</td>
                                    <td class="px-4 py-3 osstatus">{{ $desktop->osstatus ?? '-' }}</td>
                                    <td class="px-4 py-3 iprealvnc">{{ $desktop->iprealvnc ?? '-' }}</td>
                                    <td class="px-4 py-3 status">
                                        @php
                                            $status = $desktop->status;
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
                                            $badgeColor = $statusColors[$status] ?? 'bg-gray-100 text-gray-800';
                                            $label = $statusLabels[$status] ?? ucfirst($status);
                                        @endphp
                                        <span
                                            class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-md {{ $badgeColor }}">
                                            {{ $label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-row justify-center items-center gap-1">
                                            <a href="{{ route('desktops.show', $desktop->id) }}"
                                                onclick="showFullScreenLoader();">
                                                <x-buttons.action-button text="Detail" color="purple" />
                                            </a>
                                            @can('desktops.edit')
                                                <a href="{{ route('desktops.edit', $desktop->id) }}"
                                                    onclick="showFullScreenLoader();">
                                                    <x-buttons.action-button text="Edit" color="blue" />
                                                </a>
                                            @endcan
                                            @can('desktops.delete')
                                                <form action="{{ route('desktops.destroy', $desktop->id) }}" method="POST"
                                                    onsubmit="return confirmAndLoad('Are you sure to delete this desktop?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <x-buttons.action-button text="Delete" color="red" />
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                        No desktops found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Polling footer -->
        <div id="pollFooterDesktop"
            class="mt-2 flex items-center justify-between text-[11px] sm:text-xs text-gray-500 dark:text-gray-400 px-4">
            <span class="inline-flex items-center">
                <span id="pollDotDesktop" class="inline-block w-2 h-2 rounded-full bg-green-500 mr-2"></span>
                <span id="pollLabelDesktop">Polling active</span>
            </span>
            <span>Last update: <span id="lastUpdatedDesktop">—</span></span>
        </div>

        <x-per-page-selector :route="'desktops.index'" :perPage="$perPage" :search="$search" :items="$desktops" />
    </x-dashboard.sidebar>
    @include('desktops.upload-modal')

    @push('scripts')
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const tbody = document.getElementById('desktopBody');
                if (!tbody) return;

                const list = new List('desktopList', {
                    valueNames: ['number', 'hostname', 'serialnumber', 'model', 'brand', 'user', 'location',
                        'typewindows', 'osstatus', 'iprealvnc', 'status'
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

                        // delay biar DOM update dulu
                        setTimeout(renumber, 50);
                    });
                });

                // Polling setup (sama kayak laptop)
                const pollDot = document.getElementById('pollDotDesktop');
                const pollLbl = document.getElementById('pollLabelDesktop');
                const lastLbl = document.getElementById('lastUpdatedDesktop');

                function setUI(mode) {
                    if (mode === 'active') {
                        pollDot.className = 'inline-block w-2 h-2 rounded-full bg-green-500 mr-2';
                        pollLbl.textContent = 'Polling active';
                    } else if (mode === 'paused') {
                        pollDot.className = 'inline-block w-2 h-2 rounded-full bg-gray-400 mr-2';
                        pollLbl.textContent = 'Paused';
                    } else {
                        pollDot.className = 'inline-block w-2 h-2 rounded-full bg-amber-500 mr-2';
                        pollLbl.textContent = 'Idle';
                    }
                }

                function updateLast() {
                    lastLbl.textContent = new Date().toLocaleString('id-ID');
                }

                let lastTs = tbody.dataset.lastTs || new Date().toISOString();
                let baseOffset = parseInt(tbody.dataset.baseOffset || '0', 10);
                const changesUrl = tbody.dataset.changesUrl;
                const rowsUrl = tbody.dataset.rowsUrl;
                let idle = 0,
                    timer = null;
                const baseInt = 5000,
                    maxInt = 30000;

                function renumber() {
                    const rows = tbody.querySelectorAll('tr[data-id]');
                    let i = 0;
                    rows.forEach(tr => {
                        const cell = tr.querySelector('.number');
                        if (cell) cell.textContent = (baseOffset + (++i)).toString();
                    });
                    list.reIndex();
                }

                async function tick() {
                    try {
                        if (document.hidden) {
                            setUI('paused');
                            schedule(maxInt);
                            return;
                        }
                        setUI('active');
                        const u = new URL(changesUrl, window.location.origin);
                        u.searchParams.set('since', lastTs);
                        tbody.querySelectorAll('tr[data-id]').forEach(tr => {
                            u.searchParams.append('visible[]', tr.getAttribute('data-id'));
                        });
                        const res = await fetch(u.toString(), {
                            headers: {
                                'Accept': 'application/json'
                            }
                        });
                        if (!res.ok) throw new Error('fetch fail');
                        const j = await res.json();
                        const {
                            latest_ts,
                            created = [],
                            updated = [],
                            deleted = []
                        } = j || {};
                        let changed = false;
                        deleted.forEach(id => {
                            const row = tbody.querySelector(`tr[data-id="${id}"]`);
                            if (row) row.remove();
                        });
                        if (deleted.length) changed = true;
                        const need = [...new Set([...created, ...updated])];
                        if (need.length) {
                            const ru = new URL(rowsUrl, window.location.origin);
                            need.forEach(id => ru.searchParams.append('ids[]', id));
                            const html = await (await fetch(ru.toString(), {
                                headers: {
                                    'Accept': 'text/html'
                                }
                            })).text();
                            const tpl = document.createElement('template');
                            tpl.innerHTML = html.trim();
                            const fresh = Array.from(tpl.content.querySelectorAll('tr[data-id]'));
                            fresh.forEach(newRow => {
                                const id = newRow.getAttribute('data-id');
                                const old = tbody.querySelector(`tr[data-id="${id}"]`);
                                if (old) old.replaceWith(newRow);
                                else tbody.insertBefore(newRow, tbody.firstChild);
                            });
                            changed = true;
                        }
                        if (latest_ts) lastTs = latest_ts;
                        updateLast();
                        if (changed) {
                            renumber();
                            idle = 0;
                            setUI('active');
                            schedule(baseInt);
                        } else {
                            idle++;
                            setUI('idle');
                            schedule(Math.min(baseInt + idle * 5000, maxInt));
                        }
                    } catch (e) {
                        setUI('idle');
                        schedule(maxInt);
                    }
                }

                function schedule(ms) {
                    clearTimeout(timer);
                    timer = setTimeout(tick, ms);
                }
                document.addEventListener('visibilitychange', () => {
                    if (!document.hidden) {
                        idle = 0;
                        setUI('active');
                        schedule(200);
                    }
                });
                updateLast();
                renumber();
                schedule(baseInt);
            });
        </script>
    @endpush
</x-app-layout>
