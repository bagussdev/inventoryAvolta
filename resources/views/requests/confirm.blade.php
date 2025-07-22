<x-app-layout>
    <x-dashboard.sidebar>
        <x-alert-information></x-alert-information>

        {{-- Section: Back Button --}}
        <div class="mt-4">
            <a href="{{ route('requests.index') }}" onclick="showFullScreenLoader();"
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
                Confirm Request
            </h2>
        </div>

        <hr class="h-[3px] my-4 bg-gray-200 border-0 dark:bg-gray-700 w-full">

        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md text-sm text-gray-700 dark:text-gray-300">

            <h3 class="font-bold text-lg mb-4 text-gray-800 dark:text-gray-200">Request Details</h3>

            {{-- Mobile Version --}}
            <div class="block md:hidden">
                <div class="grid grid-cols-1 gap-4">
                    @php
                        $mobileFields = [
                            'ID Request' => $request->unique_id,
                            'Item Request' => $request->item_request ?? '-',
                            'Location' => $request->store->name ?? '-',
                            'User Report' => $request->user->name ?? '-',
                            'Department To' => $request->department->name ?? '-',
                            'PIC Staff' => $request->picUser->name ?? '-',
                            'Report At' => $request->created_at ?? '-',
                            'Completed At' => $request->resolved_at ?? '-',
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
                                    'resolved' => 'bg-green-100 text-green-800',
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

            {{-- Desktop Version --}}
            <div class="hidden md:block">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">ID request
                            :</label>
                        <div class="bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded-md">{{ $request->unique_id }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Item Request
                            :</label>
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
                            {{ $request->created_at ?? '-' }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Completed At
                            :</label>
                        <div class="bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded-md">
                            {{ $request->resolved_at ?? '-' }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Status :</label>
                        <span
                            class="inline-block px-3 py-1 text-xs font-semibold rounded-md {{ match (strtolower($request->status)) {
                                'resolved' => 'bg-green-100 text-green-800',
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

            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Notes and Attachment Display --}}
                <div class="">
                    <p class="font-semibold">Message User :</p>
                    <p class="italic text-gray-600 dark:text-gray-300">
                        {{ $request->message_user ?? 'No message provided.' }}</p>
                </div>
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


            <h3 class="font-bold text-lg mb-4 text-gray-800 dark:text-gray-200 mt-6">Confirm Request</h3>

            {{-- Form for Confirming request --}}
            <form action="{{ route('requests.submitConfirm', $request->id) }}" method="POST"
                enctype="multipart/form-data"
                onsubmit="return confirmAndLoad('Are you sure you want to confirm this request?')">
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
                    <x-input-error :messages="$errors->get('attachment')" class="mt-2" />
                </div>
                <div id="attachment-preview" class="mt-4 mb-1 flex justify-start items-left">
                    <img id="preview-image"
                        class="rounded shadow max-h-40 w-auto object-contain hidden border border-gray-300 dark:border-gray-600" />
                    <video id="preview-video"
                        class="rounded shadow max-h-48 w-full max-w-md hidden mt-2 border border-gray-300 dark:border-gray-600"
                        controls></video>
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
                        class="bg-purple-600 text-white px-3 py-1.5 text-sm sm:px-3 sm:py-2 sm:text-sm rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                        + Add Spareparts Used
                    </button>
                    <div id="sparepartsContainer" class="hidden"></div>
                    <button type="submit"
                        class="bg-green-600 text-white px-3 py-1.5 text-sm sm:px-3 sm:py-2 sm:text-sm rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                        Confirm
                    </button>
                    {{-- @can('request.pending') --}}
                    <button type="button" onclick="openPendingModal()"
                        class="bg-red-600 text-white px-3 py-1.5 text-sm sm:px-3 sm:py-2 sm:text-sm rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                        Mark as Pending
                    </button>
                    {{-- @endcan --}}
                </div>
            </form>
            {{-- End Confirmation Form --}}

        </div>
        @include('requests.modal-confirm')
        @include('requests.modal-pending')

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
