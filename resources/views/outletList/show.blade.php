<x-app-layout>
    <x-dashboard.sidebar>
        <x-alert-information></x-alert-information>

        <div class="mt-4">
            <a href="{{ route('outlets.index') }}" onclick="showFullScreenLoader();"
                class="inline-flex items-center text-gray-700 hover:text-purple-600 dark:text-gray-300 dark:hover:text-white transition duration-150 ease-in-out">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 32 32" class="mr-2">
                    <path fill="#101820"
                        d="M30,29a1,1,0,0,1-.81-.41l-2.12-2.92A18.66,18.66,0,0,0,15,18.25V22a1,1,0,0,1-1.6.8l-12-9a1,1,0,0,1,0-1.6l12-9A1,1,0,0,1,15,4V8.24A19,19,0,0,1,31,27v1a1,1,0,0,1-.69.95A1.12,1.12,0,0,1,30,29ZM14,16.11h.1A20.68,20.68,0,0,1,28.69,24.5l.16.21a17,17,0,0,0-15-14.6,1,1,0,0,1-.89-1V6L3.67,13,13,20V17.11a1,1,0,0,1,.33-.74A1,1,0,0,1,14,16.11Z" />
                </svg>
                <span class="text-sm font-medium">Back</span>
            </a>
        </div>

        <div class="flex justify-between items-center mt-4 w-full max-w-full">
            <h2 class="font-bold text-xl sm:text-2xl">Outlet Detail</h2>
        </div>

        <hr class="h-[3px] my-4 bg-gray-200 border-0 dark:bg-gray-700 w-full">

        @php
            $fields = [
                'Outlet Name' => $outlet->name ?? '-',
                'Site Code' => $outlet->site_code ?? '-',
                'Since' => $outlet->since ?? '-',
                'Location' => $outlet->location ?? '-',
            ];
        @endphp

        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md text-sm text-gray-700 dark:text-gray-300">
            {{-- MOBILE VERSION --}}
            <div class="block md:hidden space-y-4">
                <div class="grid grid-cols-1 gap-3">
                    @foreach ($fields as $label => $value)
                        <div class="flex">
                            <div class="w-40 font-medium">{{ $label }}</div>
                            <div class="flex-1">: {!! $value !!}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- DESKTOP VERSION --}}
            <div class="hidden md:grid grid-cols-1 md:grid-cols-4 gap-6">
                @foreach ($fields as $label => $value)
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">{{ $label }}</label>
                        <div class="bg-gray-100 dark:bg-gray-700 rounded-md px-3 py-2">
                            {!! $value !!}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- USERS SECTION --}}
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md mt-6">
            <h3 class="font-semibold text-lg mb-4 text-gray-800 dark:text-white">Users in this Outlet</h3>

            @if ($outlet->users->isEmpty())
                <p class="text-sm text-gray-500 italic">No users found.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left border border-gray-200 dark:border-gray-700">
                        <thead class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                            <tr>
                                <th class="px-4 py-2 border">Name</th>
                                <th class="px-4 py-2 border">Email</th>
                                <th class="px-4 py-2 border">No Telfon</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 whitespace-nowrap">
                            @foreach ($outlet->users as $user)
                                <tr class="border-b dark:border-gray-700">
                                    <td class="px-4 py-2">{{ $user->name }}</td>
                                    <td class="px-4 py-2">{{ $user->email }}</td>
                                    <td class="px-4 py-2">{{ $user->no_telfon ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- EQUIPMENTS SECTION --}}
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md mt-6">
            <h3 class="font-semibold text-lg mb-4 text-gray-800 dark:text-white">Equipments in this Outlet</h3>

            @if ($outlet->equipments->isEmpty())
                <p class="text-sm text-gray-500 italic">No equipments found.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left border border-gray-200 dark:border-gray-700">
                        <thead class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                            <tr>
                                <th class="px-4 py-2 border">Item</th>
                                <th class="px-4 py-2 border">S/N</th>
                                <th class="px-4 py-2 border">Status</th>
                                <th class="px-4 py-2 border">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800">
                            @foreach ($outlet->equipments as $eq)
                                <tr class="border-b dark:border-gray-700">
                                    <td class="px-4 py-2">{{ $eq->item->name ?? '-' }}</td>
                                    <td class="px-4 py-2">{{ $eq->serial_number ?? '-' }}</td>
                                    <td class="px-4 py-2">
                                        <span
                                            class="inline-block px-2 py-1 text-xs font-semibold rounded-md {{ match (strtolower($eq->status)) {
                                                'available' => 'bg-green-100 text-green-800',
                                                'used' => 'bg-yellow-100 text-yellow-800',
                                                'maintenance' => 'bg-red-100 text-red-800',
                                                default => 'bg-gray-100 text-gray-600',
                                            } }}">
                                            {{ ucfirst($eq->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2">
                                        <a href="{{ route('equipments.show', $eq->id) }}"
                                            class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-1 text-xs rounded">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </x-dashboard.sidebar>
</x-app-layout>
