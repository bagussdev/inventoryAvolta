<x-app-layout>
    <x-dashboard.sidebar>
        <x-alert-information />

        <div class="mt-4">
            <a href="{{ route('items.index') }}" onclick="showFullScreenLoader();"
                class="inline-flex items-center text-gray-700 hover:text-purple-600 dark:text-gray-300 dark:hover:text-white transition duration-150 ease-in-out">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 32 32" class="mr-2">
                    <path fill="#101820"
                        d="M30,29a1,1,0,0,1-.81-.41l-2.12-2.92A18.66,18.66,0,0,0,15,18.25V22a1,1,0,0,1-1.6.8l-12-9a1,1,0,0,1,0-1.6l12-9A1,1,0,0,1,15,4V8.24A19,19,0,0,1,31,27v1a1,1,0,0,1-.69.95A1.12,1.12,0,0,1,30,29ZM14,16.11h.1A20.68,20.68,0,0,1,28.69,24.5l.16.21a17,17,0,0,0-15-14.6,1,1,0,0,1-.89-1V6L3.67,13,13,20V17.11a1,1,0,0,1,.33-.74A1,1,0,0,1,14,16.11Z" />
                </svg>
                <span class="text-sm font-medium">Back</span>
            </a>
        </div>

        <div class="flex justify-between items-center mt-4 w-full max-w-full">
            <h2 class="font-bold text-xl sm:text-2xl">Item Detail</h2>
        </div>

        <hr class="h-[3px] my-4 bg-gray-200 border-0 dark:bg-gray-700 w-full">

        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md text-sm text-gray-700 dark:text-gray-300">
            {{-- MOBILE VERSION --}}
            <div class="block md:hidden space-y-4">
                <div class="grid grid-cols-1 gap-3">
                    <div class="flex">
                        <div class="w-40 font-medium">Item Name</div>
                        <div class="flex-1">: {{ $item->name }}</div>
                    </div>
                    <div class="flex">
                        <div class="w-40 font-medium">Type</div>
                        <div class="flex-1">: {{ $item->type }}</div>
                    </div>
                    <div class="flex">
                        <div class="w-40 font-medium">Brand</div>
                        <div class="flex-1">: {{ $item->brand }}</div>
                    </div>
                    <div class="flex">
                        <div class="w-40 font-medium">Model</div>
                        <div class="flex-1">: {{ $item->model }}</div>
                    </div>
                    <div class="flex">
                        <div class="w-40 font-medium">Category</div>
                        <div class="flex-1">: {{ $item->category }}</div>
                    </div>
                </div>
            </div>

            {{-- DESKTOP VERSION --}}
            <div class="hidden md:grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Item Name</label>
                    <div class="px-3 py-2 bg-gray-100 dark:bg-gray-700 rounded-md">{{ $item->name }}</div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Type</label>
                    <div class="px-3 py-2 bg-gray-100 dark:bg-gray-700 rounded-md">{{ $item->type }}</div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Brand</label>
                    <div class="px-3 py-2 bg-gray-100 dark:bg-gray-700 rounded-md">{{ $item->brand }}</div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Model</label>
                    <div class="px-3 py-2 bg-gray-100 dark:bg-gray-700 rounded-md">{{ $item->model }}</div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Category</label>
                    <div class="px-3 py-2 bg-gray-100 dark:bg-gray-700 rounded-md">{{ $item->category }}</div>
                </div>
            </div>
        </div>

        @if ($item->category === 'equipment')
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md mt-6">
                <h3 class="font-semibold text-lg mb-4 text-gray-800 dark:text-white">Equipments List</h3>

                @php
                    $equipments = $item->equipment;
                @endphp

                @if ($equipments->isEmpty())
                    <p class="text-sm text-gray-500 italic">No equipments found.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-max text-sm text-left border border-gray-200 dark:border-gray-700">
                            <thead class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                <tr>
                                    <th class="px-4 py-2 border">Serial Number</th>
                                    <th class="px-4 py-2 border">Location</th>
                                    <th class="px-4 py-2 border">Status</th>
                                    <th class="px-4 py-2 border">Transaction</th>
                                    <th class="px-4 py-2 border">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 whitespace-nowrap">
                                @foreach ($equipments as $eq)
                                    <tr class="border-b dark:border-gray-700">
                                        <td class="px-4 py-2">{{ $eq->serial_number }}</td>
                                        <td class="px-4 py-2">{{ $eq->store->name ?? '-' }}</td>
                                        <td class="px-4 py-2">
                                            <span
                                                class="inline-block px-2 py-1 text-xs font-semibold rounded-md
                                                    {{ match ($eq->status) {
                                                        'available' => 'bg-green-100 text-green-800',
                                                        'used' => 'bg-teal-100 text-teal-800',
                                                        'maintenance' => 'bg-red-100 text-red-800',
                                                        default => 'bg-gray-100 text-gray-600',
                                                    } }}">
                                                {{ ucfirst($eq->status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2">
                                            @if ($eq->transactions_id)
                                                <a href="{{ route('transactions.show', $eq->transactions_id) }}"
                                                    class="text-purple-600 hover:underline" target="_blank">
                                                    #{{ $eq->transactions_id }}
                                                </a>
                                            @else
                                                <span class="italic text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2">
                                            <x-buttons.action-button :href="route('equipments.show', $eq->id)" text="Details" target="_blank" />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="mt-4 text-sm text-gray-700 dark:text-gray-300">
                            Total Equipments: <strong>{{ $equipments->count() }}</strong>
                        </div>
                    </div>
                @endif
            </div>
        @elseif ($item->category === 'sparepart')
            {{-- Transaction History Placeholder untuk Spareparts --}}
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md mt-8">
                <h3 class="font-semibold text-lg mb-2 text-gray-800 dark:text-gray-100">Transaction History</h3>

                @if ($history->isEmpty())
                    <div class="text-sm text-gray-500 italic">No transaction history available.</div>
                @else
                    <div class="overflow-auto">
                        <table class="min-w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                                <tr>
                                    <th class="px-4 py-2">Date</th>
                                    <th class="px-4 py-2">Type</th>
                                    <th class="px-4 py-2">QTY</th>
                                    <th class="px-4 py-2">Note</th>
                                    <th class="px-4 py-2">Reference</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 whitespace-nowrap">
                                @foreach ($history as $entry)
                                    <tr class="border-b dark:border-gray-700">
                                        <td class="px-4 py-2">{{ $entry['date'] }}</td>
                                        <td class="px-4 py-2">
                                            <span
                                                class="px-2 py-1 rounded text-xs {{ $entry['type'] === 'in' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ ucfirst($entry['type']) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2">{{ $entry['qty'] }}</td>
                                        <td class="px-4 py-2">{{ $entry['note'] }}</td>
                                        <td class="px-4 py-2">
                                            @php
                                                $link = match ($entry['ref_type']) {
                                                    'transaction' => route('transactions.show', $entry['ref_id']),
                                                    'maintenance' => route('maintenances.show', $entry['ref_id']),
                                                    'incident' => route('incidents.show', $entry['ref_id']),
                                                    'request' => route('requests.show', $entry['ref_id']),
                                                    default => '#',
                                                };
                                            @endphp

                                            <a href="{{ $link }}" class="text-purple-600 hover:underline"
                                                target="_blank">
                                                {{ $entry['reference'] }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div
                            class="bg-white dark:bg-gray-800 p-4 rounded-lg mt-3 text-sm text-gray-700 dark:text-gray-300">
                            <h4 class="font-semibold text-base mb-2 text-gray-800 dark:text-gray-100">Summary</h4>
                            <div class="grid grid-cols-3 sm:grid-cols-3 gap-4">
                                <div>
                                    <div class="text-gray-500 dark:text-gray-400">Total In</div>
                                    <div class="font-semibold text-green-600 dark:text-green-400">{{ $totalIn }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-gray-500 dark:text-gray-400">Total Out</div>
                                    <div class="font-semibold text-red-600 dark:text-red-400">{{ $totalOut }}</div>
                                </div>
                                <div>
                                    <div class="text-gray-500 dark:text-gray-400">Total Stock</div>
                                    <div class="font-semibold text-indigo-600 dark:text-indigo-400">{{ $totalStock }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </x-dashboard.sidebar>
</x-app-layout>
