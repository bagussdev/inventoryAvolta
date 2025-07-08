<x-app-layout>
    <x-dashboard.sidebar>
        <x-alert-information></x-alert-information>

        <div class="max-w-3xl mx-auto bg-white dark:bg-gray-800 p-6 sm:p-8 rounded-lg shadow-md">
            <div class="mb-4">
                <a href="{{ route('transactions.index') }}" onclick="showFullScreenLoader();"
                    class="inline-flex items-center text-gray-700 hover:text-purple-600 dark:text-gray-300 dark:hover:text-white transition duration-150 ease-in-out">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 32 32" class="mr-2">
                        <path fill="#101820"
                            d="M30,29a1,1,0,0,1-.81-.41l-2.12-2.92A18.66,18.66,0,0,0,15,18.25V22a1,1,0,0,1-1.6.8l-12-9a1,1,0,0,1,0-1.6l12-9A1,1,0,0,1,15,4V8.24A19,19,0,0,1,31,27v1a1,1,0,0,1-.69.95A1.12,1.12,0,0,1,30,29ZM14,16.11h.1A20.68,20.68,0,0,1,28.69,24.5l.16.21a17,17,0,0,0-15-14.6,1,1,0,0,1-.89-1V6L3.67,13,13,20V17.11a1,1,0,0,1,.33-.74A1,1,0,0,1,14,16.11Z" />
                    </svg>
                    <span class="text-sm font-medium">Back</span>
                </a>
            </div>

            <h1 class="text-xl sm:text-3xl font-bold mb-6 text-center text-gray-900 dark:text-white">
                Transaction Form
            </h1>

            <form action="{{ route('transactions.store') }}" method="POST" enctype="multipart/form-data"
                onsubmit="return confirmAndLoad('Are you sure to create?')" x-data="transactionForm"
                x-init="init">
                @csrf

                {{-- Category Selector --}}
                <div class="mb-4">
                    <x-input-label for="category" :value="__('Category')" class="block text-[11px] sm:text-sm" />
                    <select name="category" x-model="category" id="category" @change="updateItems"
                        class="mt-1 block w-full text-[11px] sm:text-sm">
                        <option value="" disabled selected>Choose category</option>
                        <option value="equipment">Equipment</option>
                        <option value="sparepart">Sparepart</option>
                    </select>
                </div>

                {{-- Item Selector --}}
                <div class="mb-4" x-show="category">
                    <x-input-label for="items_id" :value="__('Items')" class="block text-[11px] sm:text-sm" />
                    <select x-ref="itemSelect" name="items_id" id="items_id"
                        class="mt-1 block w-full text-[11px] sm:text-sm">
                    </select>
                </div>

                {{-- serial_number + QTY + Supplier --}}
                <div class="mb-4" x-show="category">
                    <div class="grid grid-cols-3 gap-2">
                        <div x-show="category === 'equipment'">
                            <label for="serial_number"
                                class="block text-[11px] sm:text-sm font-medium text-gray-700 dark:text-white">S/N</label>
                            <input name="serial_number" id="serial_number"
                                class="uppercase-input mt-1 block w-full text-[11px] sm:text-sm px-2 py-1 sm:px-3 sm:py-2 rounded border-gray-300 dark:bg-gray-700 dark:text-white" />
                        </div>
                        <div>
                            <label for="qty"
                                class="block text-[11px] sm:text-sm font-medium text-gray-700 dark:text-white">QTY</label>
                            <input type="number" name="qty" id="qty"
                                class="mt-1 block w-full text-[11px] sm:text-sm px-2 py-1 sm:px-3 sm:py-2 rounded border-gray-300 dark:bg-gray-700 dark:text-white"
                                required />
                        </div>
                        <div>
                            <label for="supplier"
                                class="block text-[11px] sm:text-sm font-medium text-gray-700 dark:text-white">Supplier</label>
                            <input name="supplier" id="supplier"
                                class="mt-1 block w-full text-[11px] sm:text-sm px-2 py-1 sm:px-3 sm:py-2 rounded border-gray-300 dark:bg-gray-700 dark:text-white"
                                required />
                        </div>
                    </div>
                </div>

                {{-- Photo + Attachment --}}
                <div class="mb-4" x-show="category">
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label for="photoitems"
                                class="block text-[11px] sm:text-sm font-medium text-gray-700 dark:text-white">Photo</label>
                            <input type="file" name="photoitems"
                                class="mt-1 block w-full text-[11px] sm:text-sm px-2 py-1 sm:px-3 sm:py-2 rounded border-gray-300 dark:bg-gray-700 dark:text-white"
                                required />
                        </div>
                        <div>
                            <label for="attachmentfile"
                                class="block text-[11px] sm:text-sm font-medium text-gray-700 dark:text-white">Invoice/Letter</label>
                            <input type="file" name="attachmentfile"
                                class="mt-1 block w-full text-[11px] sm:text-sm px-2 py-1 sm:px-3 sm:py-2 rounded border-gray-300 dark:bg-gray-700 dark:text-white"
                                required />
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    {{-- Photo Preview --}}
                    <div class="mt-2">
                        <img id="photoitems-preview" src="#" alt="Preview" class="max-h-40 rounded hidden">
                    </div>
                    {{-- Attachment Preview --}}
                    <div class="mt-2">
                        <iframe id="attachmentfile-preview" class="w-full h-40 rounded hidden" frameborder="0"></iframe>
                    </div>
                </div>

                <div class="mb-4" x-show="category">
                    <x-input-label for="notes" :value="__('Note')" />
                    <textarea name="notes" rows="4" class="block w-full mt-1 rounded-md"></textarea>
                </div>

                <input type="hidden" name="type" :value="category">

                <div class="flex justify-center mt-4" x-show="category">
                    <x-primary-button>
                        {{ __('Save Transactions') }}
                    </x-primary-button>
                </div>
            </form>

            @push('scripts')
                <script>
                    document.addEventListener('alpine:init', () => {
                        Alpine.data('transactionForm', () => ({
                            category: '',
                            items: @json($items),
                            itemSelectInstance: null,
                            init() {
                                this.initTomSelect();
                            },
                            initTomSelect() {
                                this.itemSelectInstance = new TomSelect(this.$refs.itemSelect, {
                                    placeholder: 'Select item...',
                                    options: [],
                                    valueField: 'id',
                                    labelField: 'text',
                                    searchField: ['text']
                                });
                            },
                            updateItems() {
                                const filtered = this.items
                                    .filter(item => item.category === this.category)
                                    .map(item => ({
                                        id: item.id,
                                        text: `${item.name} - ${item.model} - ${item.category}`
                                    }));
                                this.itemSelectInstance.clearOptions();
                                this.itemSelectInstance.addOptions(filtered);
                                this.itemSelectInstance.refreshOptions(false);
                            }
                        }))
                    });
                </script>
            @endpush
        </div>
        {{-- Auto-uppercase --}}
        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    document.querySelectorAll('.uppercase-input').forEach(input => {
                        input.addEventListener('input', function() {
                            this.value = this.value.toUpperCase();
                        });
                    });
                });
            </script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Preview untuk Foto
                    const photoInput = document.querySelector('input[name="photoitems"]');
                    const photoPreview = document.getElementById('photoitems-preview');
                    if (photoInput) {
                        photoInput.addEventListener('change', function(e) {
                            const file = e.target.files[0];
                            if (file) {
                                const reader = new FileReader();
                                reader.onload = function(e) {
                                    photoPreview.src = e.target.result;
                                    photoPreview.classList.remove('hidden');
                                };
                                reader.readAsDataURL(file);
                            }
                        });
                    }

                    // Preview untuk Attachment (PDF atau Gambar)
                    const attachInput = document.querySelector('input[name="attachmentfile"]');
                    const attachPreview = document.getElementById('attachmentfile-preview');
                    if (attachInput) {
                        attachInput.addEventListener('change', function(e) {
                            const file = e.target.files[0];
                            if (file) {
                                const url = URL.createObjectURL(file);
                                attachPreview.src = url;
                                attachPreview.classList.remove('hidden');
                            }
                        });
                    }
                });
            </script>
        @endpush

    </x-dashboard.sidebar>
</x-app-layout>
