<nav class="fixed top-0 z-50 w-full bg-white border-b border-gray-200 dark:bg-gray-800 dark:border-gray-700">
    <div class="px-3 py-3 lg:px-5 lg:pl-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center justify-start rtl:justify-end">
                <button data-drawer-target="logo-sidebar" data-drawer-toggle="logo-sidebar" aria-controls="logo-sidebar"
                    type="button"
                    class="inline-flex items-center p-2 text-sm text-gray-500 rounded-lg sm:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600">
                    <span class="sr-only">Open sidebar</span>
                    <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg">
                        <path clip-rule="evenodd" fill-rule="evenodd"
                            d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z">
                        </path>
                    </svg>
                </button>
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2 ms-2 md:me-24"
                    onclick="showFullScreenLoader();">
                    <img src="{{ asset('assets/logo.png') }}" alt="Logo"
                        class="h-8 w-8 sm:h-10 sm:w-10 object-contain">
                    <span
                        class="hidden sm:block self-center text-base font-bold sm:text-2xl whitespace-nowrap text-purple-600">
                        {{ config('app.name') }}
                    </span>
                </a>
            </div>

            {{-- User Info --}}
            <div class="flex items-center">
                <div class="flex items-center gap-4 ms-3">
                    @include('components.notif')

                    <div>
                        <p class="font-bold capitalize text-sm sm:text-base">{{ Auth::user()->name }}</p>
                        <p class="text-xs sm:text-sm text-slate-400">
                            @if (Auth::user()->role_id === 5)
                                {{ Auth::user()->role->name }} / {{ Auth::user()->location->site_code }}
                            @else
                                {{ Auth::user()->role->name }} / {{ Auth::user()->department->name }}
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

