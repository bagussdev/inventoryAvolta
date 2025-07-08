<x-app-layout>
    <x-dashboard.sidebar>
        <x-alert-information></x-alert-information>

        @if ($errors->any())
            <div class="mb-4 text-sm text-red-600 dark:text-red-400">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="max-w-3xl mx-auto bg-white dark:bg-gray-800 p-6 sm:p-8 rounded-lg shadow-md">

            <div class="mb-4">
                <a href="{{ route('transactions.index') }}" onclick="showFullScreenLoader();"
                    class="inline-flex items-center text-gray-700 hover:text-purple-600 dark:text-gray-300 dark:hover:text-white transition duration-150 ease-in-out">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 32 32"
                        class="mr-2">
                        <path fill="#101820"
                            d="M30,29a1,1,0,0,1-.81-.41l-2.12-2.92A18.66,18.66,0,0,0,15,18.25V22a1,1,0,0,1-1.6.8l-12-9a1,1,0,0,1,0-1.6l12-9A1,1,0,0,1,15,4V8.24A19,19,0,0,1,31,27v1a1,1,0,0,1-.69.95A1.12,1.12,0,0,1,30,29ZM14,16.11h.1A20.68,20.68,0,0,1,28.69,24.5l.16.21a17,17,0,0,0-15-14.6,1,1,0,0,1-.89-1V6L3.67,13,13,20V17.11a1,1,0,0,1,.33-.74A1,1,0,0,1,14,16.11Z" />
                    </svg>
                    <span class="text-sm font-medium">Back</span>
                </a>
            </div>

            <h1 class="text-xl sm:text-3xl font-bold mb-6 text-center text-gray-900 dark:text-white">
                Edit Transaction
            </h1>

            <form action="{{ route('transactions.update', $transaction->id) }}" method="POST"
                onsubmit="return confirmAndLoad('Are you sure to updated?')" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- Category Selector --}}
                <div class="mb-4">
                    <x-input-label for="category" :value="__('Category')" class="block text-[11px] sm:text-sm" />
                    <input type="text" readonly disabled value="{{ ucfirst($transaction->item->category) }}"
                        class="mt-1 block w-full text-[11px] sm:text-sm bg-gray-100 dark:bg-gray-700 rounded" />
                </div>

                {{-- Item --}}
                <div class="mb-4">
                    <x-input-label for="items_id" :value="__('Item')" class="block text-[11px] sm:text-sm" />
                    <input type="text" readonly disabled
                        value="{{ $transaction->item->name }} - {{ $transaction->item->model }} - {{ $transaction->item->category }}"
                        class="mt-1 block w-full text-[11px] sm:text-sm bg-gray-100 dark:bg-gray-700 rounded" />
                </div>

                {{-- serial_number + QTY + Supplier --}}
                <div class="mb-4">
                    <div class="grid grid-cols-3 gap-2">
                        @if ($transaction->type === 'equipment')
                            <div>
                                <x-input-label for="serial_number" :value="__('S/N')" />
                                <input name="serial_number" id="serial_number" value="{{ $transaction->serial_number }}"
                                    class="uppercase-input mt-1 block w-full text-[11px] sm:text-sm px-2 py-1 sm:px-3 sm:py-2 rounded border-gray-300 dark:bg-gray-700 dark:text-white"
                                    required />
                            </div>
                        @endif
                        <div>
                            <x-input-label for="qty" :value="__('QTY')" />
                            <input type="number" name="qty" id="qty" value="{{ $transaction->qty }}"
                                class="mt-1 block w-full text-[11px] sm:text-sm px-2 py-1 sm:px-3 sm:py-2 rounded border-gray-300 dark:bg-gray-700 dark:text-white"
                                required />
                        </div>
                        <div>
                            <x-input-label for="supplier" :value="__('Supplier')" />
                            <input name="supplier" id="supplier" value="{{ $transaction->supplier }}"
                                class="mt-1 block w-full text-[11px] sm:text-sm px-2 py-1 sm:px-3 sm:py-2 rounded border-gray-300 dark:bg-gray-700 dark:text-white"
                                required />
                        </div>
                    </div>
                </div>

                {{-- File Input --}}
                <div class="mb-4">
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <x-input-label for="photoitems" :value="__('Photo (replace if needed)')" />
                            <input type="file" name="photoitems"
                                class="mt-1 block w-full text-[11px] sm:text-sm px-2 py-1 sm:px-3 sm:py-2 rounded border-gray-300 dark:bg-gray-700 dark:text-white" />
                        </div>
                        <div>
                            <x-input-label for="attachmentfile" :value="__('Invoice/Letter (replace if needed)')" />
                            <input type="file" name="attachmentfile"
                                class="mt-1 block w-full text-[11px] sm:text-sm px-2 py-1 sm:px-3 sm:py-2 rounded border-gray-300 dark:bg-gray-700 dark:text-white" />
                        </div>
                    </div>
                </div>

                {{-- Preview Area --}}
                <div class="grid grid-cols-2 gap-2">
                    <div class="mt-2" id="photoitems-wrapper">
                        <img src="{{ asset('storage/' . $transaction->photoitems) }}"
                            data-original="{{ asset('storage/' . $transaction->photoitems) }}"
                            class="max-h-40 rounded cursor-zoom-in">
                    </div>
                    <div class="mt-2">
                        <iframe src="{{ asset('storage/' . $transaction->attachmentfile) }}"
                            class="w-full h-40 rounded" frameborder="0"></iframe>
                        <a href="{{ asset('storage/' . $transaction->attachmentfile) }}" target="_blank"
                            class="block mt-1 text-sm text-blue-600 underline">Buka di tab baru</a>
                    </div>
                </div>

                {{-- Notes --}}
                <div class="mb-4">
                    <x-input-label for="notes" :value="__('Note')" />
                    <textarea name="notes" rows="4" class="block w-full mt-1 rounded-md">{{ $transaction->notes }}</textarea>
                </div>

                {{-- Submit --}}
                <div class="flex justify-center mt-4">
                    <x-primary-button>
                        {{ __('Update Transaction') }}
                    </x-primary-button>
                </div>
            </form>
        </div>

        {{-- Scripts --}}
        @push('scripts')
            <script src="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.10.5/viewer.min.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const wrapper = document.getElementById('photoitems-wrapper');
                    if (wrapper) {
                        new Viewer(wrapper, {
                            toolbar: true,
                            tooltip: true,
                            navbar: false,
                            title: true,
                        });
                    }
                });
            </script>
        @endpush
    </x-dashboard.sidebar>
</x-app-layout>
