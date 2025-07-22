<x-app-layout>
    <x-dashboard.sidebar>
        <x-alert-information></x-alert-information>
        <div class="mt-4">
            <a href="{{ route('transactions.index') }}" onclick="showFullScreenLoader();"
                class="inline-flex items-center text-gray-700 hover:text-purple-600 dark:text-gray-300 dark:hover:text-white transition duration-150 ease-in-out">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 32 32" class="mr-2">
                    <path fill="#101820"
                        d="M30,29a1,1,0,0,1-.81-.41l-2.12-2.92A18.66,18.66,0,0,0,15,18.25V22a1,1,0,0,1-1.6.8l-12-9a1,1,0,0,1,0-1.6l12-9A1,1,0,0,1,15,4V8.24A19,19,0,0,1,31,27v1a1,1,0,0,1-.69.95A1.12,1.12,0,0,1,30,29ZM14,16.11h.1A20.68,20.68,0,0,1,28.69,24.5l.16.21a17,17,0,0,0-15-14.6,1,1,0,0,1-.89-1V6L3.67,13,13,20V17.11a1,1,0,0,1,.33-.74A1,1,0,0,1,14,16.11Z" />
                </svg>
                <span class="text-sm font-medium">Back</span>
            </a>
        </div>
        <div class="flex justify-between items-center mt-4 w-full max-w-full">
            <h2 class="font-bold text-xl sm:text-2xl">
                Transaction Detail
            </h2>
        </div>

        <hr class="h-[3px] my-4 bg-gray-200 border-0 dark:bg-gray-700 w-full">

        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md text-sm text-gray-700 dark:text-gray-300">
            {{-- MOBILE VERSION --}}
            <div class="block md:hidden space-y-4">
                @php
                    $fields = [
                        'Transaction ID' => $transaction->id,
                        'Category' => ucfirst($transaction->item->category ?? '-'),
                        'Item' => $transaction->item->name ?? '-',
                        'Model' => $transaction->item->model ?? '-',
                        'Brand' => $transaction->item->brand ?? '-',
                        'Qty' => $transaction->qty,
                        'Supplier' => $transaction->supplier,
                        'Created By' => $transaction->user->name,
                    ];
                    if ($transaction->type === 'equipment') {
                        $fields = array_merge(
                            array_slice($fields, 0, 5),
                            ['Serial Number' => $transaction->serial_number],
                            array_slice($fields, 5),
                        );
                    }
                @endphp
                <div class="grid grid-cols-1 gap-3">
                    @foreach ($fields as $label => $value)
                        <div class="flex">
                            <div class="w-40 font-medium">{{ $label }}</div>
                            <div class="flex-1">: {!! nl2br(e($value)) !!}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- DESKTOP VERSION --}}
            <div class="hidden md:grid grid-cols-1 md:grid-cols-4 gap-6">
                <div><span class="font-medium">Transaction ID :</span>
                    <div class="bg-gray-100 dark:bg-gray-700 rounded px-3 py-2 mt-1">{{ $transaction->id }}</div>
                </div>
                <div><span class="font-medium">Category :</span>
                    <div class="bg-gray-100 dark:bg-gray-700 rounded px-3 py-2 mt-1">
                        {{ ucfirst($transaction->item->category ?? '-') }}</div>
                </div>
                <div><span class="font-medium">Item :</span>
                    <div class="bg-gray-100 dark:bg-gray-700 rounded px-3 py-2 mt-1">
                        {{ $transaction->item->name ?? '-' }}</div>
                </div>
                <div><span class="font-medium">Model :</span>
                    <div class="bg-gray-100 dark:bg-gray-700 rounded px-3 py-2 mt-1">
                        {{ $transaction->item->model ?? '-' }}</div>
                </div>
                <div><span class="font-medium">Brand :</span>
                    <div class="bg-gray-100 dark:bg-gray-700 rounded px-3 py-2 mt-1">
                        {{ $transaction->item->brand ?? '-' }}</div>
                </div>
                @if ($transaction->type === 'equipment')
                    <div><span class="font-medium">Serial Number :</span>
                        <div class="bg-gray-100 dark:bg-gray-700 rounded px-3 py-2 mt-1">
                            {{ $transaction->serial_number }}</div>
                    </div>
                @endif
                <div><span class="font-medium">Qty :</span>
                    <div class="bg-gray-100 dark:bg-gray-700 rounded px-3 py-2 mt-1">{{ $transaction->qty }}</div>
                </div>
                <div><span class="font-medium">Supplier :</span>
                    <div class="bg-gray-100 dark:bg-gray-700 rounded px-3 py-2 mt-1">{{ $transaction->supplier }}</div>
                </div>
                <div><span class="font-medium">Created By :</span>
                    <div class="bg-gray-100 dark:bg-gray-700 rounded px-3 py-2 mt-1">{{ $transaction->user->name }}
                    </div>
                </div>
            </div>
            <div class="mt-6">
                <p class="font-semibold">Note :</p>
                <p class="italic text-gray-600 dark:text-gray-300">
                    {{ $transaction->notes ?: '-' }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <div id="transaction-photo-viewer">
                    <div class="font-medium mb-2">Photo :</div>
                    @if ($transaction->photoitems)
                        <ul class="list-none">
                            <li>
                                <img src="{{ asset('storage/' . $transaction->photoitems) }}" alt="Photo"
                                    class="w-full max-w-xs h-auto rounded-md shadow cursor-pointer mx-auto">
                            </li>
                        </ul>
                    @else
                        <span class="italic text-gray-400">No photo available</span>
                    @endif
                </div>
                <div>
                    <div class="font-medium mb-2">Invoice/Letter :</div>
                    @if ($transaction->attachmentfile)
                        <iframe src="{{ asset('storage/' . $transaction->attachmentfile) }}"
                            class="w-full h-52 rounded" frameborder="0"></iframe>
                        <a href="{{ asset('storage/' . $transaction->attachmentfile) }}" target="_blank"
                            class="block mt-1 text-sm text-blue-600 underline text-center">Buka di tab baru</a>
                    @else
                        <span class="italic text-gray-400">No attachment available</span>
                    @endif
                </div>
            </div>
        </div>

        @push('scripts')
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    const imageContainer = document.getElementById('transaction-photo-viewer');
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
