<x-guest-layout>
    <div class="flex flex-col items-center justify-center mt-7">
        <h1 class="font-bold text-2xl">Create an Account</h1>
        <p class="text-slate-400">Create a account to continue</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Nama -->
        <div>
            <x-input-label for="name" :value="__('Name')" class="mb-3" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required
                autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2 mb-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" class="mb-3" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')"
                required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2 mb-2" />
        </div>

        <!-- No Telp -->
        <div class="mt-4">
            <x-input-label for="no_telfon" :value="__('No Telfon')" class="mb-3" />
            <x-text-input id="no_telfon" class="block mt-1 w-full" type="number" name="no_telfon" :value="old('no_telfon')"
                required autofocus autocomplete="no_telfon" />
            <x-input-error :messages="$errors->get('no_telfon')" class="mt-2 mb-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" class="mb-3" />

            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required
                autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2 mb-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="mb-3" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password"
                name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>
        <div class="flex flex-col items-center justify-center mt-5 mb-3">
            <button type="submit"
                class="text-white bg-[#A463F3] hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 w-3/4">
                {{ __('Sign up') }}
            </button>
            <p class="text-sm mt-2">Already have an account? <a href="{{ route('login') }}"
                    class="text-[#5A8CFF] underline">Login</a></p>
        </div>
    </form>
</x-guest-layout>
