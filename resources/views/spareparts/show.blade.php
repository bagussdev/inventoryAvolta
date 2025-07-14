<x-app-layout>
    <x-dashboard.sidebar>
        <x-alert-information></x-alert-information>

        <div class="mt-4">
            <a href="{{ route('spareparts.index') }}" onclick="showFullScreenLoader();"
                class="inline-flex items-center text-gray-700 hover:text-purple-600 dark:text-gray-300 dark:hover:text-white transition duration-150 ease-in-out">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 32 32" class="mr-2">
                    <path fill="#101820"
                        d="M30,29a1,1,0,0,1-.81-.41l-2.12-2.92A18.66,18.66,0,0,0,15,18.25V22a1,1,0,0,1-1.6.8l-12-9a1,1,0,0,1,0-1.6l12-9A1,1,0,0,1,15,4V8.24A19,19,0,0,1,31,27v1a1,1,0,0,1-.69.95A1.12,1.12,0,0,1,30,29ZM14,16.11h.1A20.68,20.68,0,0,1,28.69,24.5l.16.21a17,17,0,0,0-15-14.6,1,1,0,0,1-.89-1V6L3.67,13,13,20V17.11a1,1,0,0,1,.33-.74A1,1,0,0,1,14,16.11Z" />
                </svg>
                <span class="text-sm font-medium">Back</span>
            </a>
        </div>

        <div class="flex justify-between items-center mt-4 w-full max-w-full">
            <h2 class="font-bold text-xl sm:text-2xl">Sparepart Detail</h2>
        </div>

        <hr class="h-[3px] my-4 bg-gray-200 border-0 dark:bg-gray-700 w-full">

        @php
            $fields = [
                'Item Name' => $sparepart->item->name ?? '-',
                'Category' => $sparepart->item->category ?? '-',
                'Type' => $sparepart->item->type ?? '-',
                'Brand' => $sparepart->item->brand ?? '-',
                'Model' => $sparepart->item->model ?? '-',
                'Qty' => $sparepart->qty ?? '-',
                'Status' =>
                    $sparepart->qty > 5
                        ? '<span class="inline-block px-2 py-1 text-xs font-semibold rounded bg-green-100 text-green-800">Available</span>'
                        : ($sparepart->qty < 5
                            ? '<span class="inline-block px-2 py-1 text-xs font-semibold rounded bg-orange-100 text-orange-800">Low</span>'
                            : '<span class="inline-block px-2 py-1 text-xs font-semibold rounded bg-red-100 text-red-800">Empty</span>'),
            ];
        @endphp

        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md text-sm text-gray-700 dark:text-gray-300">
            {{-- MOBILE VERSION --}}
            <div class="block md:hidden space-y-4">
                <div class="grid grid-cols-1 gap-3">
                    @foreach ($fields as $label => $value)
                        <div class="flex">
                            <div class="w-40 font-medium">{{ $label }}</div>
                            <div class="flex-1">: {!! $value !!}</div>
                        </div>
                    @endforeach
                    <div class="flex gap-4">
                        <div class="w-40 font-medium">Photo</div>
                        <div class="flex-1" id="sparepart-photo-viewer">
                            @if ($sparepart->transaction && $sparepart->transaction->photoitems)
                                <ul class="list-none">
                                    <li>
                                        <img src="{{ asset('storage/' . $sparepart->transaction->photoitems) }}"
                                            alt="Photo" class="w-48 h-auto rounded-md shadow cursor-pointer"
                                            style="max-height: 200px;" />
                                    </li>
                                </ul>
                            @else
                                <span class="italic text-gray-400">No photo available</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- DESKTOP VERSION --}}
            <div class="hidden md:grid grid-cols-1 md:grid-cols-4 gap-6">
                @foreach ($fields as $label => $value)
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">{{ $label }}</label>
                        <div
                            class="px-3 py-2 {{ $label === 'Status' ? '' : 'bg-gray-100 dark:bg-gray-700 rounded-md' }}">
                            {!! $value !!}</div>
                    </div>
                @endforeach
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Photo</label>
                    <div class="px-3 py-2" id="sparepart-photo-viewer">
                        @if ($sparepart->transaction && $sparepart->transaction->photoitems)
                            <img src="{{ asset('storage/' . $sparepart->transaction->photoitems) }}" alt="Photo"
                                class="w-48 h-auto rounded-md shadow cursor-pointer" style="max-height: 200px;" />
                        @else
                            <span class="italic text-gray-400">No photo available</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

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
                        <tbody class="bg-white dark:bg-gray-800">
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
                    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg mt-3 text-sm text-gray-700 dark:text-gray-300">
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

        @push('scripts')
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    const imageContainer = document.getElementById('sparepart-photo-viewer');
                    if (imageContainer) {
                        new Viewer(imageContainer, {
                            inline: false,
                            toolbar: true,
                            movable: true,
                            zoomable: true,
                            scalable: true,
                            transition: true,
                        });
                    }
                });
            </script>
        @endpush

    </x-dashboard.sidebar>
</x-app-layout>