<aside id="logo-sidebar"
    class="fixed top-0 left-0 z-40 w-64 h-screen pt-20 transition-transform -translate-x-full bg-white border-r border-gray-200 sm:translate-x-0 dark:bg-gray-800 dark:border-gray-700"
    aria-label="Sidebar">
    <div class="h-full px-3 pb-4 overflow-y-auto bg-white dark:bg-gray-800">
        <ul class="space-y-2 font-medium">

            {{-- Dashboard --}}
            <li>
                <a href="{{ route('dashboard') }}" onclick="showFullScreenLoader();"
                    class="flex items-center p-2 rounded-lg group
                    {{ request()->routeIs('dashboard') ? 'bg-purple-100 text-purple-700' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    {!! view('components.icons.dashboard-icon')->render() !!}
                    <span class="ms-3">Dashboard</span>
                </a>
            </li>

            {{-- Inventory --}}
            @php
                $isInventoryOpen = request()->routeIs('items.*', 'equipments.*', 'spareparts.*');
            @endphp

            @if (auth()->user()->role_id === 5)
                <li>
                    <a href="{{ route('equipments.index') }}" onclick="showFullScreenLoader();"
                        class="flex items-center p-2 rounded-lg group
                            {{ request()->routeIs('equipments.*') ? 'bg-purple-100 text-purple-700' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        {!! view('components.icons.inventory-icon')->render() !!}
                        <span class="ms-3">Equipments</span>
                    </a>
                </li>
            @else
                <li x-data="{ open: {{ $isInventoryOpen ? 'true' : 'false' }} }">
                    <button @click="open = !open"
                        class="{{ $isInventoryOpen ? 'bg-purple-100 text-purple-700' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700' }} flex items-center w-full p-2 rounded-lg group justify-between">
                        <span class="flex items-center">
                            {!! view('components.icons.inventory-icon')->render() !!}
                            <span class="ms-3">Inventory</span>
                        </span>
                        <svg :class="{ 'rotate-180': open }" class="w-4 h-4 transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <ul x-show="open" class="ml-8 space-y-1 mt-2" x-cloak>
                        @can('inventoryitemsmenu')
                            <li><a href="{{ route('items.index') }}" onclick="showFullScreenLoader();"
                                    class="{{ request()->routeIs('items.index', 'items.create', 'items.edit', 'items.show') ? 'bg-purple-100 text-purple-700' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700' }} block py-1 px-2 rounded text-sm">Inventory
                                    Items</a></li>
                        @endcan
                        @can('equipmentsmenu')
                            <li><a href="{{ route('equipments.index') }}" onclick="showFullScreenLoader();"
                                    class="{{ request()->routeIs('equipments.*') ? 'bg-purple-100 text-purple-700' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700' }} block py-1 px-2 rounded text-sm">Equipments</a>
                            </li>
                        @endcan
                        @can('sparepartsmenu')
                            <li><a href="{{ route('spareparts.index') }}" onclick="showFullScreenLoader();"
                                    class="{{ request()->routeIs('spareparts.*') ? 'bg-purple-100 text-purple-700' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700' }} block py-1 px-2 rounded text-sm">Spareparts</a>
                            </li>
                        @endcan
                        @can('inventoryitems.delete')
                            <li><a href="{{ route('items.deleted') }}" onclick="showFullScreenLoader();"
                                    class="{{ request()->routeIs('items.deleted') ? 'bg-purple-100 text-purple-700' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700' }} block py-1 px-2 rounded text-sm">Deleted
                                    Items</a></li>
                        @endcan
                    </ul>
                </li>
            @endif


            {{-- Transactions --}}

            @can('historytransactionsmenu')
                @php
                    $isTransactionsOpen = request()->routeIs('transactions.*', 'sparepartused.*');
                @endphp
                <li x-data="{ open: {{ $isTransactionsOpen ? 'true' : 'false' }} }">
                    <button @click="open = !open"
                        class=" {{ request()->routeIs('transactions.*', 'sparepartused.*') ? 'bg-purple-100 text-purple-700' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700' }} flex items-center w-full p-2 rounded-lg group justify-between text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700">
                        <span class="flex items-center">
                            {!! view('components.icons.transaction-icon')->render() !!}
                            <span class="ms-3">Transactions</span>
                        </span>
                        <svg :class="{ 'rotate-180': open }" class="w-4 h-4 transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <ul x-show="open" class="ml-8 space-y-1 mt-2" x-cloak>
                        {{-- @can('view transactions') --}}
                        <li>
                            <a href="{{ route('transactions.index') }}" onclick="showFullScreenLoader();"
                                class=" {{ request()->routeIs('transactions.*') ? 'bg-purple-100 text-purple-700' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700' }} block py-1 px-2 rounded text-sm hover:bg-gray-100">
                                History Transactions</a>
                        </li>
                        {{-- @endcan --}}
                        <li><a href="{{ route('sparepartused.index') }}" onclick="showFullScreenLoader();"
                                class="{{ request()->routeIs('sparepartused.*') ? 'bg-purple-100 text-purple-700' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700' }} block py-1 px-2 rounded text-sm hover:bg-gray-100">Used
                                Spareparts</a></li>
                    </ul>
                </li>
            @endcan
            {{-- Maintenance --}}
            @can('maintenancemenu')
                @php
                    $isTransactionsOpen = request()->routeIs('maintenances.*');
                @endphp
                <li x-data="{ open: {{ $isTransactionsOpen ? 'true' : 'false' }} }">
                    <button @click="open = !open"
                        class="{{ request()->routeIs('maintenances.*') ? 'bg-purple-100 text-purple-700' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700' }} flex items-center w-full p-2 rounded-lg group justify-between text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700">
                        <span class="flex items-center">
                            {!! view('components.icons.maintenance-icon')->render() !!}
                            <span class="ms-3">Maintenance</span>
                        </span>
                        <svg :class="{ 'rotate-180': open }" class="w-4 h-4 transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <ul x-show="open" class="ml-8 space-y-1 mt-2" x-cloak>
                        <li><a href="{{ route('maintenances.index') }}" onclick="showFullScreenLoader();"
                                class="{{ request()->routeIs('maintenances.*') && !request()->routeIs('maintenances.completed') && !request()->routeIs('maintenances.showCompletedDetail') ? 'bg-purple-100 text-purple-700' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700' }} block py-1 px-2 rounded text-sm hover:bg-gray-100">Schedule
                                Maintenance</a></li>
                        <li><a href="{{ route('maintenances.completed') }}" onclick="showFullScreenLoader();"
                                class="{{ request()->routeIs('maintenances.completed') || request()->routeIs('maintenances.showCompletedDetail') ? 'bg-purple-100 text-purple-700' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700' }} block py-1 px-2 rounded text-sm hover:bg-gray-100">Completed
                                Maintenance</a></li>
                    </ul>
                </li>
            @endcan

            {{-- Incident & Request --}}
            @php
                $isIncidentsOpen = request()->routeIs('incidents.*', 'requests.*');
            @endphp
            <li x-data="{ open: {{ $isIncidentsOpen ? 'true' : 'false' }} }">
                <button @click="open = !open"
                    class="{{ request()->routeIs('incidents.*', 'requests.*') ? 'bg-purple-100 text-purple-700' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700' }} flex items-center w-full p-2 rounded-lg group justify-between text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700">
                    <span class="flex items-center">
                        {!! view('components.icons.rnq-icon')->render() !!}
                        <span class="ms-3">Incident</span>
                    </span>
                    <svg :class="{ 'rotate-180': open }" class="w-4 h-4 transition-transform" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <ul x-show="open" class="ml-8 space-y-1 mt-2" x-cloak>
                    <li><a href="{{ route('incidents.index') }}" onclick="showFullScreenLoader();"
                            class="{{ request()->routeIs('incidents.*') && !request()->routeIs('incidents.completed') && !request()->routeIs('incidents.showCompletedDetail') ? 'bg-purple-100 text-purple-700' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700' }} block py-1 px-2 rounded text-sm hover:bg-gray-100">Incident</a>
                    </li>
                    <li><a href="{{ route('incidents.completed') }}" onclick="showFullScreenLoader();"
                            onclick="showFullScreenLoader();"
                            class="{{ request()->routeIs('incidents.completed') || request()->routeIs('incidents.showCompletedDetail') ? 'bg-purple-100 text-purple-700' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700' }} block py-1 px-2 rounded text-sm hover:bg-gray-100">Completed
                            Incident</a></li>
                    @can('requestmenu')
                        <li><a href="{{ route('requests.index') }}" onclick="showFullScreenLoader();"
                                onclick="showFullScreenLoader();"
                                class="{{ request()->routeIs('requests.*') && !request()->routeIs('requests.completed') && !request()->routeIs('requests.showCompletedDetail') ? 'bg-purple-100 text-purple-700' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700' }} block py-1 px-2 rounded text-sm hover:bg-gray-100">Request</a>
                        </li>
                        <li><a href="{{ route('requests.completed') }}" onclick="showFullScreenLoader();"
                                onclick="showFullScreenLoader();"
                                class="{{ request()->routeIs('requests.completed') || request()->routeIs('requests.showCompletedDetail') ? 'bg-purple-100 text-purple-700' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700' }} block py-1 px-2 rounded text-sm hover:bg-gray-100">Completed
                                Request</a></li>
                    @endcan
                </ul>
            </li>
            @can('outletlistmenu')
                {{-- Outlet List --}}
                <li>
                    <a href="{{ route('outlets.index') }}" onclick="showFullScreenLoader();"
                        class="flex items-center p-2 rounded-lg group {{ request()->routeIs('outlets.*') ? 'bg-purple-100 text-purple-700' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700' }} text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700">
                        {!! view('components.icons.outlet-icon')->render() !!}
                        <span class="ms-3">Outlet List</span>
                    </a>
                </li>
            @endcan

            {{-- Management User --}}
            @can('managementusermenu')
                <li>
                    <a href="{{ route('users.index') }}" onclick="showFullScreenLoader();"
                        class="flex items-center p-2 rounded-lg group
                {{ request()->routeIs('users.*') ? 'bg-purple-100 text-purple-700' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        {!! view('components.icons.user-icon')->render() !!}
                        <span class="ms-3">Management User</span>
                    </a>
                </li>
            @endcan
            {{-- Permission Settings --}}
            @php
                $isMasterSettingOpen = request()->routeIs(
                    'permissions.*',
                    'notification-preferences.*',
                    'notifications.*',
                    'log.*',
                );
            @endphp

            @canany(['permissionsettingsmenu', 'notifpermission', 'lognotif', 'isMaster'])
                <li x-data="{ open: {{ $isMasterSettingOpen ? 'true' : 'false' }} }">
                    <button @click="open = !open"
                        class="{{ $isMasterSettingOpen ? 'bg-purple-100 text-purple-700' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700' }} flex items-center w-full p-2 rounded-lg group justify-between">
                        <span class="flex items-center">
                            {!! view('components.icons.permission-icon')->render() !!}
                            <span class="ms-3">Master Settings</span>
                        </span>
                        <svg :class="{ 'rotate-180': open }" class="w-4 h-4 transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <ul x-show="open" class="ml-8 space-y-1 mt-2" x-cloak>
                        @can('permissionsettingsmenu')
                            <li><a href="{{ route('permissions.index') }}" onclick="showFullScreenLoader();"
                                    class="{{ request()->routeIs('permissions.*') ? 'bg-purple-100 text-purple-700' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700' }} block py-1 px-2 rounded text-sm">
                                    Permissions Settings</a>
                            </li>
                        @endcan
                        @can('notifpermission')
                            <li><a href="{{ route('notification-preferences.index') }}" onclick="showFullScreenLoader();"
                                    class="{{ request()->routeIs('notification-preferences.*') ? 'bg-purple-100 text-purple-700' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700' }} block py-1 px-2 rounded text-sm">
                                    Notif Permissions</a>
                            </li>
                        @endcan
                        @can('lognotif')
                            <li><a href="{{ route('notifications.index') }}" onclick="showFullScreenLoader();"
                                    class="{{ request()->routeIs('notifications.*') ? 'bg-purple-100 text-purple-700' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700' }} block py-1 px-2 rounded text-sm">
                                    Log Notifications</a>
                            </li>
                        @endcan
                        @can('isMaster')
                            <li><a href="{{ route('log.viewer') }}" onclick="showFullScreenLoader();"
                                    class="{{ request()->routeIs('log.*') ? 'bg-purple-100 text-purple-700' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700' }} block py-1 px-2 rounded text-sm">
                                    Log Error</a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcanany

            {{-- @can('permissionsettingsmenu')
                <li>
                    <a href="{{ route('permissions.index') }}" onclick="showFullScreenLoader();"
                        class="flex items-center p-2 rounded-lg group 
                    {{ request()->routeIs('permissions.*') ? 'bg-purple-100 text-purple-700' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700' }} text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700">
                        <?xml version="1.0" ?>
                        {!! view('components.icons.permission-icon')->render() !!}
                        <span class="ms-3">Permissions Settings</span>
                    </a>
                </li>
            @endcan
            @can('notifpermission')
                <li>
                    <a href="{{ route('notification-preferences.index') }}" onclick="showFullScreenLoader();"
                        class="flex items-center p-2 rounded-lg group 
                    {{ request()->routeIs('notification-preferences.*') ? 'bg-purple-100 text-purple-700' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700' }} text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700">
                        <?xml version="1.0" ?>
                        {!! view('components.icons.permission-icon')->render() !!}
                        <span class="ms-3">Notif Permissions</span>
                    </a>
                </li>
            @endcan
            @can('lognotif')
                <li>
                    <a href="{{ route('notifications.index') }}" onclick="showFullScreenLoader();"
                        class="flex items-center p-2 rounded-lg group 
                    {{ request()->routeIs('notifications.*') ? 'bg-purple-100 text-purple-700' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700' }} text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700">
                        <?xml version="1.0" ?>
                        {!! view('components.icons.permission-icon')->render() !!}
                        <span class="ms-3">Log Notifications</span>
                    </a>
                </li>
            @endcan
            @can('isMaster')
                <li>
                    <a href="{{ route('log.viewer') }}" onclick="showFullScreenLoader();"
                        class="flex items-center p-2 rounded-lg group 
                        {{ request()->routeIs('log.*') ? 'bg-purple-100 text-purple-700' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        {!! view('components.icons.permission-icon')->render() !!}
                        <span class="ms-3">Log Error</span>
                    </a>
                </li>
            @endcan --}}
            <li>
                <a href="{{ asset('assets/User-Guide-and-UAT-Support-Portal-Avolta-v1.0.pdf') }}" target="_blank"
                    class="flex items-center p-2 rounded-lg group text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('profile.edit') ? 'bg-purple-100 text-purple-700' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700' }}">

                    {{-- SVG icon buku --}}
                    {!! view('components.icons.user-guide-icon')->render() !!}

                    <span class="ms-3">User Guide</span>
                </a>
            </li>
            {{-- Settings --}}
            <li>
                <a href="{{ route('profile.edit') }}" onclick="showFullScreenLoader();"
                    class="flex items-center p-2 rounded-lg group text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('profile.edit') ? 'bg-purple-100 text-purple-700' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    {!! view('components.icons.settings-icon')->render() !!}
                    <span class="ms-3">Settings</span>
                </a>
            </li>
            {{-- Logout --}}
            <li>
                <form method="POST" action="{{ route('logout') }}"
                    onsubmit="return confirmAndLoad('Are you sure to logout?')">
                    @csrf
                    <button type="submit"
                        class="w-full text-left flex items-center p-2 rounded-lg text-red-500 hover:bg-red-100">
                        {!! view('components.icons.logout-icon')->render() !!}
                        <span class="ms-3">Logout</span>
                    </button>
                </form>
            </li>

        </ul>
    </div>
</aside>

{{-- Content --}}
<main class="p-4 sm:ml-64 mt-14">
    {{ $slot }}
</main>
</div>
