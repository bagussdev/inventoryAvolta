<x-app-layout>
    <x-dashboard.sidebar>
        <x-alert-information></x-alert-information>

        <div
            class="max-w-4xl mx-auto bg-white dark:bg-gray-800 p-6 sm:p-8 rounded-lg shadow-md text-sm text-gray-700 dark:text-gray-300">
            {{-- Back Button --}}
            <div class="mb-4">
                <a href="{{ route('requests.index') }}" onclick="showFullScreenLoader();"
                    class="inline-flex items-center text-gray-700 hover:text-purple-600 dark:text-gray-300 dark:hover:text-white transition duration-150 ease-in-out">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 32 32" class="mr-2">
                        <path fill="#101820"
                            d="M30,29a1,1,0,0,1-.81-.41l-2.12-2.92A18.66,18.66,0,0,0,15,18.25V22a1,1,0,0,1-1.6.8l-12-9a1,1,0,0,1,0-1.6l12-9A1,1,0,0,1,15,4V8.24A19,19,0,0,1,31,27v1a1,1,0,0,1-.69.95A1.12,1.12,0,0,1,30,29ZM14,16.11h.1A20.68,20.68,0,0,1,28.69,24.5l.16.21a17,17,0,0,0-15-14.6,1,1,0,0,1-.89-1V6L3.67,13,13,20V17.11a1,1,0,0,1,.33-.74A1,1,0,0,1,14,16.11Z" />
                    </svg>
                    <span class="text-sm font-medium">Back</span>
                </a>
            </div>

            {{-- Title --}}
            <div class="mb-6">
                <h2 class="text-xl sm:text-2xl font-bold text-center text-gray-900 dark:text-white">Edit Request</h2>
            </div>

            {{-- Form --}}
            <form method="POST" action="{{ route('requests.update', $request->id) }}" enctype="multipart/form-data"
                class="space-y-6" onsubmit="return confirmAndLoad('Are you sure to update this request?')">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- LEFT SIDE --}}
                    <div class="space-y-4">
                        {{-- Store --}}
                        <div>
                            <label class="block font-medium mb-1">Store</label>
                            <input type="text" readonly value="{{ $request->store->name ?? '-' }}"
                                class="w-full rounded-md border-gray-300 bg-gray-100 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                        </div>

                        {{-- Department --}}
                        <div>
                            <label class="block font-medium mb-1">Department To</label>
                            <input type="text" readonly value="{{ $request->department->name ?? '-' }}"
                                class="w-full rounded-md border-gray-300 bg-gray-100 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                        </div>

                        {{-- Item Request --}}
                        <div>
                            <label class="block font-medium mb-1">Item Request</label>
                            <input type="text" name="item_request"
                                value="{{ old('item_request', $request->item_request) }}" required
                                class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2" />
                        </div>

                        {{-- Quantity --}}
                        <div>
                            <label class="block font-medium mb-1">Quantity</label>
                            <input type="number" name="qty" value="{{ old('qty', $request->qty) }}" min="1"
                                required
                                class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2" />
                        </div>

                        <label class="block font-medium mt-4 mb-1">Upload New Attachment (optional)</label>
                        <input type="file" name="attachment_user" id="attachmentPreviewInput"
                            accept="image/*,video/*"
                            class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white p-2" />
                        <div id="attachmentPreviewContainer" class="mt-4 hidden">
                            <div id="viewerWrapper">
                                <img id="imagePreview" class="max-h-64 rounded hidden object-contain w-full" />
                            </div>
                            <video id="videoPreview" class="w-full max-h-64 rounded hidden mt-4" controls></video>
                        </div>
                    </div>

                    {{-- RIGHT SIDE --}}
                    <div>
                        <label class="block font-medium mb-1">Current Attachment</label>
                        @if ($request->attachment_user && Storage::disk('public')->exists($request->attachment_user))
                            @php $ext = pathinfo($request->attachment_user, PATHINFO_EXTENSION); @endphp
                            @if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif']))
                                <img src="{{ asset('storage/' . $request->attachment_user) }}"
                                    class="rounded max-h-48 w-full object-contain" />
                            @elseif(in_array(strtolower($ext), ['mp4', 'mov', 'avi']))
                                <video src="{{ asset('storage/' . $request->attachment_user) }}"
                                    class="rounded w-full max-h-48 mt-2" controls></video>
                            @endif
                        @else
                            <p class="text-gray-500 italic">No attachment available.</p>
                        @endif
                    </div>
                </div>

                {{-- Message --}}
                <div>
                    <label class="block font-medium mb-1">Message</label>
                    <textarea name="message_user" rows="4" required
                        class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 resize-y"
                        placeholder="input your message here...">{{ old('message_user', $request->message_user) }}</textarea>
                </div>

                {{-- Submit --}}
                <div class="text-center">
                    <x-primary-button class="px-6">
                        Update Request
                    </x-primary-button>
                </div>
            </form>
        </div>
        @push('scripts')
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    const input = document.getElementById('attachmentPreviewInput');
                    const imagePreview = document.getElementById('imagePreview');
                    const videoPreview = document.getElementById('videoPreview');
                    const container = document.getElementById('attachmentPreviewContainer');
                    const wrapper = document.getElementById('viewerWrapper');

                    let viewer;

                    input.addEventListener('change', function(e) {
                        const file = e.target.files[0];
                        if (!file) return;

                        const url = URL.createObjectURL(file);
                        const fileType = file.type;

                        container.classList.remove('hidden');
                        imagePreview.classList.add('hidden');
                        videoPreview.classList.add('hidden');

                        if (fileType.startsWith('image/')) {
                            imagePreview.src = url;
                            imagePreview.classList.remove('hidden');

                            if (viewer) viewer.destroy(); // destroy old if any
                            viewer = new Viewer(wrapper, {
                                toolbar: true,
                                navbar: false,
                                title: false,
                            });
                        } else if (fileType.startsWith('video/')) {
                            videoPreview.src = url;
                            videoPreview.classList.remove('hidden');

                            if (viewer) {
                                viewer.destroy();
                                viewer = null;
                            }
                        } else {
                            alert("Unsupported file type");
                        }
                    });
                });
            </script>
        @endpush
    </x-dashboard.sidebar>
</x-app-layout>
