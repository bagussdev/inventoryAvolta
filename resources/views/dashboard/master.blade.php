<x-app-layout>
    <x-dashboard.sidebar>
        <x-alert-information />

        <div class="text-xl font-bold mb-6 text-gray-800 dark:text-white">
            Welcome, {{ auth()->user()->name }}
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 p-4 rounded shadow flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Incident Pending</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $totalIncidents }}</p>
                </div>
                <div class="text-2xl">
                    🚨
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 p-4 rounded shadow flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Request Pending</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $totalRequests }}</p>
                </div>
                <div class="text-2xl">
                    📢
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 p-4 rounded shadow flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Maintenance Pending</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $totalMaintenances }}</p>
                </div>
                <div class="text-2xl">
                    📆
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 p-4 rounded shadow flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Outlet List</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $totalEquipments }}</p>
                </div>
                <div class="text-2xl">
                    🛋️
                </div>
            </div>
        </div>

        {{-- Latest Maintenance Table --}}
        <div class="flex justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Latest Maintenance</h3>
            <x-buttons.action-button text="More Maintenance" color="purple" class=""
                href="{{ route('maintenances.index') }}" onclick="showFullScreenLoader()" />
        </div>
        @if ($maintenances->count() > 0)
            <x-table.maintenance :maintenances="$maintenances" :perPage="$perPage ?? null" :showPagination="false" />
        @else
            <div class="text-center text-sm text-gray-500 dark:text-gray-400 py-8">
                Not found data with status maintenance.
            </div>
        @endif

        {{-- Latest Incident Table --}}
        <div class="flex justify-between mb-4 mt-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Latest Incidents</h3>
            <x-buttons.action-button text="More Incidents" color="purple" class=""
                href="{{ route('incidents.index') }}" onclick="showFullScreenLoader()" />
        </div>
        @if ($incidents->count() > 0)
            <x-table.incident :incidents="$incidents" :perPage="null" :showPagination="false" />
        @else
            <div class="text-center text-sm text-gray-500 dark:text-gray-400 py-8">
                Not found incidents data with status waiting.
            </div>
        @endif


    </x-dashboard.sidebar>
</x-app-layout>
