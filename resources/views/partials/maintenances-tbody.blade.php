<tbody class="list whitespace-nowrap" id="maintenance-tbody">
    @if ($maintenances->isEmpty())
        <tr id="maintenance-notfound">
            <td colspan="8" class="text-center py-4 text-xs text-gray-500">
                No maintenance data found.
            </td>
        </tr>
    @else
        @foreach ($maintenances as $maintenance)
            <tr
                class="border-b dark:border-gray-700 {{ $loop->odd ? 'bg-white dark:bg-gray-900' : 'bg-gray-50 dark:bg-gray-800' }}">
                <td class="px-4 py-2 md:px-6 md:py-3 no">{{ $loop->iteration }}</td>
                <td class="px-4 py-2 md:px-6 md:py-3 name">
                    <a href="{{ route('maintenances.show', $maintenance->id) }}" class="text-purple-600 hover:underline">
                        {{ 'MNT' . str_pad($maintenance->id, 5, '0', STR_PAD_LEFT) }}
                    </a>
                </td>
                <td class="px-4 py-2 md:px-6 md:py-3 model">
                    {{ ucwords(
                        strtolower(
                            optional(optional($maintenance->equipment)->item)->name .
                                (!empty($maintenance->equipment->alias) || !empty($maintenance->item_description)
                                    ? ' - ' . ($maintenance->equipment->alias ?? $maintenance->item_description)
                                    : ''),
                        ),
                    ) }}
                </td>
                <td class="px-4 py-2 md:px-6 md:py-3 store">
                    {{ $maintenance->equipment->store->name ?? '-' }}
                </td>
                <td class="px-4 py-2 md:px-6 md:py-3 date">
                    {{ \Carbon\Carbon::parse($maintenance->maintenance_date)->format('d M Y') }}
                </td>
                <td class="px-4 py-2 md:px-6 md:py-3 freq">{{ ucfirst($maintenance->frequensi) }}</td>
                <td class="px-4 py-2 md:px-6 md:py-3 staff">
                    @if ($maintenance->staff)
                        <button
                            onclick="showUserModal({{ json_encode([
                                'name' => $maintenance->staff->name ?? '-',
                                'location' => optional($maintenance->staff->location)->name ?? '-',
                                'email' => $maintenance->staff->email ?? '-',
                                'phone' => $maintenance->staff->no_telfon ?? '',
                            ]) }})"
                            class="text-purple-600 hover:underline">
                            {{ $maintenance->staff->name }}
                        </button>
                    @else
                        -
                    @endif
                </td>
                <td class="px-4 py-2 md:px-6 md:py-3 status">
                    {{-- Status badge --}}
                    @php
                        $status = strtolower($maintenance->status);
                        $color = match ($status) {
                            'completed' => 'bg-green-100 text-green-800',
                            'maintenance' => 'bg-red-100 text-red-600',
                            'in progress' => 'bg-blue-100 text-blue-800',
                            'resolved' => 'bg-purple-100 text-purple-800',
                            default => 'bg-gray-100 text-gray-600',
                        };
                    @endphp
                    <span class="px-3 py-1 text-xs font-medium rounded-md {{ $color }}">
                        {{ ucfirst($maintenance->status) }}
                    </span>
                </td>
                <td class="px-4 py-2 md:px-6 md:py-3">
                    <div class="flex flex-row items-center justify-center gap-1">
                        {{-- Tindakan berdasarkan status --}}
                        @if ($maintenance->status === 'maintenance')
                            @can('maintenance.proses')
                                <form action="{{ route('maintenances.proses', $maintenance->id) }}" method="GET"
                                    onsubmit="return confirmAndLoad('Are you sure to process?')">
                                    <x-buttons.action-button text="Proses" color="green" />
                                </form>
                            @endcan
                        @elseif ($maintenance->status === 'completed')
                            @can('maintenance.edit')
                                <x-buttons.action-button text="Edit" color="blue"
                                    href="{{ route('maintenances.editCompleted', $maintenance->id) }}"
                                    onclick="showFullScreenLoader();" />
                            @endcan
                        @elseif ($maintenance->status === 'in progress')
                            @can('maintenance.confirm')
                                <x-buttons.action-button text="Confirm" color="blue"
                                    href="{{ route('maintenances.confirm', $maintenance->id) }}"
                                    onclick="showFullScreenLoader();" />
                            @endcan
                        @elseif ($maintenance->status === 'resolved')
                            @can('maintenance.closed')
                                <form action="{{ route('maintenances.closed', $maintenance->id) }}" method="GET"
                                    onsubmit="return confirmAndLoad('Are you sure to closed?')">
                                    <x-buttons.action-button text="Closed" color="red" />
                                </form>
                            @endcan
                        @else
                            @can('schedulemaintenance.edit')
                                <x-buttons.action-button text="Edit" color="blue"
                                    href="{{ route('maintenances.edit', $maintenance->id) }}"
                                    onclick="showFullScreenLoader();" />
                            @endcan
                        @endif

                        <x-buttons.action-button text="Detail" color="purple"
                            href="{{ route('maintenances.show', $maintenance->id) }}"
                            onclick="showFullScreenLoader();" />
                    </div>
                </td>
            </tr>
        @endforeach
    @endif
</tbody>
