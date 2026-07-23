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
            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-md {{ $badgeColor }}">
                {{ $label }}
            </span>
        </td>
        <td class="px-4 py-3">
            <div class="flex flex-row justify-center items-center gap-1">
                <a href="{{ route('desktops.show', $desktop->id) }}" onclick="showFullScreenLoader();">
                    <x-buttons.action-button text="Detail" color="purple" />
                </a>
                @can('desktops.edit')
                    <a href="{{ route('desktops.edit', $desktop->id) }}" onclick="showFullScreenLoader();">
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
