<x-app-layout>
    <x-dashboard.sidebar>
        <div class="flex justify-center px-4 sm:px-6 lg:px-8">
            <div class="w-full max-w-2xl bg-white dark:bg-gray-800 p-6 sm:p-8 rounded-lg shadow-md">
                <div class="mb-4">
                    <a href="{{ route('outlets.index') }}" onclick="showFullScreenLoader();"
                        class="inline-flex items-center text-gray-700 hover:text-purple-600 dark:text-gray-300 dark:hover:text-white transition duration-150 ease-in-out">
                        {{-- SVG Icon --}}
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 32 32"
                            class="mr-2">
                            <path fill="#101820"
                                d="M30,29a1,1,0,0,1-.81-.41l-2.12-2.92A18.66,18.66,0,0,0,15,18.25V22a1,1,0,0,1-1.6.8l-12-9a1,1,0,0,1,0-1.6l12-9A1,1,0,0,1,15,4V8.24A19,19,0,0,1,31,27v1a1,1,0,0,1-.69.95A1.12,1.12,0,0,1,30,29ZM14,16.11h.1A20.68,20.68,0,0,1,28.69,24.5l.16.21a17,17,0,0,0-15-14.6,1,1,0,0,1-.89-1V6L3.67,13,13,20V17.11a1,1,0,0,1,.33-.74A1,1,0,0,1,14,16.11Z" />
                        </svg>
                        <span class="text-sm font-medium">Back</span>
                    </a>
                </div>

                <h1 class="text-2xl sm:text-3xl font-bold mb-6 text-center text-gray-900 dark:text-white">
                    Edit Outlet
                </h1>

                <form action="{{ route('outlets.update', $store->id) }}" method="POST" class="space-y-6"
                    onsubmit="showFullScreenLoader(); return confirm('Are you sure to updated?')">
                    @csrf
                    @method('PUT')

                    {{-- Name --}}
                    <div>
                        <x-input-label for="name" :value="__('Name')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                            value="{{ old('name', $store->name) }}" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    {{-- Site Code --}}
                    <div>
                        <x-input-label for="site_code" :value="__('Site Code')" />
                        <x-text-input id="site_code" name="site_code" type="text" class="mt-1 block w-full"
                            value="{{ old('site_code', $store->site_code) }}" required />
                        <x-input-error :messages="$errors->get('site_code')" class="mt-2" />
                    </div>

                    {{-- Since --}}
                    <div>
                        <x-input-label for="since" :value="__('Since (Tanggal Berdiri)')" />
                        <x-text-input id="since" name="since" type="date" class="mt-1 block w-full"
                            value="{{ old('since', $store->since->format('Y-m-d')) }}" required />
                        <x-input-error :messages="$errors->get('since')" class="mt-2" />
                    </div>

                    {{-- Location --}}
                    <div>
                        <x-input-label for="location" :value="__('Location')" />
                        <x-text-input id="location" name="location" type="text" class="mt-1 block w-full"
                            value="{{ old('location', $store->location) }}" required />
                        <x-input-error :messages="$errors->get('location')" class="mt-2" />
                    </div>

                    {{-- Submit --}}
                    <div class="flex justify-end">
                        <x-primary-button class="px-6">
                            {{ __('Update Outlet') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </x-dashboard.sidebar>
</x-app-layout>
