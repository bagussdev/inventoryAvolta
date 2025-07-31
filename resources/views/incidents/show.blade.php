<x-app-layout>
    <x-dashboard.sidebar>
        <x-alert-information></x-alert-information>

        {{-- Back button --}}
        @if ($incident->status === 'completed')
            <div class="mt-4">
                <a href="{{ route('incidents.completed') }}" onclick="showFullScreenLoader();"
                    class="inline-flex items-center text-gray-700 hover:text-purple-600 dark:text-gray-300 dark:hover:text-white transition duration-150 ease-in-out">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 32 32" class="mr-2">
                        <path fill="#101820"
                            d="M30,29a1,1,0,0,1-.81-.41l-2.12-2.92A18.66,18.66,0,0,0,15,18.25V22a1,1,0,0,1-1.6.8l-12-9a1,1,0,0,1,0-1.6l12-9A1,1,0,0,1,15,4V8.24A19,19,0,0,1,31,27v1a1,1,0,0,1-.69.95A1.12,1.12,0,0,1,30,29ZM14,16.11h.1A20.68,20.68,0,0,1,28.69,24.5l.16.21a17,17,0,0,0-15-14.6,1,1,0,0,1-.89-1V6L3.67,13,13,20V17.11a1,1,0,0,1,.33-.74A1,1,0,0,1,14,16.11Z" />
                    </svg>
                    <span class="text-sm font-medium">Back</span>
                </a>
            </div>
        @else
            <div class="mt-4">
                <a href="{{ route('incidents.index') }}" onclick="showFullScreenLoader();"
                    class="inline-flex items-center text-gray-700 hover:text-purple-600 dark:text-gray-300 dark:hover:text-white transition duration-150 ease-in-out">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 32 32"
                        class="mr-2">
                        <path fill="#101820"
                            d="M30,29a1,1,0,0,1-.81-.41l-2.12-2.92A18.66,18.66,0,0,0,15,18.25V22a1,1,0,0,1-1.6.8l-12-9a1,1,0,0,1,0-1.6l12-9A1,1,0,0,1,15,4V8.24A19,19,0,0,1,31,27v1a1,1,0,0,1-.69.95A1.12,1.12,0,0,1,30,29ZM14,16.11h.1A20.68,20.68,0,0,1,28.69,24.5l.16.21a17,17,0,0,0-15-14.6,1,1,0,0,1-.89-1V6L3.67,13,13,20V17.11a1,1,0,0,1,.33-.74A1,1,0,0,1,14,16.11Z" />
                    </svg>
                    <span class="text-sm font-medium">Back</span>
                </a>
            </div>
        @endif

        {{-- Title --}}
        <div class="flex justify-between items-center mt-4 w-full max-w-full">
            <h2 class="font-bold text-xl sm:text-2xl">
                Incident Details
            </h2>
        </div>

        <hr class="h-[3px] my-4 bg-gray-200 border-0 dark:bg-gray-700 w-full">

        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md text-sm text-gray-700 dark:text-gray-300">
            {{-- Versi MOBILE (grid-col-1, gaya flex + titik dua) --}}
            {{-- Versi MOBILE diperbarui --}}
            <div class="block md:hidden">
                <div class="grid grid-cols-1 gap-4">
                    @php
                        $mobileFields = [
                            'ID Incident' => $incident->unique_id,
                            'Item Problem' => ucwords(
                                strtolower(
                                    optional(optional($incident->equipment)->item)->name .
                                        (optional($incident->equipment)->item &&
                                        (optional($incident->equipment)->alias || $incident->item_description)
                                            ? ' - ' .
                                                (optional($incident->equipment)->alias ?? $incident->item_description)
                                            : (!optional($incident->equipment)->item
                                                ? optional($incident->equipment)->alias ??
                                                    ($incident->item_description ?? '-')
                                                : '')),
                                ),
                            ),
                            'Model' => $incident->item->model ?? '-',
                            'Brand' => $incident->item->brand ?? '-',
                            'Location' => $incident->store->name ?? '-',

                            'User Report' => $incident->user
                                ? '<a href="javascript:void(0);" onclick=\'showUserModal(' .
                                    json_encode([
                                        'name' => $incident->user->name,
                                        'location' => optional($incident->user->location)->name,
                                        'email' => $incident->user->email,
                                        'phone' => $incident->user->no_telfon,
                                    ]) .
                                    ')\' class="text-purple-600 hover:underline">' .
                                    e($incident->user->name) .
                                    '</a>'
                                : '-',

                            'Department To' => $incident->department->name ?? '-',

                            'PIC Staff' => $incident->picUser
                                ? '<a href="javascript:void(0);" onclick=\'showUserModal(' .
                                    json_encode([
                                        'name' => $incident->picUser->name,
                                        'location' => optional($incident->picUser->location)->name,
                                        'email' => $incident->picUser->email,
                                        'phone' => $incident->picUser->no_telfon ?? '-',
                                    ]) .
                                    ')\' class="text-purple-600 hover:underline">' .
                                    e($incident->picUser->name) .
                                    '</a>'
                                : '-',

                            'Resolved By' => $incident->resolve
                                ? '<a href="javascript:void(0);" onclick=\'showUserModal(' .
                                    json_encode([
                                        'name' => $incident->resolve->name,
                                        'location' => optional($incident->resolve->location)->name,
                                        'email' => $incident->resolve->email,
                                        'phone' => $incident->resolve->no_telfon ?? '-',
                                    ]) .
                                    ')\' class="text-purple-600 hover:underline">' .
                                    e($incident->resolve->name) .
                                    '</a>'
                                : '-',

                            'Resolved At' => $incident->resolved_at
                                ? \Carbon\Carbon::parse($incident->resolved_at)->format('d M Y H:i')
                                : '-',

                            'Confirm By' => $incident->confirm
                                ? '<a href="javascript:void(0);" onclick=\'showUserModal(' .
                                    json_encode([
                                        'name' => $incident->confirm->name,
                                        'location' => optional($incident->confirm->location)->name,
                                        'email' => $incident->confirm->email,
                                        'phone' => $incident->confirm->no_telfon ?? '-',
                                    ]) .
                                    ')\' class="text-purple-600 hover:underline">' .
                                    e($incident->confirm->name) .
                                    '</a>'
                                : '-',
                        ];

                        $linkableLabels = ['User Report', 'PIC Staff', 'Resolved By', 'Confirm By'];
                    @endphp

                    @foreach ($mobileFields as $label => $value)
                        <div class="flex items-start gap-2">
                            <div class="w-40 font-medium shrink-0">{{ $label }}</div>
                            <div class="flex-1 break-all">
                                : @if (in_array($label, $linkableLabels))
                                    {!! $value !!}
                                @else
                                    {{ $value }}
                                @endif
                            </div>
                        </div>
                    @endforeach

                    {{-- Status dengan badge --}}
                    <div class="flex items-start gap-2">
                        <div class="w-40 font-medium shrink-0">Status</div>
                        <div class="flex-1">
                            : <span
                                class="inline-block px-3 py-1 text-xs font-medium rounded-md {{ match (strtolower($incident->status)) {
                                    'resolved' => 'bg-green-100 text-green-800',
                                    'in progress' => 'bg-blue-100 text-blue-800',
                                    'pending' => 'bg-red-100 text-red-800',
                                    'waiting' => 'bg-yellow-100 text-yellow-600',
                                    'completed' => 'bg-green-100 text-green-600',
                                    default => 'bg-gray-100 text-gray-600',
                                } }}">
                                {{ ucfirst($incident->status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Versi DESKTOP (grid-col-4, model label + box) --}}
            <div class="hidden md:block">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">ID Incident
                            :</label>
                        <div class="bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded-md">{{ $incident->unique_id }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Item Problem
                            :</label>
                        <div class="bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded-md">
                            {{ ucwords(
                                strtolower(
                                    optional(optional($incident->equipment)->item)->name .
                                        (optional($incident->equipment)->item &&
                                        (optional($incident->equipment)->alias || $incident->item_description)
                                            ? ' - ' . (optional($incident->equipment)->alias ?? $incident->item_description)
                                            : (!optional($incident->equipment)->item
                                                ? optional($incident->equipment)->alias ?? ($incident->item_description ?? '-')
                                                : '')),
                                ),
                            ) }}
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Model :</label>
                        <div class="bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded-md">
                            {{ $incident->item->model ?? '-' }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Brand :</label>
                        <div class="bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded-md">
                            {{ $incident->item->brand ?? '-' }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Location
                            :</label>
                        <div class="bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded-md">
                            {{ $incident->store->name ?? '-' }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">User Report
                            :</label>
                        <div class="bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded-md">
                            @if ($incident->user)
                                <a href="javascript:void(0);"
                                    onclick="showUserModal({{ json_encode([
                                        'name' => $incident->user->name,
                                        'location' => optional($incident->user->location)->name,
                                        'email' => $incident->user->email,
                                        'phone' => $incident->user->no_telfon ?? null,
                                    ]) }})"
                                    class="text-purple-600 hover:underline">
                                    {{ $incident->user->name }}
                                </a>
                            @else
                                -
                            @endif

                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Department To
                            :</label>
                        <div class="bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded-md">
                            {{ $incident->department->name ?? '-' }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">PIC Staff
                            :</label>
                        <div class="bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded-md">
                            {!! $incident->picUser
                                ? '<a href="javascript:void(0);" onclick=\'showUserModal(' .
                                    json_encode([
                                        'name' => $incident->picUser->name,
                                        'location' => optional($incident->picUser->location)->name,
                                        'email' => $incident->picUser->email,
                                        'phone' => $incident->picUser->no_telfon ?? '',
                                    ]) .
                                    ')\' class="text-purple-600 hover:underline">' .
                                    e($incident->picUser->name) .
                                    '</a>'
                                : '-' !!}
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Resolved By
                            :</label>
                        <div class="bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded-md">
                            {!! $incident->resolve
                                ? '<a href="javascript:void(0);" onclick=\'showUserModal(' .
                                    json_encode([
                                        'name' => $incident->resolve->name,
                                        'location' => optional($incident->resolve->location)->name,
                                        'email' => $incident->resolve->email,
                                        'phone' => $incident->resolve->no_telfon ?? '',
                                    ]) .
                                    ')\' class="text-purple-600 hover:underline">' .
                                    e($incident->resolve->name) .
                                    '</a>'
                                : '-' !!}
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Resolved At
                            :</label>
                        <div class="bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded-md">
                            {{ $incident->resolved_at ? \Carbon\Carbon::parse($incident->resolved_at)->format('d M Y H:i') : '-' }}
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Confirm By
                            :</label>
                        <div class="bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded-md">
                            @if ($incident->confirm)
                                <a href="javascript:void(0);"
                                    onclick="showUserModal({{ json_encode([
                                        'name' => $incident->confirm->name,
                                        'location' => optional($incident->confirm->location)->name,
                                        'email' => $incident->confirm->email,
                                        'phone' => $incident->confirm->no_telfon ?? null,
                                    ]) }})"
                                    class="text-purple-600 hover:underline">
                                    {{ $incident->confirm->name }}
                                </a>
                            @else
                                -
                            @endif
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Status :</label>
                        <span
                            class="inline-block px-3 py-1 text-xs font-semibold rounded-md {{ match (strtolower($incident->status)) {
                                'resolved' => 'bg-green-100 text-green-800',
                                'in progress' => 'bg-blue-100 text-blue-800',
                                'pending' => 'bg-red-100 text-red-800',
                                'waiting' => 'bg-yellow-100 text-yellow-600',
                                'completed' => 'bg-green-100 text-green-600',
                                default => 'bg-gray-100 text-gray-600',
                            } }}">
                            {{ ucfirst($incident->status) }}
                        </span>
                    </div>
                </div>
            </div>


            {{-- Message --}}
            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Left Column: Message User and Message Staff --}}
                <div>
                    <p class="font-semibold">Message User :</p>
                    <p class="italic text-gray-600 dark:text-gray-300">
                        {{ $incident->message_user ?? 'No message provided.' }}</p>

                    {{-- Message Staff now directly below Message User --}}
                    <div class="mt-6"> {{-- Add margin-top for separation between the two messages --}}
                        <p class="font-semibold">Message Staff :</p>
                        <p class="italic text-gray-600 dark:text-gray-300">
                            {{ $incident->message_staff ?? 'No message provided.' }}</p>
                    </div>
                    {{-- Used Spareparts (now below Attachment in the right column) --}}
                    @if (in_array(strtolower($incident->status), ['resolved', 'completed']))
                        <div class="mt-6"> {{-- Add margin-top for separation from Attachment --}}
                            <h4 class="text-md font-semibold mb-2">Used Spareparts</h4>
                            @if ($incident->usedSpareParts && $incident->usedSpareParts->isNotEmpty())
                                <ul class="list-disc pl-6">
                                    @foreach ($incident->usedSpareParts as $used)
                                        <li>{{ $used->sparepart->item->name ?? '-' }} — Qty:
                                            {{ $used->qty }}
                                            @if ($used->note)
                                                <span class="text-gray-500 italic">({{ $used->note }})</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-gray-500 italic">No spareparts used for this maintenance.</p>
                            @endif
                            @can('incident.update')
                                <div class="mt-4">
                                    <button onclick="openSparepartModal()"
                                        class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md inline-block">
                                        ⚙️ Manage Sparepart
                                    </button>
                                </div>
                            @endcan
                        </div>
                    @endif
                </div>


                {{-- Right Column: Attachment and Used Spareparts --}}
                <div>
                    {{-- Attachment --}}
                    <div>
                        <h4 class="text-md font-semibold mb-2">Attachment</h4>
                        @if ($incident->attachment_user && Storage::disk('public')->exists($incident->attachment_user))
                            @php
                                $ext = pathinfo($incident->attachment_user, PATHINFO_EXTENSION);
                                $url = asset('storage/' . $incident->attachment_user);
                            @endphp
                            @if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif']))
                                <img id="viewer-image-wrapper" src="{{ $url }}" alt="Attachment"
                                    class="max-h-40 w-full object-contain rounded shadow-md border border-gray-300 dark:border-gray-600" />
                            @elseif(in_array(strtolower($ext), ['mp4', 'mov', 'avi']))
                                <video src="{{ $url }}" controls
                                    class="rounded shadow w-full max-h-96 mt-2"></video>
                            @else
                                <a href="{{ $url }}" target="_blank" class="text-blue-600 underline"
                                    onclick="showFullScreenLoader();">See Attachment</a>
                            @endif
                        @else
                            <p class="text-gray-500 italic">No attachment uploaded.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <form id="sparepartsForm" action="{{ route('incidents.updateSpareparts', $incident->id) }}" method="POST"
            onsubmit="showFullScreenLoader();">
            @csrf
            @method('PUT')
            <div id="sparepartsContainer" class="hidden"></div>
            <div id="deleted_ids" class="hidden"></div>
        </form>

        @include('components.modal-user')
        @include('incidents.modal-spareparts')
        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const wrapper = document.getElementById('viewer-image-wrapper');
                    if (wrapper) {
                        new Viewer(wrapper, {
                            toolbar: true,
                            title: false,
                            tooltip: true,
                            movable: true,
                            zoomable: true,
                            scalable: false,
                            fullscreen: true,
                            transition: true,
                        });
                    }
                });
            </script>
            <script>
                function showUserModal(user) {
                    document.getElementById('detailName').textContent = user.name || '-';
                    document.getElementById('detailLocation').textContent = user.location || '-';

                    const emailLink = document.getElementById('detailEmail');
                    if (user.email) {
                        emailLink.textContent = user.email;
                        emailLink.href = 'mailto:' + user.email;
                    } else {
                        emailLink.textContent = '-';
                        emailLink.href = '#';
                    }

                    const phoneLink = document.getElementById('detailPhone');
                    if (user.phone) {
                        phoneLink.textContent = user.phone;
                        phoneLink.href = 'https://wa.me/' + user.phone.replace(/^0/, '62');
                        phoneLink.setAttribute('target', '_blank');
                        phoneLink.setAttribute('rel', 'noopener noreferrer');
                    } else {
                        phoneLink.textContent = '-';
                        phoneLink.href = '#';
                    }

                    document.getElementById('userDetailModal').classList.remove('hidden');
                }

                function closeUserModal() {
                    document.getElementById('userDetailModal').classList.add('hidden');
                }

                function handleOutsideClick(event) {
                    if (event.target.id === 'userDetailModal') {
                        closeUserModal();
                    }
                }
            </script>
        @endpush

    </x-dashboard.sidebar>
</x-app-layout>
