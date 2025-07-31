<x-app-layout>
    <x-dashboard.sidebar>
        <x-alert-information></x-alert-information>

        {{-- Back button --}}
        @if ($maintenance->status === 'completed')
            <div class="mt-4">
                <a href="{{ route('maintenances.completed') }}" onclick="showFullScreenLoader();"
                    class="inline-flex items-center text-gray-700 hover:text-purple-600 dark:text-gray-300 dark:hover:text-white transition duration-150 ease-in-out">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 32 32" class="mr-2">
                        <path fill="#101820"
                            d="M30,29a1,1,0,0,1-.81-.41l-2.12-2.92A18.66,18.66,0,0,0,15,18.25V22a1,1,0,0,1-1.6.8l-12-9a1,1,0,0,1,0-1.6l12-9A1,1,0,0,1,15,4V8.24A19,19,0,0,1,31,27v1a1,1,0,0,1-.69.95A1.12,1.12,0,0,1,30,29ZM14,16.11h.1A20.68,20.68,0,0,1,28.69,24.5l.16.21a17,17,0,0,0-15-14.6,1,1,0,0,1-.89-1V6L3.67,13,13,20V17.11a1,1,0,0,1,.33-.74A1,1,0,0,1,14,16.11Z" />
                    </svg>
                    <span class="text-sm font-medium">Back</span>
                </a>
            </div>
        @else
            <div class="mt-4">
                <a href="{{ route('maintenances.index') }}" onclick="showFullScreenLoader();"
                    class="inline-flex items-center text-gray-700 hover:text-purple-600 dark:text-gray-300 dark:hover:text-white transition duration-150 ease-in-out">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 32 32"
                        class="mr-2">
                        <path fill="#101820"
                            d="M30,29a1,1,0,0,1-.81-.41l-2.12-2.92A18.66,18.66,0,0,0,15,18.25V22a1,1,0,0,1-1.6.8l-12-9a1,1,0,0,1,0-1.6l12-9A1,1,0,0,1,15,4V8.24A19,19,0,0,1,31,27v1a1,1,0,0,1-.69.95A1.12,1.12,0,0,1,30,29ZM14,16.11h.1A20.68,20.68,0,0,1,28.69,24.5l.16.21a17,17,0,0,0-15-14.6,1,1,0,0,1-.89-1V6L3.67,13,13,20V17.11a1,1,0,0,1,.33-.74A1,1,0,0,1,14,16.11Z" />
                    </svg>
                    <span class="text-sm font-medium">Back</span>
                </a>
            </div>
        @endif


        {{-- Title --}}
        <div class="flex justify-between items-center mt-4 w-full max-w-full">
            <h2 class="font-bold text-xl sm:text-2xl">
                Maintenance Details
            </h2>
        </div>

        <hr class="h-[3px] my-4 bg-gray-200 border-0 dark:bg-gray-700 w-full">

        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md text-sm text-gray-700 dark:text-gray-300">

            {{-- Versi Mobile: Flex Row --}}
            <div class="block md:hidden">
                <div class="grid grid-cols-1 gap-4">
                    @php
                        $fields = [
                            ['label' => 'ID', 'value' => $maintenance->id],
                            ['label' => 'Store', 'value' => $maintenance->equipment->store->name ?? '-'],
                            ['label' => 'Item', 'value' => $maintenance->equipment->item->name ?? '-'],
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
                                'value' => \Carbon\Carbon::parse($maintenance->resolved_at)->format('d M Y'),
                            ],
                            ['label' => 'Confirm By', 'value' => $maintenance->confirm->name ?? '-'],
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
                                    default => 'bg-gray-100 text-gray-600',
                                } }}">
                                {{ ucfirst($maintenance->status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Versi Desktop: Label + Gray Box --}}
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
                            {{ $maintenance->resolved_at ? \Carbon\Carbon::parse($maintenance->resolved_at)->format('d M Y H:i') : '-' }}
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Confirm
                            By</label>
                        <div class="bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded-md">
                            {{ $maintenance->confirm->name ?? '-' }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Status</label>
                        <span
                            class="inline-block px-3 py-1 text-xs font-semibold rounded-md {{ match (strtolower($maintenance->status)) {
                                'completed' => 'bg-green-100 text-green-800',
                                'maintenance' => 'bg-red-100 text-red-600',
                                'in progress' => 'bg-blue-100 text-blue-800',
                                'resolved' => 'bg-purple-100 text-purple-800',
                                default => 'bg-gray-100 text-gray-600',
                            } }}">
                            {{ ucfirst($maintenance->status) }}
                        </span>
                    </div>
                </div>
            </div>


            {{-- Notes dan Attachment --}}
            <div class="mt-6">
                <p class="font-semibold">Notes:</p>
                <p class="italic text-gray-600 dark:text-gray-300">{{ $maintenance->notes ?? 'No notes provided.' }}
                </p>
            </div>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Attachment --}}
                <div>
                    <h4 class="text-md font-semibold mb-2">Attachment</h4>

                    @if ($maintenance->attachment)
                        <div id="attachment-viewer" class="max-w-full">
                            @php
                                $ext = pathinfo($maintenance->attachment, PATHINFO_EXTENSION);
                                $url = asset('storage/' . $maintenance->attachment);
                            @endphp

                            @if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif']))
                                <img src="{{ $url }}" alt="Attachment"
                                    class="cursor-zoom-in max-h-60 rounded shadow w-full object-contain" />
                            @elseif(in_array(strtolower($ext), ['mp4', 'mov', 'avi']))
                                <video src="{{ $url }}" controls
                                    class="rounded shadow w-full max-h-96 mt-2"></video>
                            @else
                                <a href="{{ $url }}" target="_blank" class="text-blue-600 underline"
                                    onclick="showFullScreenLoader();">View
                                    Attachment</a>
                            @endif
                        </div>
                    @else
                        <p class="text-gray-500 italic">No attachment uploaded.</p>
                    @endif
                </div>

                {{-- Used Spareparts --}}
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <h3 class="font-bold text-md text-gray-800 dark:text-gray-200">Used Spareparts</h3>
                    </div>

                    <div class="text-sm">
                        @if ($maintenance->usedSpareParts->isEmpty())
                            <p id="no-spareparts-message" class="text-gray-500 italic">No spareparts used for this
                                maintenance.</p>
                        @else
                            <ul id="used-spareparts-list" class="list-disc ml-6">
                                @foreach ($maintenance->usedSpareParts as $used)
                                    <li>
                                        {{ $used->sparepart->item->name ?? 'Unknown' }} — Qty: {{ $used->qty }}
                                        @if ($used->note)
                                            <span class="text-gray-500 italic">({{ $used->note }})</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    {{-- Tombol Edit dan Add Spareparts --}}
                    @can('maintenance.edit')
                        <div class="mt-4 text-left flex gap-2">
                            @if (in_array(strtolower($maintenance->status), ['resolved', 'completed']))
                                <button type="button" onclick="openSparepartModal()"
                                    class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                                    ⚙️ Manage Spareparts
                                </button>
                            @endif
                        </div>
                    @endcan
                </div>
            </div>

            {{-- Form untuk submit sparepart, diisi oleh JS --}}
            <form id="sparepartsForm" action="{{ route('maintenances.updateSpareparts', $maintenance->id) }}"
                method="POST" onsubmit="showFullScreenLoader();">
                @csrf
                @method('PUT')
                <div id="sparepartsContainer" class="hidden"></div>
                <div id="deleted_ids" class="hidden"></div>
            </form>

        </div>

        {{-- === INCLUDE MODAL PARTIAL FOR MAINTENANCE === --}}
        @include('maintenances.modal-spareparts')

        @push('scripts')
            {{-- Script for Viewer.js --}}
            <script>
                function showFullScreenLoader() {
                    document.getElementById('loading-overlay').classList.remove('hidden');
                }

                function hideFullScreenLoader() {
                    document.getElementById('loading-overlay').classList.add('hidden');
                }
                document.addEventListener("DOMContentLoaded", function() {
                    const viewerContainer = document.getElementById('attachment-viewer');
                    if (viewerContainer) {
                        new Viewer(viewerContainer.querySelector('img'), {
                            toolbar: true,
                            title: false,
                            navbar: false,
                        });
                    }
                });
            </script>
        @endpush
    </x-dashboard.sidebar>
</x-app-layout>
