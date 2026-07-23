<x-app-layout>
    <x-dashboard.sidebar>
        <x-alert-information></x-alert-information>
        <div class="flex justify-center px-4 sm:px-6 lg:px-8">
            <div class="w-full max-w-4xl bg-white dark:bg-gray-800 p-6 md:p-8 rounded-lg shadow-md">
                {{-- Back Button --}}
                <div class="mb-4">
                    <a href="{{ route('desktops.index') }}" onclick="showFullScreenLoader();"
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
                <h1 class="text-2xl font-bold mb-7 text-center text-gray-900 dark:text-white">
                    Create New Desktop
                </h1>

                {{-- Form --}}
                <form action="{{ route('desktops.store') }}" method="POST" class="space-y-6"
                    onsubmit="showFullScreenLoader(); return confirm('Are you sure to create this desktop?')">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        {{-- Hostname (auto) --}}
                        <div>
                            <x-input-label for="hostname" :value="__('Hostname (Auto)')" />
                            <x-text-input id="hostname" name="hostname" type="text" value="{{ $nextHostname }}"
                                class="mt-1 block w-full bg-gray-100 dark:bg-gray-700 cursor-not-allowed text-gray-500"
                                readonly />
                            <p class="text-xs text-gray-500 mt-1">Hostname ini otomatis ter-generate.</p>
                        </div>

                        {{-- Serial Number (required) --}}
                        <div>
                            <x-input-label for="serialnumber" :value="__('Serial Number *')" />
                            <x-text-input id="serialnumber" name="serialnumber" type="text" class="mt-1 block w-full"
                                required />
                            <x-input-error :messages="$errors->get('serialnumber')" class="mt-2" />
                        </div>

                        {{-- Model (required) --}}
                        <div>
                            <x-input-label for="model" :value="__('Model *')" />
                            <x-text-input id="model" name="model" type="text" class="mt-1 block w-full"
                                required />
                            <x-input-error :messages="$errors->get('model')" class="mt-2" />
                        </div>

                        {{-- Brand (required) --}}
                        <div>
                            <x-input-label for="brand" :value="__('Brand *')" />
                            <x-text-input id="brand" name="brand" type="text" class="mt-1 block w-full"
                                required />
                            <x-input-error :messages="$errors->get('brand')" class="mt-2" />
                        </div>

                        {{-- User (optional) --}}
                        <div>
                            <x-input-label for="user" :value="__('User')" />
                            <x-text-input id="user" name="user" type="text" class="mt-1 block w-full"
                                placeholder="Nama lengkap user (opsional)" />
                            <x-input-error :messages="$errors->get('user')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="location" :value="__('Location *')" />
                            <select id="location" name="location" required
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 rounded-md shadow-sm
                                focus:ring focus:ring-purple-300 dark:bg-gray-700 dark:text-white">
                                <option value="" disabled selected>Pilih lokasi</option>
                                @foreach ($stores as $store)
                                    <option value="{{ $store->id }}"
                                        {{ old('location') == $store->id ? 'selected' : '' }}>
                                        {{ $store->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('location')" class="mt-2" />
                        </div>

                        {{-- Type Windows (optional) --}}
                        <div>
                            <x-input-label for="typewindows" :value="__('Windows Version')" />
                            <x-text-input id="typewindows" name="typewindows" type="text"
                                placeholder="Misal: Windows 11 Pro" class="mt-1 block w-full" />
                            <x-input-error :messages="$errors->get('typewindows')" class="mt-2" />
                        </div>

                        {{-- OS Status (optional) --}}
                        <div>
                            <x-input-label for="osstatus" :value="__('OS Status')" />
                            <x-text-input id="osstatus" name="osstatus" type="text"
                                placeholder="Support Update / Not Supported" class="mt-1 block w-full" />
                            <x-input-error :messages="$errors->get('osstatus')" class="mt-2" />
                        </div>

                        {{-- IP RealVNC (optional) --}}
                        <div>
                            <x-input-label for="iprealvnc" :value="__('IP')" />
                            <x-text-input id="iprealvnc" name="iprealvnc" type="text" class="mt-1 block w-full"
                                placeholder="192.168.1.10" />
                            <x-input-error :messages="$errors->get('iprealvnc')" class="mt-2" />
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="text-center">
                        <x-primary-button class="px-6">
                            {{ __('Save Desktop') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </x-dashboard.sidebar>
</x-app-layout>
