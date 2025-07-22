    <x-app-layout>
        <x-dashboard.sidebar>
            <x-alert-information></x-alert-information>

            <div class="mt-4">
                <a href="{{ route('equipments.index') }}" onclick="showFullScreenLoader();"
                    class="inline-flex items-center text-gray-700 hover:text-purple-600 dark:text-gray-300 dark:hover:text-white transition duration-150 ease-in-out">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 32 32" class="mr-2">
                        <path fill="#101820"
                            d="M30,29a1,1,0,0,1-.81-.41l-2.12-2.92A18.66,18.66,0,0,0,15,18.25V22a1,1,0,0,1-1.6.8l-12-9a1,1,0,0,1,0-1.6l12-9A1,1,0,0,1,15,4V8.24A19,19,0,0,1,31,27v1a1,1,0,0,1-.69.95A1.12,1.12,0,0,1,30,29ZM14,16.11h.1A20.68,20.68,0,0,1,28.69,24.5l.16.21a17,17,0,0,0-15-14.6,1,1,0,0,1-.89-1V6L3.67,13,13,20V17.11a1,1,0,0,1,.33-.74A1,1,0,0,1,14,16.11Z" />
                    </svg>
                    <span class="text-sm font-medium">Back</span>
                </a>
            </div>

            <div class="flex justify-between items-center mt-4 w-full max-w-full">
                <h2 class="font-bold text-xl sm:text-2xl">Equipment Detail</h2>
            </div>

            <hr class="h-[3px] my-4 bg-gray-200 border-0 dark:bg-gray-700 w-full">

            @php
                $status = strtolower($equipment->status);
                $statusColor = match ($status) {
                    'available' => 'bg-green-100 text-green-800',
                    'used' => 'bg-teal-100 text-teal-800',
                    'maintenance' => 'bg-red-100 text-red-800',
                    default => 'bg-gray-100 text-gray-600',
                };

                $fields = [
                    'Item Name' => $equipment->item->name ?? '-',
                    'Type' => $equipment->item->type ?? '-',
                    'Brand' => $equipment->item->brand ?? '-',
                    'Model' => $equipment->item->model ?? '-',
                    'Serial Number' => $equipment->serial_number ?? '-',
                    'Qty' => $equipment->transaction->qty ?? '-',
                    'Transaction ID' => $equipment->transaction
                        ? '<a href="' .
                            route('transactions.show', $equipment->transaction->id) .
                            '" class="text-purple-600 hover:underline" target="_blank">' .
                            $equipment->transaction->id .
                            '</a>'
                        : '-',
                    'Location' => $equipment->store->name ?? '-',
                    'Status' =>
                        '<span class="inline-block px-2 py-1 text-xs sm:text-sm font-semibold rounded-md ' .
                        $statusColor .
                        '">' .
                        ucfirst($equipment->status) .
                        '</span>',
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
                        <div class="flex items-start gap-4">
                            <div class="w-40 font-medium">Photo</div>
                            <div class="flex-1" id="equipment-photo-viewer">
                                @if ($equipment->transaction && $equipment->transaction->photoitems)
                                    <img src="{{ asset('storage/' . $equipment->transaction->photoitems) }}"
                                        alt="Photo" class="w-48 h-auto rounded-md shadow cursor-pointer"
                                        style="max-height: 200px;" />
                                @else
                                    <span class="italic text-gray-400">No photo available</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- DESKTOP VERSION --}}
                <div class="hidden md:grid grid-cols-1 md:grid-cols-4 gap-6">
                    @foreach ($fields as $label => $value)
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">{{ $label }}</label>
                            <div
                                class="px-3 py-2 {{ in_array($label, ['Status']) ? '' : 'bg-gray-100 dark:bg-gray-700 rounded-md' }}">
                                {!! $value !!}
                            </div>
                        </div>
                    @endforeach
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Photo</label>
                        <div class="px-3 py-2" id="equipment-photo-viewer">
                            @if ($equipment->transaction && $equipment->transaction->photoitems)
                                <img src="{{ asset('storage/' . $equipment->transaction->photoitems) }}" alt="Photo"
                                    class="w-48 h-auto rounded-md shadow cursor-pointer" style="max-height: 200px;" />
                            @else
                                <span class="italic text-gray-400">No photo available</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="my-8"></div>

            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md mb-6">
                <h3 class="font-semibold text-lg mb-4 text-gray-800 dark:text-white">History Maintenance</h3>

                @if ($equipment->maintenances->isEmpty())
                    <p class="text-sm text-gray-500 italic">No maintenance history found.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-max text-sm text-left border border-gray-200 dark:border-gray-700">
                            <thead class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                <tr>
                                    <th class="px-4 py-2 border">Maintenance ID</th>
                                    <th class="px-4 py-2 border">Date</th>
                                    <th class="px-4 py-2 border">Frequency</th>
                                    <th class="px-4 py-2 border">PIC</th>
                                    <th class="px-4 py-2 border">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800">
                                @foreach ($equipment->maintenances as $m)
                                    <tr class="border-b dark:border-gray-700">
                                        <td class="px-4 py-2">
                                            <a href="{{ $m->status === 'completed' ? route('maintenances.showCompletedDetail', $m->id) : route('maintenances.show', $m->id) }}"
                                                class="text-purple-600 hover:underline" target="blank">
                                                {{ $m->unique_id ?? 'MTN' . str_pad($m->id, 5, '0', STR_PAD_LEFT) }}
                                            </a>
                                        </td>
                                        <td class="px-4 py-2">
                                            {{ \Carbon\Carbon::parse($m->maintenance_date)->format('d M Y') }}</td>
                                        <td class="px-4 py-2 capitalize">{{ $m->frequensi }}</td>
                                        <td class="px-4 py-2">{{ $m->staff->name ?? '-' }}</td>
                                        <td class="px-4 py-2">
                                            <span
                                                class="inline-block px-2 py-1 text-xs font-semibold rounded-md
                                        {{ match ($m->status) {
                                            'not due' => 'bg-gray-100 text-gray-800',
                                            'maintenance' => 'bg-yellow-100 text-yellow-700',
                                            'in progress' => 'bg-blue-100 text-blue-700',
                                            'resolved' => 'bg-green-100 text-green-700',
                                            'completed' => 'bg-purple-100 text-purple-700',
                                            default => 'bg-gray-100 text-gray-600',
                                        } }}">
                                                {{ ucfirst($m->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>


            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
                <h3 class="font-semibold text-lg mb-4 text-gray-800 dark:text-white">History Incident</h3>

                @if ($equipment->incidents->isEmpty())
                    <p class="text-sm text-gray-500 italic">No incident history found.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-max text-sm text-left border border-gray-200 dark:border-gray-700">
                            <thead class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                <tr>
                                    <th class="px-4 py-2 border">Incident ID</th>
                                    <th class="px-4 py-2 border">Date</th>
                                    <th class="px-4 py-2 border">Reported By</th>
                                    <th class="px-4 py-2 border">Resolved By</th>
                                    <th class="px-4 py-2 border">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800">
                                @foreach ($equipment->incidents as $incident)
                                    <tr class="border-b dark:border-gray-700">
                                        <td class="px-4 py-2">
                                            <a href="{{ $incident->status === 'completed' ? route('incidents.showCompletedDetail', $incident->id) : route('incidents.show', $incident->id) }}"
                                                class="text-purple-600 hover:underline" target="blank">
                                                {{ $incident->unique_id }}
                                            </a>
                                        </td>
                                        <td class="px-4 py-2">{{ $incident->created_at->format('d M Y') }}</td>
                                        <td class="px-4 py-2">{{ $incident->user->name ?? '-' }}</td>
                                        <td class="px-4 py-2">{{ $incident->picUser->name ?? '-' }}</td>
                                        <td class="px-4 py-2">
                                            <span
                                                class="inline-block px-2 py-1 text-xs font-semibold rounded-md
                                        {{ match ($incident->status) {
                                            'waiting' => 'bg-yellow-100 text-yellow-800',
                                            'in progress' => 'bg-blue-100 text-blue-800',
                                            'resolved' => 'bg-green-100 text-green-800',
                                            'completed' => 'bg-purple-100 text-purple-800',
                                            default => 'bg-gray-100 text-gray-700',
                                        } }}">
                                                {{ ucfirst($incident->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>


            @push('scripts')
                <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        const imageContainer = document.getElementById('equipment-photo-viewer');
                        if (imageContainer) {
                            new Viewer(imageContainer, {
                                inline: false,
                                toolbar: true,
                                movable: true,
                                zoomable: true,
                                scalable: true,
                                transition: true,
                            });
                        }
                    });
                </script>
            @endpush

        </x-dashboard.sidebar>
    </x-app-layout>
