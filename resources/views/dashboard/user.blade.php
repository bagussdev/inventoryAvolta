<x-app-layout>
    <x-dashboard.sidebar>
        <x-alert-information />

        <div class="text-xl font-bold mt-5 mb-6 text-gray-800 dark:text-white">
            Welcome, {{ Auth::user()->name }}
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-6">
            <x-dashboard.stat-card title="Request" :value="$totalRequests" :icon="'<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'24\' height=\'24\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'#000000\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\' class=\'lucide lucide-file-plus2-icon lucide-file-plus-2\'><path d=\'M4 22h14a2 2 0 0 0 2-2V7l-5-5H6a2 2 0 0 0-2 2v4\'/><path d=\'M14 2v4a2 2 0 0 0 2 2h4\'/><path d=\'M3 15h6\'/><path d=\'M6 12v6\'/></svg>'" />
            <x-dashboard.stat-card title="Incident" :value="$totalIncidents" :icon="'<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'24\' height=\'24\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'#000000\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\' class=\'lucide lucide-user-cog-icon lucide-user-cog\'><path d=\'M10 15H6a4 4 0 0 0-4 4v2\'/><path d=\'m14.305 16.53.923-.382\'/><path d=\'m15.228 13.852-.923-.383\'/><path d=\'m16.852 12.228-.383-.923\'/><path d=\'m16.852 17.772-.383.924\'/><path d=\'m19.148 12.228.383-.923\'/><path d=\'m19.53 18.696-.382-.924\'/><path d=\'m20.772 13.852.924-.383\'/><path d=\'m20.772 16.148.924.383\'/><circle cx=\'18\' cy=\'15\' r=\'3\'/><circle cx=\'9\' cy=\'7\' r=\'4\'/></svg>'" />
            <x-dashboard.stat-card title="Equipment" :value="$totalEquipments" :icon="'<svg class=\'w-6 h-6 text-gray-800\' xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M5 13l4 4L19 7\'/></svg>'" />

        </div>

        {{-- Waiting Incidents --}}
        <div class="flex justify-between mb-4 mt-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Incidents Waiting</h3>
            <x-buttons.action-button text="More Incidents" color="purple" class=""
                href="{{ route('incidents.index') }}" onclick="showFullScreenLoader()" />
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow mb-6">
            @if ($incidents->isEmpty())
                <p class="text-gray-500 italic">No waiting incidents.</p>
            @else
                <x-table.incident :incidents="$incidents" :perPage="null" :showPagination="false" />
            @endif
        </div>
        <div class="flex justify-between mb-4 mt-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Requests Waiting</h3>
            <x-buttons.action-button text="More Requests" color="purple" class=""
                href="{{ route('requests.index') }}" onclick="showFullScreenLoader()" />
        </div>
        {{-- Waiting Requests --}}
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow mb-6">

            @if ($requestsModel->isEmpty())
                <p class="text-gray-500 italic">No waiting requests.</p>
            @else
                <x-table.request :requests="$requestsModel" :perPage="null" :showPagination="false" />
            @endif
        </div>

    </x-dashboard.sidebar>
</x-app-layout>
