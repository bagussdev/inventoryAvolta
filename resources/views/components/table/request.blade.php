<div class="w-full overflow-x-auto">
    <div class="min-w-full inline-block align-middle">
        <div class="overflow-hidden shadow sm:rounded-lg" id="request-list">
            <table
                class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs sm:text-sm text-center text-gray-600 dark:text-gray-300">
                <thead
                    class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-700 dark:text-gray-400 whitespace-nowrap">
                    <tr>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="no">No</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="id">Request ID</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="report">Reported By</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="department">Report To</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="item">Item Req</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="qty">Qty</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="location">Location</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="date">Start</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="status">Status</th>
                        <th class="px-4 py-2 md:px-6 md:py-3">Action</th>
                    </tr>
                </thead>
                <tbody class="list whitespace-nowrap">
                    @foreach ($requests as $request)
                        <tr
                            class="border-b dark:border-gray-700 {{ $loop->odd ? 'bg-white dark:bg-gray-900' : 'bg-gray-50 dark:bg-gray-800' }}">
                            <td class="px-4
                            py-2 md:px-6 md:py-3 no">{{ $loop->iteration }}
                            </td>
                            <td class="px-4 py-2 md:px-6 md:py-3 id">{{ $request->unique_id }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 report">{{ $request->user->name ?? '-' }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 department">{{ $request->department->name ?? '-' }}
                            </td>
                            <td class="px-4 py-2 md:px-6 md:py-3 item">{{ $request->item_request }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 qty">{{ $request->qty }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 location">{{ $request->store->site_code ?? '-' }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 date">
                                {{ \Carbon\Carbon::parse($request->created_at)->format('d M Y') }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 status">
                                @php
                                    $status = strtolower($request->status);
                                    $color = match ($status) {
                                        'resolved', 'completed' => 'bg-green-100 text-green-800',
                                        'in progress' => 'bg-blue-100 text-blue-800',
                                        'pending' => 'bg-red-100 text-red-800',
                                        'waiting' => 'bg-yellow-100 text-yellow-600',
                                        default => 'bg-gray-100 text-gray-600',
                                    };
                                @endphp
                                <span class="inline-block px-3 py-1 text-xs font-medium rounded-md {{ $color }}">
                                    {{ ucfirst($request->status) }}
                                </span>
                            </td>
                            @php
                                $roleName = strtolower(Auth::user()->role->name ?? '');
                            @endphp
                            <td class="px-4 py-2 md:px-6 md:py-3">
                                <div class="flex flex-col sm:flex-row items-center justify-center gap-1">

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
            @if ($showPagination)
                <div class="mt-6 px-4 pb-3 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div>
                        {{ $requests->appends(request()->query())->links() }}
                    </div>
                    <div class="flex items-center gap-4 flex-wrap justify-end">
                        <form method="GET" action="{{ route('requests.index') }}" onsubmit="showFullScreenLoader();">
                            <input type="hidden" name="search" value="{{ request('search') }}">
                            <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                            <input type="hidden" name="end_date" value="{{ request('end_date') }}">
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
        </div>
    </div>
</div>

@push('scripts')
    <script>
        const requestList = new List('request-list', {
            valueNames: ['no', 'id', 'report', 'department', 'item', 'qty', 'location', 'date', 'status']
        });
    </script>
@endpush
