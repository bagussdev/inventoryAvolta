<tbody id="equipments-body" class="list whitespace-nowrap">
    <tr id="loading-indicator" class="hidden">
        <td colspan="8" class="text-center py-4 text-sm text-gray-500">
            <svg aria-hidden="true" role="status" class="inline w-5 h-5 mr-2 text-gray-200 animate-spin fill-purple-600"
                viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                    fill="currentColor" />
                <path
                    d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.544 70.6331 15.2552C75.2735 17.9665 79.3347 21.5619 82.5849 25.841C84.9175 28.9125 86.7992 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                    fill="currentFill" />
            </svg>
            Loading data...
        </td>
    </tr>

    @forelse ($equipments as $equipment)
        <tr
            class="border-b dark:border-gray-700 {{ $loop->odd ? 'bg-white dark:bg-gray-900' : 'bg-gray-50 dark:bg-gray-800' }}">
            <td class="px-4 py-2 md:px-6 md:py-3 no">{{ $loop->iteration }}</td>
            <td class="px-4 py-2 md:px-6 md:py-3 name capitalize">{{ strtolower($equipment->item->name ?? '-') }}</td>
            <td class="px-4 py-2 md:px-6 md:py-3 sn capitalize">{{ $equipment->serial_number ?? '-' }}</td>
            <td class="px-4 py-2 md:px-6 md:py-3 sn capitalize">{{ $equipment->alias ?? '-' }}</td>
            <td class="px-4 py-2 md:px-6 md:py-3 qty">{{ $equipment->transaction->qty ?? '-' }}</td>
            <td class="px-4 py-2 md:px-6 md:py-3 location capitalize">{{ strtolower($equipment->store->name ?? '-') }}
            </td>
            <td class="px-4 py-2 md:px-6 md:py-3 status">
                @php
                    $status = strtolower($equipment->status);
                    $color = match ($status) {
                        'available' => 'bg-green-100 text-green-800',
                        'maintenance' => 'bg-red-100 text-red-800',
                        'used' => 'bg-yellow-100 text-yellow-600',
                        default => 'bg-gray-100 text-gray-600',
                    };
                @endphp
                <span class="px-3 py-1 text-xs font-medium rounded-md {{ $color }}">
                    {{ ucfirst($equipment->status) }}
                </span>
            </td>

            @if (!Auth::user()->role_id === 5)
                <td class="px-4 py-2 md:px-6 md:py-3 transaction">
                    <a href="{{ route('transactions.show', $equipment->transactions_id) }}"
                        onclick="showFullScreenLoader();" class="text-sm text-blue-600 hover:underline">
                        #{{ $equipment->transactions_id }}
                    </a>
                </td>
            @endif

            <td class="px-4 py-2 md:px-6 md:py-3">
                <div class="flex flex-row items-center justify-center gap-1">
                    @can('equipments.migrate')
                        <x-buttons.action-button text="Migrate" color="green"
                            href="{{ route('equipments.migrate.form', $equipment->id) }}"
                            onclick="showFullScreenLoader()" />
                    @endcan
                    <x-buttons.action-button text="Detail" color="purple"
                        href="{{ route('equipments.show', $equipment->id) }}" onclick="showFullScreenLoader()" />
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="8" class="text-center py-4 text-xs">No equipment found.</td>
        </tr>
    @endforelse
</tbody>
