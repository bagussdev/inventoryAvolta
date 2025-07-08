<x-app-layout>
    <x-dashboard.sidebar>
        <x-alert-information />

        <div class="text-xl font-bold mt-5 mb-6 text-gray-800 dark:text-white">
            Welcome, {{ Auth::user()->name }}
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-4 w-full">
                <div class="flex justify-between items-center gap-4 mb-3">
                    <h2 class="text-sm font-medium text-gray-600 dark:text-gray-300">
                        Incident
                    </h2>
                    <div class="bg-yellow-300 rounded-2xl w-10 h-10 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6 text-gray-800" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4.5V19a1 1 0 0 0 1 1h15M7 14l4-4 4 4 5-5m0 0h-3.207M20 9v3.207" />
                        </svg>
                    </div>
                </div>
                <p class="text-4xl font-bold text-gray-900 dark:text-white">
                    {{ $totalIncidents }}
                </p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-4 w-full">
                <div class="flex justify-between items-center gap-4 mb-3">
                    <h2 class="text-sm font-medium text-gray-600 dark:text-gray-300">
                        Request
                    </h2>
                    <div class="bg-yellow-300 rounded-2xl w-10 h-10 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6 text-gray-800" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4.5V19a1 1 0 0 0 1 1h15M7 14l4-4 4 4 5-5m0 0h-3.207M20 9v3.207" />
                        </svg>
                    </div>
                </div>
                <p class="text-4xl font-bold text-gray-900 dark:text-white">
                    {{ $totalRequests }}
                </p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-4 w-full">
                <div class="flex justify-between items-center gap-4 mb-3">
                    <h2 class="text-sm font-medium text-gray-600 dark:text-gray-300">
                        Equipment
                    </h2>
                    <div class="bg-yellow-300 rounded-2xl w-10 h-10 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6 text-gray-800" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4.5V19a1 1 0 0 0 1 1h15M7 14l4-4 4 4 5-5m0 0h-3.207M20 9v3.207" />
                        </svg>
                    </div>
                </div>
                <p class="text-4xl font-bold text-gray-900 dark:text-white">
                    {{ $totalEquipments }}
                </p>
            </div>
        </div>

        {{-- Waiting Incidents --}}
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Incidents Waiting</h3>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow mb-6">

            @if ($waitingIncidents->isEmpty())
                <p class="text-gray-500 italic">No waiting incidents.</p>
            @else
                <x-table.incident :data="$waitingIncidents" />
            @endif
        </div>

        {{-- Waiting Requests --}}
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow mb-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Requests Waiting</h3>
            @if ($waitingRequests->isEmpty())
                <p class="text-gray-500 italic">No waiting requests.</p>
            @else
                <x-table.request :data="$waitingRequests" />
            @endif
        </div>

    </x-dashboard.sidebar>
</x-app-layout>
