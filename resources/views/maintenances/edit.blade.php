<x-app-layout>
    <x-dashboard.sidebar>
        <x-alert-information></x-alert-information>

        <div class="mt-4">
            <a href="{{ route('maintenances.index') }}" onclick="showFullScreenLoader();"
                class="inline-flex items-center text-gray-700 hover:text-purple-600 dark:text-gray-300 dark:hover:text-white transition duration-150 ease-in-out">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 32 32" class="mr-2">
                    <path fill="#101820"
                        d="M30,29a1,1,0,0,1-.81-.41l-2.12-2.92A18.66,18.66,0,0,0,15,18.25V22a1,1,0,0,1-1.6.8l-12-9a1,1,0,0,1,0-1.6l12-9A1,1,0,0,1,15,4V8.24A19,19,0,0,1,31,27v1a1,1,0,0,1-.69.95A1.12,1.12,0,0,1,30,29ZM14,16.11h.1A20.68,20.68,0,0,1,28.69,24.5l.16.21a17,17,0,0,0-15-14.6,1,1,0,0,1-.89-1V6L3.67,13,13,20V17.11a1,1,0,0,1,.33-.74A1,1,0,0,1,14,16.11Z" />
                </svg>
                <span class="text-sm font-medium">Back</span>
            </a>
        </div>

        <div class="flex justify-between items-center mt-4 w-full max-w-full">
            <h2 class="font-bold text-xl sm:text-2xl">
                Edit Maintenance
            </h2>
        </div>

        <hr class="h-[3px] my-4 bg-gray-200 border-0 dark:bg-gray-700 w-full">

        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md text-sm text-gray-700 dark:text-gray-300">
            <form method="POST" action="{{ route('maintenances.update', $maintenance->id) }} "
                onsubmit="return confirmAndLoad('Are you sure to updated?')">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex">
                        <div class="w-40 font-medium">Name</div>
                        <div class="flex-1">: {{ $maintenance->equipment->item->name ?? '-' }}</div>
                    </div>
                    <div class="flex">
                        <div class="w-40 font-medium">Location</div>
                        <div class="flex-1">: {{ $maintenance->equipment->store->name ?? '-' }}</div>
                    </div>
                    <div class="flex">
                        <div class="w-40 font-medium">S/N</div>
                        <div class="flex-1">: {{ $maintenance->equipment->serial_number ?? '-' }}</div>
                    </div>
                    <div class="flex">
                        <div class="w-40 font-medium">Brand</div>
                        <div class="flex-1">: {{ $maintenance->equipment->item->brand ?? '-' }}</div>
                    </div>
                    <div class="flex">
                        <div class="w-40 font-medium">Model</div>
                        <div class="flex-1">: {{ $maintenance->equipment->item->model ?? '-' }}</div>
                    </div>
                    <div class="flex">
                        <div class="w-40 font-medium">Status</div>
                        <div class="flex-1">
                            @php
                                $status = strtolower($maintenance->status);
                                $color = match ($status) {
                                    'completed' => 'bg-green-100 text-green-800',
                                    'maintenance' => 'bg-red-100 text-red-600',
                                    'in progress' => 'bg-blue-100 text-blue-800',
                                    'resolved' => 'bg-purple-100 text-purple-800',
                                    default => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            : <span class="px-3 py-1 text-xs font-medium rounded-md {{ $color }}">
                                {{ ucfirst($maintenance->status) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Maintenance
                            Date</label>
                        <input type="date" name="maintenance_date"
                            value="{{ \Carbon\Carbon::parse($maintenance->maintenance_date)->format('Y-m-d') }}"
                            class="w-full px-3 py-2 border rounded-md dark:bg-gray-700 dark:text-white" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Frequency</label>
                        <select name="frequensi" required
                            class="w-full px-3 py-2 border rounded-md dark:bg-gray-700 dark:text-white">
                            <option value="weekly" {{ $maintenance->frequensi === 'weekly' ? 'selected' : '' }}>Weekly
                            </option>
                            <option value="monthly" {{ $maintenance->frequensi === 'monthly' ? 'selected' : '' }}>
                                Monthly</option>
                        </select>
                    </div>
                </div>

                <div class="mt-8 flex justify-end">
                    <button type="submit" class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-md">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </x-dashboard.sidebar>
</x-app-layout>
