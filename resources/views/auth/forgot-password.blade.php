<x-guest-layout>
    {{-- <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </div> --}}
    <div class="mb-6">
        <a href="{{ route('login') }}" onclick="showFullScreenLoader();"
            class="inline-flex items-center text-sm text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-white">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="w-5 h-5 mr-2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
            </svg>
        </a>
    </div>
    <div class="flex flex-col items-center justify-center">
        <h1 class="font-bold text-2xl mt-7 mb-2">Forgot Password</h1>
        <p class="text-slate-400 mb-7">Enter your registered email if you forget your password</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')"
                required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        {{-- <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Forget Password') }}
            </x-primary-button>
        </div> --}}
        <div class="flex flex-col items-center justify-center mt-5 mb-3">
            <button type="submit"
                class="text-white bg-[#A463F3] hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 w-3/4">
                {{ __('Forget Password') }}
            </button>
        </div>
    </form>
</x-guest-layout>
