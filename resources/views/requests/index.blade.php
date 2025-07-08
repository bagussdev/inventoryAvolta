<x-app-layout>
    <x-dashboard.sidebar>
        <x-alert-information></x-alert-information>

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mt-10 w-full gap-4">
            <h2 class="font-bold text-xl sm:text-2xl whitespace-nowrap truncate">
                Request List
            </h2>

            <div class="flex flex-wrap gap-3 items-center">
                <form id="filterForm" method="GET" action="{{ route('requests.index') }}" class="flex gap-2 items-center"
                    onsubmit="showFullScreenLoader();">
                    <x-date-filter-dropdown :action="route('requests.index')" :startDate="request('start_date')" :endDate="request('end_date')" formId="filterForm" />

                    <a href="{{ route('requests.export', array_merge(request()->query(), ['export' => 1])) }}"
                        class="text-xs sm:text-sm px-3 py-1.5 sm:px-4 sm:py-2 text-white bg-gray-600 hover:bg-gray-700 focus:ring-4 focus:ring-gray-300 font-medium rounded-md focus:outline-none dark:bg-gray-500 dark:hover:bg-gray-600 dark:focus:ring-gray-700 text-center">
                        Excel
                    </a>

                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..."
                        class="text-xs sm:text-sm px-2 py-1.5 sm:px-3 sm:py-2 rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-purple-500 w-full sm:w-44" />
                    <input type="hidden" name="per_page" value="{{ $perPage ?? 5 }}">
                    <button type="submit"
                        class="text-xs sm:text-sm px-3 py-1.5 sm:px-3 sm:py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition">
                        Search
                    </button>
                </form>
                <a href="{{ route('requests.create') }}" onclick="showFullScreenLoader();"
                    class="text-xs sm:text-sm px-3 py-1.5 sm:px-4 sm:py-2 text-white bg-purple-600 hover:bg-purple-700 focus:ring-4 focus:ring-purple-300 font-medium rounded-md focus:outline-none dark:bg-purple-500 dark:hover:bg-purple-600 dark:focus:ring-purple-700 text-center">
                    Create New Request
                </a>
            </div>
        </div>

        <hr class="h-[3px] my-8 bg-gray-200 border-0 dark:bg-gray-700 w-full">

        <div class="relative overflow-x-auto shadow-md sm:rounded-lg mt-5" id="request-list">
            <table class="min-w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400 text-center">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th class="px-5 py-4 cursor-pointer sort" data-sort="no">No</th>
                        <th class="px-5 py-4 cursor-pointer sort" data-sort="id">Request ID</th>
                        <th class="px-5 py-4 cursor-pointer sort" data-sort="report">Reported By</th>
                        <th class="px-5 py-4 cursor-pointer sort" data-sort="department">Report To</th>
                        <th class="px-5 py-4 cursor-pointer sort" data-sort="item">Item Req</th>
                        <th class="px-5 py-4 cursor-pointer sort" data-sort="item">qty</th>
                        <th class="px-5 py-4 cursor-pointer sort" data-sort="location">Location</th>
                        <th class="px-5 py-4 cursor-pointer sort" data-sort="date">Start</th>
                        <th class="px-5 py-4 cursor-pointer sort" data-sort="status">Status</th>
                        <th class="px-5 py-4">Action</th>
                    </tr>
                </thead>
                <tbody class="list">
                    @foreach ($requests as $request)
                        <tr
                            class="border-b dark:border-gray-700 {{ $loop->odd ? 'bg-white dark:bg-gray-900' : 'bg-gray-50 dark:bg-gray-800' }}">
                            <td class="px-5 py-4 no">{{ $loop->iteration }}</td>
                            <td class="px-5 py-4 id">{{ $request->unique_id }}</td>
                            <td class="px-5 py-4 report">{{ $request->user->name ?? '-' }}</td>
                            <td class="px-5 py-4 department">{{ $request->department->name ?? '-' }}</td>
                            <td class="px-5 py-4 item">{{ $request->item_request }}</td>
                            <td class="px-5 py-4 item">{{ $request->qty }}</td>
                            <td class="px-5 py-4 location">{{ $request->store->site_code ?? '-' }}</td>
                            <td class="px-5 py-4 date">
                                {{ \Carbon\Carbon::parse($request->created_at)->format('d M Y') }}</td>
                            <td class="px-5 py-4 status">
                                @php
                                    $status = strtolower($request->status);
                                    $color = match ($status) {
                                        'resolved' => 'bg-green-100 text-green-800',
                                        'completed' => 'bg-green-100 text-green-800',
                                        'in progress' => 'bg-blue-100 text-blue-800',
                                        'pending' => 'bg-red-100 text-red-800',
                                        'waiting' => 'bg-yellow-100 text-yellow-600',
                                        default => 'bg-gray-100 text-gray-600',
                                    };
                                @endphp
                                <span
                                    class="inline-block px-2 py-0.5 sm:px-3 sm:py-1 text-[12px] sm:text-sm font-semibold rounded-md {{ $color }}">
                                    {{ ucfirst($request->status) }}
                                </span>
                            </td>
                            @php
                                $roleName = strtolower(Auth::user()->role->name ?? '');
                            @endphp

                            <td class="px-5 py-4">
                                <div class="flex flex-col sm:flex-row flex-wrap gap-2 justify-center items-center">

                                    {{-- ROLE: USER -- tampilkan tombol Edit jika status masih waiting --}}
                                    @if ($roleName === 'user' && $request->status === 'waiting')
                                        @can('request.edit')
                                            <x-buttons.action-button text="Edit" color="green"
                                                href="{{ route('requests.edit', $request->id) }}"
                                                onclick="showFullScreenLoader();" />
                                        @endcan
                                    @endif

                                    {{-- ROLE: STAFF atau SPV --}}
                                    @if (in_array($roleName, ['staff', 'spv']))
                                        @if ($request->status === 'waiting')
                                            @can('request.proses')
                                                <form method="POST" action="{{ route('requests.start', $request->id) }}"
                                                    onsubmit="return confirmAndLoad('Start progress for this request?')">
                                                    @csrf
                                                    <x-buttons.action-button text="Proses" color="blue" />
                                                </form>
                                            @endcan
                                        @elseif ($request->status === 'pending')
                                            @can('request.proses')
                                                <form method="POST" action="{{ route('requests.restart', $request->id) }}"
                                                    onsubmit="return confirmAndLoad('Continue progress for this request?')">
                                                    @csrf
                                                    <x-buttons.action-button text="Continue" color="yellow" />
                                                </form>
                                            @endcan
                                        @elseif ($request->status === 'in progress')
                                            @can('request.resolve')
                                                <x-buttons.action-button text="Confirm" color="blue"
                                                    href="{{ route('requests.resolve', $request->id) }}"
                                                    onclick="showFullScreenLoader();" />
                                            @endcan
                                        @elseif ($request->status === 'resolved')
                                            @can('request.closed')
                                                <form method="POST"
                                                    action="{{ route('requests.complete', $request->id) }}"
                                                    onsubmit="return confirmAndLoad('Mark this request as completed?')">
                                                    @csrf
                                                    <x-buttons.action-button text="Closed" color="red" />
                                                </form>
                                            @endcan
                                        @endif
                                    @endif

                                    {{-- ROLE: MASTER --}}
                                    @if ($roleName === 'master')
                                        {{-- Aksi sesuai status --}}
                                        @if ($request->status === 'waiting')
                                            <form method="POST" action="{{ route('requests.start', $request->id) }}"
                                                onsubmit="return confirmAndLoad('Start progress for this request?')">
                                                @csrf
                                                <x-buttons.action-button text="Proses" color="blue" />
                                            </form>
                                            {{-- Tampilkan tombol Edit untuk semua request --}}
                                            <x-buttons.action-button text="Edit" color="green"
                                                href="{{ route('requests.edit', $request->id) }}"
                                                onclick="showFullScreenLoader();" />
                                        @elseif ($request->status === 'pending')
                                            <form method="POST" action="{{ route('requests.restart', $request->id) }}"
                                                onsubmit="return confirmAndLoad('Continue progress for this request?')">
                                                @csrf
                                                <x-buttons.action-button text="Continue" color="yellow" />
                                            </form>
                                        @elseif ($request->status === 'in progress')
                                            <x-buttons.action-button text="Confirm" color="blue"
                                                href="{{ route('requests.resolve', $request->id) }}"
                                                onclick="showFullScreenLoader();" />
                                        @elseif ($request->status === 'resolved')
                                            <form method="POST"
                                                action="{{ route('requests.complete', $request->id) }}"
                                                onsubmit="return confirmAndLoad('Mark this request as completed?')">
                                                @csrf
                                                <x-buttons.action-button text="Closed" color="red" />
                                            </form>
                                        @endif
                                    @endif

                                    {{-- Tombol DETAIL selalu muncul --}}
                                    <x-buttons.action-button text="Detail" color="purple"
                                        href="{{ route('requests.show', $request->id) }}"
                                        onclick="showFullScreenLoader();" />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-6 px-4 pb-3 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div>
                    {{ $requests->appends(request()->query())->links() }}
                </div>
                <div class="flex items-center gap-4 flex-wrap justify-end">
                    <form method="GET" action="{{ route('requests.index') }}" onsubmit="showFullScreenLoader();">
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
        </div>

        @push('scripts')
            <script>
                const requestList = new List('request-list', {
                    valueNames: ['no', 'id', 'report', 'department', 'item', 'location', 'date', 'status']
                });
            </script>
        @endpush

    </x-dashboard.sidebar>
</x-app-layout>
