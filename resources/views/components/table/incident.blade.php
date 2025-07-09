<div class="w-full overflow-x-auto">
    <div class="min-w-full inline-block align-middle">
        <div class="overflow-hidden shadow sm:rounded-lg" id="incident-list">
            <table
                class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs sm:text-sm text-center text-gray-600 dark:text-gray-300">
                <thead
                    class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-700 dark:text-gray-400 whitespace-nowrap">
                    <tr>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="no">No</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="id">Incident ID</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="report">Reported By</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="department">Department</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="equipment">Equipment</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="location">Location</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="date">Start Date</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="staff">PIC Staff</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="status">Status</th>
                        <th class="px-4 py-2 md:px-6 md:py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="list whitespace-nowrap">
                    @forelse ($incidents as $incident)
                        <tr
                            class="border-b dark:border-gray-700 {{ $loop->odd ? 'bg-white dark:bg-gray-900' : 'bg-gray-50 dark:bg-gray-800' }}">
                            <td class="px-4 py-2 md:px-6 md:py-3 no">{{ $loop->iteration }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 id">{{ $incident->unique_id ?? '-' }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 report">{{ $incident->user->name ?? '-' }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 department">{{ $incident->department->name ?? '-' }}
                            </td>
                            <td class="px-4 py-2 md:px-6 md:py-3 equipment">
                                {{ $incident->equipment->item->name ?? '-' }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 location">{{ $incident->store->site_code ?? '-' }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 date">
                                {{ \Carbon\Carbon::parse($incident->created_at)->format('d M Y') }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 staff">{{ $incident->picUser->name ?? '-' }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 status">
                                @php
                                    $status = strtolower($incident->status);
                                    $color = match ($status) {
                                        'resolved' => 'bg-green-100 text-green-800',
                                        'completed' => 'bg-green-100 text-green-800',
                                        'in progress' => 'bg-blue-100 text-blue-800',
                                        'pending' => 'bg-red-100 text-red-800',
                                        'waiting' => 'bg-yellow-100 text-yellow-600',
                                        default => 'bg-gray-100 text-gray-600',
                                    };
                                @endphp
                                <span class="px-3 py-1 text-xs font-medium rounded-md {{ $color }}">
                                    {{ ucfirst($incident->status) }}
                                </span>
                            </td>
                            @php
                                $roleName = strtolower(Auth::user()->role->name ?? '');
                            @endphp
                            <td class="px-5 py-4">
                                <div class="flex flex-col sm:flex-row items-center justify-center gap-1">

                                    {{-- ROLE: USER -- tampilkan tombol Edit jika status masih waiting --}}
                                    @if ($roleName === 'user' && $incident->status === 'waiting')
                                        @can('incident.edit')
                                            <x-buttons.action-button text="Edit" color="green"
                                                href="{{ route('incidents.edit', $incident->id) }}"
                                                onclick="showFullScreenLoader();" />
                                        @endcan
                                    @endif

                                    {{-- ROLE: STAFF atau SPV --}}
                                    @if (in_array($roleName, ['staff', 'spv']))
                                        @if ($incident->status === 'waiting')
                                            @can('incident.proses')
                                                <form method="POST" action="{{ route('incidents.start', $incident->id) }}"
                                                    onsubmit="return confirmAndLoad('Start progress for this incident?')">
                                                    @csrf
                                                    <x-buttons.action-button text="Proses" color="blue" />
                                                </form>
                                            @endcan
                                        @elseif ($incident->status === 'pending')
                                            @can('incident.proses')
                                                <form method="POST"
                                                    action="{{ route('incidents.restart', $incident->id) }}"
                                                    onsubmit="return confirmAndLoad('Continue progress for this incident?')">
                                                    @csrf
                                                    <x-buttons.action-button text="Continue" color="yellow" />
                                                </form>
                                            @endcan
                                        @elseif ($incident->status === 'in progress')
                                            @can('incident.resolve')
                                                <x-buttons.action-button text="Confirm" color="blue"
                                                    href="{{ route('incidents.resolve', $incident->id) }}"
                                                    onclick="showFullScreenLoader();" />
                                            @endcan
                                        @elseif ($incident->status === 'resolved')
                                            @can('incident.closed')
                                                <form method="POST"
                                                    action="{{ route('incidents.complete', $incident->id) }}"
                                                    onsubmit="return confirmAndLoad('Mark this incident as completed?')">
                                                    @csrf
                                                    <x-buttons.action-button text="Closed" color="red" />
                                                </form>
                                            @endcan
                                        @endif
                                    @endif

                                    {{-- ROLE: MASTER --}}
                                    @if ($roleName === 'master')
                                        {{-- Aksi sesuai status --}}
                                        @if ($incident->status === 'waiting')
                                            <form method="POST" action="{{ route('incidents.start', $incident->id) }}"
                                                onsubmit="return confirmAndLoad('Start progress for this incident?')">
                                                @csrf
                                                <x-buttons.action-button text="Proses" color="blue" />
                                            </form>
                                            {{-- Tampilkan tombol Edit untuk semua incident --}}
                                            <x-buttons.action-button text="Edit" color="green"
                                                href="{{ route('incidents.edit', $incident->id) }}"
                                                onclick="showFullScreenLoader();" />
                                        @elseif ($incident->status === 'pending')
                                            <form method="POST"
                                                action="{{ route('incidents.restart', $incident->id) }}"
                                                onsubmit="return confirmAndLoad('Continue progress for this incident?')">
                                                @csrf
                                                <x-buttons.action-button text="Continue" color="yellow" />
                                            </form>
                                        @elseif ($incident->status === 'in progress')
                                            <x-buttons.action-button text="Confirm" color="blue"
                                                href="{{ route('incidents.resolve', $incident->id) }}"
                                                onclick="showFullScreenLoader();" />
                                        @elseif ($incident->status === 'resolved')
                                            <form method="POST"
                                                action="{{ route('incidents.complete', $incident->id) }}"
                                                onsubmit="return confirmAndLoad('Mark this incident as completed?')">
                                                @csrf
                                                <x-buttons.action-button text="Closed" color="red" />
                                            </form>
                                        @endif
                                    @endif

                                    {{-- Tombol DETAIL selalu muncul --}}
                                    <x-buttons.action-button text="Detail" color="purple"
                                        href="{{ route('incidents.show', $incident->id) }}"
                                        onclick="showFullScreenLoader();" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4 text-xs">No incident data found.</td>
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
            {{ $incidents->appends(request()->query())->links() }}
        </div>
        <div class="flex items-center gap-4 flex-wrap justify-end">
            <form method="GET" action="{{ route('incidents.index') }}">
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

@push('scripts')
    <script>
        const incidentList = new List('incident-list', {
            valueNames: ['no', 'id', 'report', 'department', 'equipment', 'location', 'date', 'staff', 'status']
        });
    </script>
@endpush
