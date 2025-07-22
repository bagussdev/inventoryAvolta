<x-app-layout>
    <x-dashboard.sidebar>
        <div class="mt-4">
            <a href="{{ route('users.index') }}" onclick="showFullScreenLoader();"
                class="inline-flex items-center text-gray-700 hover:text-purple-600 dark:text-gray-300 dark:hover:text-white transition duration-150 ease-in-out">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 32 32" class="mr-2">
                    <path fill="#101820"
                        d="M30,29a1,1,0,0,1-.81-.41l-2.12-2.92A18.66,18.66,0,0,0,15,18.25V22a1,1,0,0,1-1.6.8l-12-9a1,1,0,0,1,0-1.6l12-9A1,1,0,0,1,15,4V8.24A19,19,0,0,1,31,27v1a1,1,0,0,1-.69.95A1.12,1.12,0,0,1,30,29ZM14,16.11h.1A20.68,20.68,0,0,1,28.69,24.5l.16.21a17,17,0,0,0-15-14.6,1,1,0,0,1-.89-1V6L3.67,13,13,20V17.11a1,1,0,0,1,.33-.74A1,1,0,0,1,14,16.11Z" />
                </svg>
                <span class="text-sm font-medium">Back</span>
            </a>
        </div>

        <div class="flex justify-center mt-6 px-4 sm:px-6 lg:px-8">
            <div class="w-full max-w-2xl bg-white dark:bg-gray-800 p-6 sm:p-8 rounded-lg shadow-md">
                <h1 class="text-2xl sm:text-3xl font-bold mb-6 text-center text-gray-900 dark:text-white">Create User
                </h1>

                <form method="POST" action="{{ route('users.store') }}" class="space-y-5"
                    onsubmit="return confirmAndLoad('Are you sure you want to create this user?')">
                    @csrf

                    {{-- Name --}}
                    <div>
                        <x-input-label for="name" :value="'Name'" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                            :value="old('name')" required />
                    </div>

                    {{-- Email --}}
                    <div>
                        <x-input-label for="email" :value="'Email'" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                            :value="old('email')" required />
                    </div>

                    {{-- Phone Number --}}
                    <div>
                        <x-input-label for="no_telfon" :value="'Phone Number'" />
                        <x-text-input id="no_telfon" name="no_telfon" type="number" class="mt-1 block w-full"
                            :value="old('no_telfon')" required maxlength="12"
                            oninput="if (this.value.length > 12) this.value = this.value.slice(0, 12)" />
                    </div>

                    {{-- Store Location --}}
                    <div>
                        <x-input-label for="store_location" :value="'Location'" />
                        <select id="store_location" name="store_location"
                            class="w-full mt-1 border-gray-300 dark:border-gray-700 rounded-md dark:bg-gray-700 dark:text-white"
                            required>
                            <option value="">Choose location</option>
                            @foreach ($stores as $store)
                                <option value="{{ $store->id }}"
                                    {{ old('store_location') == $store->id ? 'selected' : '' }}>
                                    {{ $store->name }} ({{ $store->site_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Role --}}
                    <div>
                        <x-input-label for="role_id" :value="'Role'" />
                        <select id="role_id" name="role_id" required
                            class="w-full mt-1 border-gray-300 dark:border-gray-700 rounded-md dark:bg-gray-700 dark:text-white">
                            <option value="">Choose role</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Department --}}
                    <div>
                        <x-input-label for="department_id" :value="'Department'" />
                        <select id="department_id" name="department_id"
                            class="w-full mt-1 border-gray-300 dark:border-gray-700 rounded-md dark:bg-gray-700 dark:text-white">
                            <option value="">Choose department</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}"
                                    {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Password --}}
                    <!-- <div>
                        <x-input-label for="password" :value="'Password'" />
                        <x-text-input id="password" name="password" type="password" class="mt-1 block w-full"
                            required />
                    </div>

                    <div>
                        <x-input-label for="password_confirmation" :value="'Confirm Password'" />
                        <x-text-input id="password_confirmation" name="password_confirmation" type="password"
                            class="mt-1 block w-full" required />
                    </div> -->

                    {{-- Submit --}}
                    <div class="flex justify-end">
                        <x-primary-button>Save</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </x-dashboard.sidebar>
</x-app-layout>
