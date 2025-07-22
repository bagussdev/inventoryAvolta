<x-app-layout>
    <x-dashboard.sidebar>
        <x-alert-information></x-alert-information>

        {{-- Section: Back Button --}}
        <div class="mt-4">
            <a href="{{ route('maintenances.index') }}" onclick="showFullScreenLoader();"
                class="inline-flex items-center text-gray-700 hover:text-purple-600 dark:text-gray-300 dark:hover:text-white transition duration-150 ease-in-out">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 32 32" class="mr-2">
                    <path fill="#101820"
                        d="M30,29a1,1,0,0,1-.81-.41l-2.12-2.92A18.66,18.66,0,0,0,15,18.25V22a1,1,0,0,1-1.6.8l-12-9a1,1,0,0,1,0-1.6l12-9A1,1,0,0,1,15,4V8.24A19,19,0,0,1,31,27v1a1,1,0,0,1-.69.95A1.12,1.12,0,0,1,30,29ZM14,16.11h.1A20.68,20.68,0,0,1,28.69,24.5l.16.21a17,17,0,0,0-15-14.6,1,1,0,0,1-.89-1V6L3.67,13,13,20V17.11a1,1,0,0,1,.33-.74A1,1,0,0,1,14,16.11Z" />
                </svg>
                <span class="text-sm font-medium">Back</span>
            </a>
        </div>

        {{-- Page Title --}}
        <div class="flex justify-between items-center mt-4 w-full max-w-full">
            <h2 class="font-bold text-xl sm:text-2xl">
                Confirm Maintenance
            </h2>
        </div>

        <hr class="h-[3px] my-4 bg-gray-200 border-0 dark:bg-gray-700 w-full">

        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md text-sm text-gray-700 dark:text-gray-300">

            <h3 class="font-bold text-lg mb-4 text-gray-800 dark:text-gray-200">Maintenance Details</h3>

            {{-- Mobile Version: Flex Row --}}
            <div class="block md:hidden">
                <div class="grid grid-cols-1 gap-4">
                    @php
                        $fields = [
                            ['label' => 'ID', 'value' => $maintenance->id],
                            ['label' => 'Store', 'value' => $maintenance->equipment->store->name ?? '-'],
                            [
                                'label' => 'Item',
                                'value' => ucwords(
                                    strtolower(
                                        optional(optional($maintenance->equipment)->item)->name .
                                            ' - ' .
                                            ($maintenance->equipment->alias ?? ($maintenance->item_description ?? '-')),
                                    ),
                                ),
                            ],
                            [
                                'label' => 'Scheduled',
                                'value' => \Carbon\Carbon::parse($maintenance->maintenance_date)->format('d M Y'),
                            ],
                            ['label' => 'Model', 'value' => $maintenance->equipment->item->model ?? '-'],
                            [
                                'label' => 'PIC Staff',
                                'value' => $maintenance->staff->name ?? '-',
                            ],
                            ['label' => 'Brand', 'value' => $maintenance->equipment->item->brand ?? '-'],
                            [
                                'label' => 'Resolved At',
                                'value' => $maintenance->resolved_at
                                    ? \Carbon\Carbon::parse($maintenance->resolved_at)->format('d M Y')
                                    : '-',
                            ],
                            ['label' => 'Confirm By', 'value' => $maintenance->confirm->name ?? 'N/A'],
                        ];
                    @endphp

                    @foreach ($fields as $field)
                        <div class="flex items-center">
                            <div class="w-40 font-medium">{{ $field['label'] }}</div>
                            <div class="flex-1">: {{ $field['value'] }}</div>
                        </div>
                    @endforeach

                    <div class="flex items-center">
                        <div class="w-40 font-medium">Status</div>
                        <div class="flex-1">:
                            <span
                                class="inline-block px-3 py-1 text-xs font-semibold rounded-md {{ match (strtolower($maintenance->status)) {
                                    'completed' => 'bg-green-100 text-green-800',
                                    'maintenance' => 'bg-red-100 text-red-600',
                                    'in progress' => 'bg-blue-100 text-blue-800',
                                    'resolved' => 'bg-purple-100 text-purple-800',
                                    'not due' => 'bg-gray-100 text-gray-600', // Ensure 'not due' is handled if applicable
                                    default => 'bg-yellow-100 text-yellow-800',
                                } }}">
                                {{ ucfirst($maintenance->status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Desktop Version: Label + Gray Box --}}
            <div class="hidden md:block">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">ID</label>
                        <div class="bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded-md">MNT-000{{ $maintenance->id }}
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Store</label>
                        <div class="bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded-md">
                            {{ $maintenance->equipment->store->name ?? '-' }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Item</label>
                        <div class="bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded-md">
                            {{ ucwords(
                                strtolower(
                                    optional(optional($maintenance->equipment)->item)->name .
                                        (!empty($maintenance->equipment->alias) || !empty($maintenance->item_description)
                                            ? ' - ' . ($maintenance->equipment->alias ?? $maintenance->item_description)
                                            : ''),
                                ),
                            ) }}
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">
                            Scheduled</label>
                        <div class="bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded-md">
                            {{ \Carbon\Carbon::parse($maintenance->maintenance_date)->format('d M Y') }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Model</label>
                        <div class="bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded-md">
                            {{ $maintenance->equipment->item->model ?? '-' }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">PIC Staff</label>
                        <div class="bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded-md">
                            {{ $maintenance->staff->name ?? '-' }}
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Brand</label>
                        <div class="bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded-md">
                            {{ $maintenance->equipment->item->brand ?? '-' }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Resolved
                            At</label>
                        <div class="bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded-md">
                            {{ $maintenance->resolved_at ? \Carbon\Carbon::parse($maintenance->resolved_at)->format('d M Y') : '-' }}
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Confirm
                            By</label>
                        <div class="bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded-md">
                            {{ $maintenance->confirm->name ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Status</label>
                        <span
                            class="inline-block px-3 py-1 text-xs font-semibold rounded-md {{ match (strtolower($maintenance->status)) {
                                'completed' => 'bg-green-100 text-green-800',
                                'maintenance' => 'bg-red-100 text-red-600',
                                'in progress' => 'bg-blue-100 text-blue-800',
                                'resolved' => 'bg-purple-100 text-purple-800',
                                'not due' => 'bg-gray-100 text-gray-600',
                                default => 'bg-yellow-100 text-yellow-800',
                            } }}">
                            {{ ucfirst($maintenance->status) }}
                        </span>
                    </div>
                </div>
            </div>


            <h3 class="font-bold text-lg mb-4 text-gray-800 dark:text-gray-200 mt-6">Confirmation</h3>

            {{-- Form for Confirming Maintenance --}}
            <form action="{{ route('maintenances.submitConfirm', $maintenance->id) }}" method="POST"
                enctype="multipart/form-data"
                onsubmit="return confirmAndLoad('Are you sure you want to confirm this maintenance?')">
                @csrf

                <div class="mb-4 mt-4">
                    <label for="attachment"
                        class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Attachment
                        (photo/video)</label>
                    <input type="file" name="attachment" id="attachment" accept="image/*,video/*"
                        class="block w-full text-sm text-gray-700 dark:text-gray-300 file:mr-4 file:py-2 file:px-4
                        file:rounded-md file:border-0 file:text-sm file:font-semibold
                        file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100
                        dark:file:bg-gray-700 dark:file:text-gray-200 dark:hover:file:bg-gray-600
                        border border-gray-300 rounded-md shadow-sm"
                        required>
                </div>
                <div id="attachment-preview" class="mt-4 mb-1">
                    <img id="preview-image" class="max-h-48 hidden" />
                    <video id="preview-video" class="max-h-48 hidden" controls></video>
                </div>

                <div class="mb-6">
                    <label for="notes"
                        class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Notes</label>
                    <textarea name="notes" id="notes" rows="3" required
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm resize-y
                        focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                </div>

                <div class="flex flex-wrap justify-start gap-2 mb-6">
                    <button type="button" onclick="openSparepartModal()"
                        class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                        + Add Spareparts Used
                    </button>
                    <div id="sparepartsContainer" class="hidden"></div>
                    <button type="submit"
                        class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                        Confirm
                    </button>
                </div>

            </form>
            {{-- End Confirmation Form --}}

        </div> {{-- End of main content box --}}

        {{-- Include the modal and its scripts --}}
        @include('maintenances.modal-confirm')

    </x-dashboard.sidebar>
</x-app-layout>
