<x-app-layout>
    <x-dashboard.sidebar>
        <x-alert-information />

        <div class="text-xl font-bold mt-5 mb-6 text-gray-800 dark:text-white">
            Welcome, {{ Auth::user()->name }}
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
            <x-dashboard.stat-card title="Total Request" :value="$totalRequests" :icon="view('components.icons.request-icon')"
                href="{{ route('requests.index') }}" />
            <x-dashboard.stat-card title="Total Incident" :value="$totalIncidents" :icon="view('components.icons.incident-icon')"
                href="{{ route('incidents.index') }}" />
            <x-dashboard.stat-card title="Equipment" :value="$totalEquipments" :icon="view('components.icons.equipment-icon')"
                href="{{ route('equipments.index') }}" />
        </div>

        {{-- data Incidents --}}
        <div class="flex justify-between mb-4 mt-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Latest Incidents</h3>
            <x-buttons.action-button text="More Incidents" color="purple" class="w-fit"
                href="{{ route('incidents.index') }}" onclick="showFullScreenLoader()" />
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow mb-6">
            @if ($incidents->isEmpty())
                <p class="text-gray-500 italic">No data incidents.</p>
            @else
                <x-table.incident :incidents="$incidents" :perPage="null" :showPagination="false" />
            @endif
        </div>
        <div class="flex justify-between mb-4 mt-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Latest Request</h3>
            <x-buttons.action-button text="More Requests" color="purple" class="w-fit"
                href="{{ route('requests.index') }}" onclick="showFullScreenLoader()" />
        </div>
        {{-- data Requests --}}
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow mb-6">

            @if ($requestsModel->isEmpty())
                <p class="text-gray-500 italic">No data requests.</p>
            @else
                <x-table.request :requests="$requestsModel" :perPage="null" :showPagination="false" />
            @endif
        </div>

    </x-dashboard.sidebar>
</x-app-layout>
