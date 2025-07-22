<x-app-layout>
    <x-dashboard.sidebar>
        <x-alert-information></x-alert-information>

        <div
            class="max-w-4xl mx-auto bg-white dark:bg-gray-800 p-6 sm:p-8 rounded-lg shadow-md text-sm text-gray-700 dark:text-gray-300">
            {{-- Back Button --}}
            <div class="mb-4">
                <a href="{{ route('incidents.index') }}" onclick="showFullScreenLoader();"
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
                <h2 class="text-xl sm:text-2xl font-bold text-center text-gray-900 dark:text-white">Incident Form</h2>
            </div>

            {{-- Form Start --}}
            <form method="POST" action="{{ route('incidents.store') }}" enctype="multipart/form-data" class="space-y-6"
                onsubmit="return confirmAndLoad('Are you sure to create?')">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- LEFT SIDE --}}
                    <div class="space-y-4">
                        {{-- Store --}}
                        <div>
                            <label class="block font-medium mb-1">Store</label>
                            @if (in_array(Auth::user()->role->name, ['Staff', 'SPV', 'Master']))
                                <select name="store_id" id="store_id" required onchange="toggleItems()"
                                    class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <option value="">-- Choose Store --</option>
                                    @foreach ($stores as $store)
                                        @if ($store->type == 'Store')
                                            <option value="{{ $store->id }}">{{ $store->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            @else
                                <input type="text" readonly value="{{ Auth::user()->location->name ?? '-' }}"
                                    class="w-full rounded-md border-gray-300 bg-gray-100 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                                <input type="hidden" name="store_id" id="store_id"
                                    value="{{ Auth::user()->store_location }}" onload="toggleItems()">
                            @endif
                        </div>

                        {{-- Department --}}
                        <div>
                            <label class="block font-medium mb-1">Department To</label>
                            <select name="department_to" id="department_to" required onchange="toggleItems()"
                                class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">-- Select Department --</option>
                                <option value="{{ $deptItId }}">IT</option>
                                <option value="{{ $deptMepId }}">MEP</option>
                            </select>
                        </div>

                        {{-- Item Problem --}}
                        <div id="item_problem_container" class="hidden">
                            <label class="block font-medium mb-1">Item Problem</label>
                            <select name="item_problem" id="item_problem" required
                                class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></select>
                        </div>
                        <div id="item_description_container" class="hidden">
                            <label class="block font-medium mb-1 mt-2">Item Description</label>
                            <input type="text" name="item_description" id="item_description"
                                class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="Describe the item here..." />
                        </div>
                    </div>

                    {{-- RIGHT SIDE --}}
                    <div>
                        <label class="block font-medium mb-1">Attachment</label>
                        <input type="file" name="attachment_user" id="attachmentPreviewInput"
                            accept="image/*,video/*" required
                            class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white p-2" />
                        <x-input-error :messages="$errors->get('attachment_user')" class="mt-2" />

                        <div id="attachmentPreviewContainer" class="mt-4 hidden">
                            <div id="viewerWrapper">
                                <img id="imagePreview" class="max-h-64 rounded hidden object-contain w-full" />
                            </div>
                            <video id="videoPreview" class="w-full max-h-64 rounded hidden mt-4" controls></video>
                        </div>

                    </div>
                </div>

                {{-- Notes --}}
                <div>
                    <label class="block font-medium mb-1">Note</label>
                    <textarea name="message_user" rows="4" required
                        class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 resize-y"
                        placeholder="Describe your problem here..."></textarea>
                </div>

                {{-- Submit --}}
                <div class="text-center">
                    <x-primary-button class="px-6">
                        Send
                    </x-primary-button>
                </div>
            </form>
        </div>

        @push('scripts')
            <script>
                let itemSelect;

                async function toggleItems() {
                    const storeId = document.getElementById('store_id')?.value;
                    const departmentId = document.getElementById('department_to')?.value;
                    const container = document.getElementById('item_problem_container');

                    if (!storeId || !departmentId) {
                        container.classList.add('hidden');
                        return;
                    }

                    showFullScreenLoader();

                    try {
                        const response = await fetch(`/ajax/items-by-store/${storeId}/${departmentId}`);
                        const items = await response.json();

                        if (!Array.isArray(items)) throw new Error('Invalid response format');

                        if (itemSelect) itemSelect.destroy();

                        itemSelect = new TomSelect('#item_problem', {
                            placeholder: 'Select item...',
                            options: [{
                                    value: 'others',
                                    text: 'Other',
                                    optgroup: 'Top'
                                },
                                ...items.map(item => ({
                                    value: item.id,
                                    text: `${item.name} - ${item.alias}`,
                                    optgroup: 'Items'
                                }))
                            ],
                            optgroups: [{
                                    value: 'Top',
                                    label: ''
                                }, // no label for top
                                {
                                    value: 'Items',
                                    label: 'Item List'
                                }
                            ],
                            render: {
                                optgroup_header: function(data, escape) {
                                    if (data.value === 'Top') return '';
                                    return `<div class="text-xs text-gray-400 px-2 pt-1">${escape(data.label)}</div>`;
                                }
                            },
                            dropdownParent: 'body',
                            create: false
                        });

                        container.classList.remove('hidden');

                        // Re-attach the change event to show/hide description input
                        itemSelect.on('change', function(value) {
                            const descContainer = document.getElementById('item_description_container');
                            if (value === 'others') {
                                descContainer.classList.remove('hidden');
                            } else {
                                descContainer.classList.add('hidden');
                            }
                        });

                    } catch (err) {
                        console.error('Error fetching items:', err);
                        alert('Failed to load items.');
                    } finally {
                        hideFullScreenLoader();
                    }
                }

                // Attachment preview handler
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
                            if (viewer) viewer.destroy();
                            viewer = new Viewer(wrapper, {
                                toolbar: true,
                                navbar: false,
                                title: false
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

                // Incident status check
                document.addEventListener("DOMContentLoaded", function() {
                    document.addEventListener('change', async function(e) {
                        if (e.target && e.target.id === 'item_problem' && e.target.value !== 'others') {
                            const equipmentId = e.target.value;
                            try {
                                const response = await fetch(`/ajax/check-incident-status/${equipmentId}`);
                                const data = await response.json();
                                if (data.active) {
                                    alert('Warning: This item is already reported and is still being handled.');
                                }
                            } catch (error) {
                                console.error('Error checking incident status:', error);
                            }
                        }
                    });
                });
            </script>
        @endpush
    </x-dashboard.sidebar>
</x-app-layout>
