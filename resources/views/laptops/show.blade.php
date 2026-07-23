<x-app-layout>
    <x-dashboard.sidebar>
        <x-alert-information />

        {{-- Back button --}}
        <div class="mt-4">
            <a href="{{ route('laptops.index') }}" onclick="showFullScreenLoader();"
                class="inline-flex items-center text-gray-700 hover:text-purple-600 dark:text-gray-300 dark:hover:text-white transition duration-150 ease-in-out">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 32 32" class="mr-2">
                    <path fill="#101820"
                        d="M30,29a1,1,0,0,1-.81-.41l-2.12-2.92A18.66,18.66,0,0,0,15,18.25V22a1,1,0,0,1-1.6.8l-12-9a1,1,0,0,1,0-1.6l12-9A1,1,0,0,1,15,4V8.24A19,19,0,0,1,31,27v1a1,1,0,0,1-.69.95A1.12,1.12,0,0,1,30,29ZM14,16.11h.1A20.68,20.68,0,0,1,28.69,24.5l.16.21a17,17,0,0,0-15-14.6,1,1,0,0,1-.89-1V6L3.67,13,13,20V17.11a1,1,0,0,1,.33-.74A1,1,0,0,1,14,16.11Z" />
                </svg>
                <span class="text-sm font-medium">Back</span>
            </a>
        </div>

        {{-- Title --}}
        <div class="flex justify-between items-center mt-4 w-full max-w-full">
            <h2 class="font-bold text-xl sm:text-2xl">Laptop Detail</h2>
        </div>

        <hr class="h-[3px] my-4 bg-gray-200 border-0 dark:bg-gray-700 w-full">

        {{-- Detail Card --}}
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md text-sm text-gray-700 dark:text-gray-300">
            {{-- MOBILE VERSION --}}
            <div class="block md:hidden space-y-4">
                <div class="grid grid-cols-1 gap-3">
                    <div class="flex">
                        <div class="w-40 font-medium">Hostname</div>
                        <div class="flex-1">: {{ $laptop->hostname }}</div>
                    </div>
                    <div class="flex">
                        <div class="w-40 font-medium">Serial Number</div>
                        <div class="flex-1">: {{ $laptop->serialnumber ?? '-' }}</div>
                    </div>
                    <div class="flex">
                        <div class="w-40 font-medium">Model</div>
                        <div class="flex-1">: {{ $laptop->model ?? '-' }}</div>
                    </div>
                    <div class="flex">
                        <div class="w-40 font-medium">Brand</div>
                        <div class="flex-1">: {{ $laptop->brand ?? '-' }}</div>
                    </div>
                    <div class="flex">
                        <div class="w-40 font-medium">User</div>
                        <div class="flex-1">: {{ $laptop->user ?? '-' }}</div>
                    </div>
                    <div class="flex">
                        <div class="w-40 font-medium">Location</div>
                        <div class="flex-1">: {{ $laptop->store->name ?? '-' }}</div>
                    </div>
                    <div class="flex">
                        <div class="w-40 font-medium">Windows</div>
                        <div class="flex-1">: {{ $laptop->typewindows ?? '-' }}</div>
                    </div>
                    <div class="flex">
                        <div class="w-40 font-medium">OS Status</div>
                        <div class="flex-1">: {{ $laptop->osstatus ?? '-' }}</div>
                    </div>
                    <div class="flex">
                        <div class="w-40 font-medium">IP RealVNC</div>
                        <div class="flex-1">: {{ $laptop->iprealvnc ?? '-' }}</div>
                    </div>
                    <div class="flex">
                        <div class="w-40 font-medium">Created By</div>
                        <div class="flex-1">: {{ $laptop->creator->name ?? '-' }}</div>
                    </div>
                    <div class="flex">
                        <div class="w-40 font-medium">Status</div>
                        <div class="flex-1">:
                            <span
                                class="inline-block px-2 py-1 text-xs font-semibold rounded-md
                                {{ match ($laptop->status) {
                                    'available' => 'bg-green-100 text-green-800',
                                    'in_use' => 'bg-blue-100 text-blue-800',
                                    'broken' => 'bg-red-100 text-red-800',
                                    'scrap' => 'bg-gray-200 text-gray-800',
                                    default => 'bg-gray-100 text-gray-800',
                                } }}">
                                {{ ucfirst(str_replace('_', ' ', $laptop->status)) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- DESKTOP VERSION --}}
            <div class="hidden md:grid grid-cols-1 md:grid-cols-3 gap-6">
                <div><label class="block text-sm font-medium mb-1">Hostname</label>
                    <div class="px-3 py-2 bg-gray-100 dark:bg-gray-700 rounded-md">{{ $laptop->hostname }}</div>
                </div>
                <div><label class="block text-sm font-medium mb-1">Serial Number</label>
                    <div class="px-3 py-2 bg-gray-100 dark:bg-gray-700 rounded-md">{{ $laptop->serialnumber ?? '-' }}
                    </div>
                </div>
                <div><label class="block text-sm font-medium mb-1">Model</label>
                    <div class="px-3 py-2 bg-gray-100 dark:bg-gray-700 rounded-md">{{ $laptop->model ?? '-' }}</div>
                </div>
                <div><label class="block text-sm font-medium mb-1">Brand</label>
                    <div class="px-3 py-2 bg-gray-100 dark:bg-gray-700 rounded-md">{{ $laptop->brand ?? '-' }}</div>
                </div>
                <div><label class="block text-sm font-medium mb-1">User</label>
                    <div class="px-3 py-2 bg-gray-100 dark:bg-gray-700 rounded-md">{{ $laptop->user ?? '-' }}</div>
                </div>
                <div><label class="block text-sm font-medium mb-1">Location</label>
                    <div class="px-3 py-2 bg-gray-100 dark:bg-gray-700 rounded-md">{{ $laptop->store->name ?? '-' }}
                    </div>
                </div>
                <div><label class="block text-sm font-medium mb-1">Windows</label>
                    <div class="px-3 py-2 bg-gray-100 dark:bg-gray-700 rounded-md">{{ $laptop->typewindows ?? '-' }}
                    </div>
                </div>
                <div><label class="block text-sm font-medium mb-1">OS Status</label>
                    <div class="px-3 py-2 bg-gray-100 dark:bg-gray-700 rounded-md">{{ $laptop->osstatus ?? '-' }}</div>
                </div>
                <div><label class="block text-sm font-medium mb-1">IP RealVNC</label>
                    <div class="px-3 py-2 bg-gray-100 dark:bg-gray-700 rounded-md">{{ $laptop->iprealvnc ?? '-' }}
                    </div>
                </div>
                <div><label class="block text-sm font-medium mb-1">Created By</label>
                    <div class="px-3 py-2 bg-gray-100 dark:bg-gray-700 rounded-md">{{ $laptop->creator->name ?? '-' }}
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Status</label>
                    <span
                        class="inline-block px-2 py-1 text-xs font-semibold rounded-md
                        {{ match ($laptop->status) {
                            'available' => 'bg-green-100 text-green-800',
                            'in_use' => 'bg-blue-100 text-blue-800',
                            'broken' => 'bg-red-100 text-red-800',
                            'scrap' => 'bg-gray-200 text-gray-800',
                            default => 'bg-gray-100 text-gray-800',
                        } }}">
                        {{ ucfirst(str_replace('_', ' ', $laptop->status)) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Action buttons bawah --}}
        <div class="flex gap-2 mt-4">
            @can('laptops.edit')
                <a href="{{ route('laptops.edit', $laptop->id) }}" onclick="showFullScreenLoader();">
                    <x-buttons.action-button text="Edit" color="blue" />
                </a>
            @endcan
            @can('laptops.delete')
                <form action="{{ route('laptops.destroy', $laptop->id) }}" method="POST"
                    onsubmit="return confirmAndLoad('Are you sure to delete this laptop?')">
                    @csrf
                    @method('DELETE')
                    <x-buttons.action-button text="Delete" color="red" />
                </form>
            @endcan
        </div>
    </x-dashboard.sidebar>
</x-app-layout>
