<x-app-layout>
    <x-dashboard.sidebar>
        <x-alert-information></x-alert-information>
        <div class="flex justify-center px-4 sm:px-6 lg:px-8">
            <div class="w-full max-w-2xl bg-white dark:bg-gray-800 p-6 sm:p-8 rounded-lg shadow-md">
                {{-- Back Button --}}
                <div class="mb-4">
                    <a href="{{ route('items.index') }}" onclick="showFullScreenLoader();"
                        class="inline-flex items-center text-gray-700 hover:text-purple-600 dark:text-gray-300 dark:hover:text-white transition duration-150 ease-in-out">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 32 32"
                            class="mr-2">
                            <path fill="#101820"
                                d="M30,29a1,1,0,0,1-.81-.41l-2.12-2.92A18.66,18.66,0,0,0,15,18.25V22a1,1,0,0,1-1.6.8l-12-9a1,1,0,0,1,0-1.6l12-9A1,1,0,0,1,15,4V8.24A19,19,0,0,1,31,27v1a1,1,0,0,1-.69.95A1.12,1.12,0,0,1,30,29ZM14,16.11h.1A20.68,20.68,0,0,1,28.69,24.5l.16.21a17,17,0,0,0-15-14.6,1,1,0,0,1-.89-1V6L3.67,13,13,20V17.11a1,1,0,0,1,.33-.74A1,1,0,0,1,14,16.11Z" />
                        </svg>
                        <span class="text-sm font-medium">Back</span>
                    </a>
                </div>

                {{-- Heading --}}
                <h1 class="text-xl sm:text-3xl font-bold mb-6 text-center text-gray-900 dark:text-white">
                    Create New Item
                </h1>

                {{-- Form --}}
                <form action="{{ route('items.store') }}" method="POST" class="space-y-6"
                    onsubmit="showFullScreenLoader(); return confirm('Are you sure to create?')">
                    @csrf

                    {{-- Name --}}
                    <div>
                        <x-input-label for="name" :value="__('Name')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" required
                            autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    {{-- Type --}}
                    <div>
                        <x-input-label for="type" :value="__('Type')" />
                        <select id="type" name="type" required
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:ring focus:ring-purple-300 dark:bg-gray-700 dark:text-white">
                            <option value="" disabled selected>Choose type</option>
                            <option value="Unit">Unit</option>
                            <option value="Pcs">Pcs</option>
                            <option value="Box">Box</option>
                        </select>
                        <x-input-error :messages="$errors->get('type')" class="mt-2" />
                    </div>

                    {{-- Brand --}}
                    <div>
                        <x-input-label for="brand" :value="__('Brand')" />
                        <x-text-input id="brand" name="brand" type="text" class="mt-1 block w-full" />
                        <x-input-error :messages="$errors->get('brand')" class="mt-2" />
                    </div>

                    {{-- Model --}}
                    <div>
                        <x-input-label for="model" :value="__('Model')" />
                        <x-text-input id="model" name="model" type="text"
                            class="mt-1 block w-full uppercase-input" />
                        <x-input-error :messages="$errors->get('model')" class="mt-2" />
                    </div>

                    {{-- Category --}}
                    <div>
                        <x-input-label for="category" :value="__('Category')" />
                        <select id="category" name="category" required
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:ring focus:ring-purple-300 dark:bg-gray-700 dark:text-white">
                            <option value="" disabled selected>Choose category</option>
                            <option value="sparepart">Sparepart</option>
                            <option value="equipment">Equipment</option>
                        </select>
                        <x-input-error :messages="$errors->get('category')" class="mt-2" />
                    </div>
                    @can('isMaster')
                        <div class="mb-4">
                            <label for="department_id" class="block text-gray-700 dark:text-gray-300">Department</label>
                            <select name="department_id" id="department_id"
                                class="form-select w-full mt-1 rounded-md shadow-sm border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">Select a department</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}"
                                        {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @endcan

                    {{-- Submit --}}
                    <div class="text-center">
                        <x-primary-button class="px-6">
                            {{ __('Save Item') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </x-dashboard.sidebar>
    {{-- Auto-uppercase --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.uppercase-input').forEach(input => {
                input.addEventListener('input', function() {
                    this.value = this.value.toUpperCase();
                });
            });
        });
    </script>
</x-app-layout>
