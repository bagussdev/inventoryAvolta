@forelse ($laptops as $laptop)
    <tr data-id="{{ $laptop->id }}">
        <td class="px-4 py-3 number"></td>
        <td class="px-4 py-3 hostname">{{ $laptop->hostname }}</td>
        <td class="px-4 py-3 serialnumber">{{ $laptop->serialnumber }}</td>
        <td class="px-4 py-3 model">{{ $laptop->model }}</td>
        <td class="px-4 py-3 brand text-center">{{ $laptop->brand }}</td>
        <td class="px-4 py-3 user">{{ $laptop->user ?? '-' }}</td>
        <td class="px-4 py-3 location">{{ $laptop->store->name ?? '-' }}</td>
        <td class="px-4 py-3 typewindows">{{ $laptop->typewindows ?? '-' }}</td>
        <td class="px-4 py-3 osstatus">{{ $laptop->osstatus ?? '-' }}</td>
        <td class="px-4 py-3 iprealvnc">{{ $laptop->iprealvnc ?? '-' }}</td>
        <td class="px-4 py-3 status">
            @php
                $status = $laptop->status; // ganti $laptop → $desktop kalau di desktop
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
                <a href="{{ route('laptops.show', $laptop->id) }}" onclick="showFullScreenLoader();">
                    <x-buttons.action-button text="Detail" color="purple" />
                </a>
                @can('laptops.edit')
                    <a href="{{ route('laptops.edit', $laptop->id) }}" onclick="showFullScreenLoader();">
                        <x-buttons.action-button text="Edit" color="blue" />
                    </a>
                @endcan
                @can('laptops.delete')
                    <form action="{{ route('laptops.destroy', $laptop->id) }}" method="POST"
                        onsubmit="return confirmAndLoad('Are you sure to delete this laptop?')">
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
        <td colspan="12" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">No
            laptops found.</td>
    </tr>
@endforelse
