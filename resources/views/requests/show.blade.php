<x-app-layout>
    <x-dashboard.sidebar>
        <x-alert-information></x-alert-information>

        {{-- Back button --}}
        @if ($request->status === 'completed')
            <div class="mt-4">
                <a href="{{ route('requests.completed') }}" onclick="showFullScreenLoader();"
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
                <a href="{{ route('requests.index') }}" onclick="showFullScreenLoader();"
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
                Request Details
            </h2>
        </div>

        <hr class="h-[3px] my-4 bg-gray-200 border-0 dark:bg-gray-700 w-full">

        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md text-sm text-gray-700 dark:text-gray-300">
            {{-- Versi MOBILE (grid-col-1, gaya flex + titik dua) --}}
            {{-- Versi MOBILE diperbarui --}}
            <div class="block md:hidden">
                <div class="grid grid-cols-1 gap-4">
                    @php
                        $mobileFields = [
                            'ID request' => $request->unique_id,
                            'Item' => $request->item_request ?? '-',
                            'Location' => $request->store->name ?? '-',
                            'User Report' => $request->user->name ?? '-',
                            'Department To' => $request->department->name ?? '-',
                            'PIC Staff' => $request->picUser->name ?? '-',
                            'Completed At' => \Carbon\Carbon::parse($request->resolved_at)->format('d M Y H:i') ?? '-',
                        ];
                    @endphp

                    @foreach ($mobileFields as $label => $value)
                        <div class="flex items-start gap-2">
                            <div class="w-40 font-medium shrink-0">{{ $label }}</div>
                            <div class="flex-1 break-all">: {{ $value }}</div>
                        </div>
                    @endforeach

                    {{-- Status dengan badge --}}
                    <div class="flex items-start gap-2">
                        <div class="w-40 font-medium shrink-0">Status</div>
                        <div class="flex-1">
                            : <span
                                class="inline-block px-3 py-1 text-xs font-medium rounded-md {{ match (strtolower($request->status)) {
                                    'completed' => 'bg-green-100 text-green-800',
                                    'in progress' => 'bg-blue-100 text-blue-800',
                                    'pending' => 'bg-red-100 text-red-800',
                                    'waiting' => 'bg-yellow-100 text-yellow-600',
                                    default => 'bg-gray-100 text-gray-600',
                                } }}">
                                {{ ucfirst($request->status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>


            {{-- Versi DESKTOP (grid-col-4, model label + box) --}}
            <div class="hidden md:block">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">ID request
                            :</label>
                        <div class="bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded-md">{{ $request->unique_id }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Item :</label>
                        <div class="bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded-md">
                            {{ $request->item_request ?? '-' }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Location
                            :</label>
                        <div class="bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded-md">
                            {{ $request->store->name ?? '-' }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">User Report
                            :</label>
                        <div class="bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded-md">
                            {{ $request->user->name ?? '-' }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Department To
                            :</label>
                        <div class="bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded-md">
                            {{ $request->department->name ?? '-' }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">PIC Staff
                            :</label>
                        <div class="bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded-md">
                            {{ $request->picUser->name ?? '-' }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Report At
                            :</label>
                        <div class="bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded-md">
                            {{ \Carbon\Carbon::parse($request->created_at)->format('d M Y H:i') ?? '-' }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Completed At
                            :</label>
                        <div class="bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded-md">
                            {{ $request->resolved_at ? \Carbon\Carbon::parse($request->resolved_at)->format('d M Y H:i') : '-' }}
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Status :</label>
                        <span
                            class="inline-block px-3 py-1 text-xs font-semibold rounded-md {{ match (strtolower($request->status)) {
                                'completed' => 'bg-green-100 text-green-800',
                                'in progress' => 'bg-blue-100 text-blue-800',
                                'pending' => 'bg-red-100 text-red-800',
                                'waiting' => 'bg-yellow-100 text-yellow-600',
                                default => 'bg-gray-100 text-gray-600',
                            } }}">
                            {{ ucfirst($request->status) }}
                        </span>
                    </div>
                </div>
            </div>


            {{-- Message --}}
            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Left Column: Message User and Message Staff --}}
                <div>
                    <p class="font-semibold">Message User :</p>
                    <p class="italic text-gray-600 dark:text-gray-300">
                        {{ $request->message_user ?? 'No message provided.' }}</p>

                    {{-- Message Staff now directly below Message User --}}
                    <div class="mt-6"> {{-- Add margin-top for separation between the two messages --}}
                        <p class="font-semibold">Message Staff :</p>
                        <p class="italic text-gray-600 dark:text-gray-300">
                            {{ $request->message_staff ?? 'No message provided.' }}</p>
                    </div>
                    {{-- Used Spareparts (now below Attachment in the right column) --}}
                    @if (in_array(strtolower($request->status), ['resolved', 'completed']))
                        <div class="mt-6"> {{-- Add margin-top for separation from Attachment --}}
                            <h4 class="text-md font-semibold mb-2">Used Spareparts</h4>
                            @if ($request->usedSpareParts && $request->usedSpareParts->isNotEmpty())
                                <ul class="list-disc pl-6">
                                    @foreach ($request->usedSpareParts as $used)
                                        <li>{{ $used->sparepart->item->name ?? '-' }} — Qty:
                                            {{ $used->qty }}
                                            @if ($used->note)
                                                <span class="text-gray-500 italic">({{ $used->note }})</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-gray-500 italic">No spareparts used for this maintenance.</p>
                            @endif
                            @can('request.update')
                                <div class="mt-4">
                                    <button onclick="openSparepartModal()"
                                        class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md inline-block">
                                        ⚙️ Manage Sparepart
                                    </button>
                                </div>
                            @endcan
                        </div>
                    @endif
                </div>


                {{-- Right Column: Attachment and Used Spareparts --}}
                <div>
                    {{-- Attachment --}}
                    <div>
                        <h4 class="text-md font-semibold mb-2">Attachment</h4>
                        @if ($request->attachment_user && Storage::disk('public')->exists($request->attachment_user))
                            @php
                                $ext = pathinfo($request->attachment_user, PATHINFO_EXTENSION);
                                $url = asset('storage/' . $request->attachment_user);
                            @endphp
                            @if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif']))
                                <img id="viewer-image-wrapper" src="{{ $url }}" alt="Attachment"
                                    class="max-h-40 w-full object-contain rounded shadow-md border border-gray-300 dark:border-gray-600" />
                            @elseif(in_array(strtolower($ext), ['mp4', 'mov', 'avi']))
                                <video src="{{ $url }}" controls
                                    class="rounded shadow w-full max-h-96 mt-2"></video>
                            @else
                                <a href="{{ $url }}" target="_blank" class="text-blue-600 underline"
                                    onclick="showFullScreenLoader();">See Attachment</a>
                            @endif
                        @else
                            <p class="text-gray-500 italic">No attachment uploaded.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <form id="sparepartsForm" action="{{ route('requests.updateSpareparts', $request->id) }}" method="POST"
            onsubmit="showFullScreenLoader();">
            @csrf
            @method('PUT')
            <div id="sparepartsContainer" class="hidden"></div>
            <div id="deleted_ids" class="hidden"></div>
        </form>

        @include('requests.modal-spareparts')
        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const wrapper = document.getElementById('viewer-image-wrapper');
                    if (wrapper) {
                        new Viewer(wrapper, {
                            toolbar: true,
                            title: false,
                            tooltip: true,
                            movable: true,
                            zoomable: true,
                            scalable: false,
                            fullscreen: true,
                            transition: true,
                        });
                    }
                });
            </script>
        @endpush

    </x-dashboard.sidebar>
</x-app-layout>
