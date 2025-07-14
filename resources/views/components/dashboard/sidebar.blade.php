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
                        Inventory Avolta
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
                            @if (Auth::user()->store_location != null)
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
                    <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.143 4H4.857A.857.857 0 0 0 4 4.857v4.286c0 .473.384.857.857.857h4.286A.857.857 0 0 0 10 9.143V4.857A.857.857 0 0 0 9.143 4Zm10 0h-4.286a.857.857 0 0 0-.857.857v4.286c0 .473.384.857.857.857h4.286A.857.857 0 0 0 20 9.143V4.857A.857.857 0 0 0 19.143 4Zm-10 10H4.857a.857.857 0 0 0-.857.857v4.286c0 .473.384.857.857.857h4.286a.857.857 0 0 0 .857-.857v-4.286A.857.857 0 0 0 9.143 14Zm10 0h-4.286a.857.857 0 0 0-.857.857v4.286c0 .473.384.857.857.857h4.286a.857.857 0 0 0 .857-.857v-4.286a.857.857 0 0 0-.857-.857Z" />
                    </svg>
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
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-package-icon lucide-package mr-2">
                            <path
                                d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z" />
                            <path d="M12 22V12" />
                            <polyline points="3.29 7 12 12 20.71 7" />
                            <path d="m7.5 4.27 9 5.15" />
                        </svg>
                        <span class="ms-1">Equipments</span>
                    </a>
                </li>
            @else
                <li x-data="{ open: {{ $isInventoryOpen ? 'true' : 'false' }} }">
                    <button @click="open = !open"
                        class="{{ $isInventoryOpen ? 'bg-purple-100 text-purple-700' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700' }} flex items-center w-full p-2 rounded-lg group justify-between">
                        <span class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-package-icon lucide-package">
                                <path
                                    d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z" />
                                <path d="M12 22V12" />
                                <polyline points="3.29 7 12 12 20.71 7" />
                                <path d="m7.5 4.27 9 5.15" />
                            </svg>
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
                        @can('inventoryitemsmenu')
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
                            <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24"
                                height="24" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M13.6 16.733c.234.269.548.456.895.534a1.4 1.4 0 0 0 1.75-.762c.172-.615-.446-1.287-1.242-1.481-.796-.194-1.41-.861-1.241-1.481a1.4 1.4 0 0 1 1.75-.762c.343.077.654.26.888.524m-1.358 4.017v.617m0-5.939v.725M4 15v4m3-6v6M6 8.5 10.5 5 14 7.5 18 4m0 0h-3.5M18 4v3m2 8a5 5 0 1 1-10 0 5 5 0 0 1 10 0Z" />
                            </svg>
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
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Layer_1"
                                viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve">
                                <g>
                                    <path
                                        d="M380.957,281.962c-3.906-3.904-10.236-3.904-14.142,0c-5.848,5.849-15.365,5.848-21.213,0.001   c-5.849-5.85-5.849-15.366,0-21.214c11.044-11.046,11.561-28.297,2.196-39.974l42.352-42.352   c32.325,10.933,67.938,2.771,92.37-21.671c22.646-22.635,31.578-55.944,23.313-86.929c-1.988-7.455-11.308-9.919-16.731-4.495   l-20.722,20.711c-11.693,11.695-30.723,11.695-42.418,0c-11.695-11.694-11.695-30.724,0.002-42.42l20.709-20.72   c5.451-5.453,2.922-14.754-4.495-16.731c-30.979-8.266-64.293,0.667-86.926,23.31c-24.446,24.436-32.61,60.045-21.674,92.373   l-84.645,84.655L131.816,89.39l4.284-17.134c0.948-3.794-0.401-7.792-3.455-10.234L61.935,5.453   C57.956,2.27,52.22,2.588,48.617,6.19L6.19,48.616c-3.603,3.604-3.921,9.339-0.738,13.318l56.569,70.711   c2.443,3.054,6.443,4.403,10.234,3.454l17.135-4.283l117.115,117.115l-84.654,84.645C53.96,310.607-12.348,372.77,6.168,442.177   c1.987,7.45,11.302,9.921,16.731,4.495l20.722-20.711c11.724-11.725,30.696-11.723,42.417,0   c11.695,11.694,11.695,30.724-0.002,42.42l-20.71,20.72c-5.453,5.456-2.918,14.754,4.495,16.731   c69.292,18.488,131.578-47.747,108.601-115.683l42.353-42.353c11.787,9.475,29.029,8.747,39.973-2.195   c5.848-5.849,15.365-5.848,21.213-0.001c5.849,5.85,5.849,15.366,0,21.214c-3.905,3.905-3.905,10.237,0,14.142l113.137,113.137   c19.497,19.495,51.214,19.495,70.711,0l28.284-28.284c19.495-19.495,19.495-51.216,0-70.711L380.957,281.962z M352.291,131.42   c2.881-2.881,3.726-7.221,2.136-10.972c-19.546-46.126,14.102-96.704,63.729-97.309l-6.337,6.34   c-19.542,19.542-19.538,51.165,0,70.703c19.493,19.491,51.21,19.494,70.701,0.001l6.342-6.338   c-0.604,49.447-51.011,83.347-97.31,63.728c-3.75-1.591-8.091-0.745-10.973,2.136l-50.39,50.389   c-9.497-1.859-19.645,0.857-27.014,8.224l-21.213,21.213l-18.888-18.888L352.291,131.42z M157.572,391.553   c19.548,46.126-14.102,96.703-63.728,97.309l6.337-6.34c19.493-19.493,19.493-51.21,0-70.703   c-19.542-19.542-51.166-19.537-70.701-0.001l-6.341,6.338c0.602-49.501,51.072-83.322,97.309-63.729   c3.752,1.591,8.092,0.744,10.973-2.136l89.226-89.217l18.888,18.888l-21.213,21.213c-7.344,7.346-10.085,17.542-8.225,27.016   l-50.389,50.388C156.828,383.46,155.983,387.801,157.572,391.553z M479.951,451.667l-28.284,28.284   c-11.725,11.727-30.704,11.724-42.426,0L302.147,372.856c7.161-13.261,5.147-30.206-6.043-41.397   c-13.647-13.647-35.851-13.645-49.497,0c-5.078,5.078-13.054,3.123-15.959-2.459c-0.106-0.246-0.217-0.491-0.343-0.729   c-1.469-3.515-0.876-7.917,2.161-10.954l84.853-84.853c1.89-1.891,4.401-2.932,7.07-2.932c8.84,0,13.376,10.769,7.071,17.073   c-13.646,13.646-13.646,35.851,0.001,49.498c11.19,11.191,28.138,13.204,41.396,6.042l107.095,107.095   C491.678,420.966,491.675,439.945,479.951,451.667z" />
                                    <path
                                        d="M352.673,366.815c-3.906-3.904-10.236-3.904-14.143,0c-3.905,3.905-3.905,10.237,0,14.142l84.853,84.853   c3.906,3.904,10.235,3.905,14.143,0c3.905-3.905,3.905-10.237,0-14.143L352.673,366.815z" />
                                    <path
                                        d="M380.957,338.53c-3.906-3.904-10.236-3.904-14.142,0c-3.905,3.905-3.905,10.237,0,14.143l84.852,84.852   c3.906,3.904,10.235,3.906,14.143,0c3.905-3.905,3.905-10.237,0-14.143L380.957,338.53z" />
                                </g>
                            </svg>
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
                        <svg viewBox="0 0 200 200" xml:space="preserve" width="24" height="24"
                            xmlns="http://www.w3.org/2000/svg">
                            <g fill="#000000" class="fill-5e889e">
                                <path
                                    d="M157.009 117.575c-.543-2.392-1.868-4.648-3.954-6.473-2.103-1.813-4.665-2.907-7.323-3.307l-42.74-8.271 12.364 8.205-19.521 61.195-10.993-45.457-10.996 45.457L54.33 107.73l12.362-8.205-42.743 8.271c-2.656.399-5.221 1.494-7.321 3.307-2.088 1.824-3.413 4.081-3.956 6.473L0 173.235l23.901 8.298c7.942 8.247 30.154 14.316 56.797 14.893h8.283c26.646-.576 48.858-6.646 56.796-14.893l23.905-8.298-12.673-55.66z">
                                </path>
                                <path
                                    d="m84.841 105.735-9.854 3.53 9.854 14.204 9.852-14.204zM121.418 50.925c0-25.262-10.394-45.06-36.577-45.06-26.186 0-36.579 19.798-36.579 45.06-2.738 1.43-4.535 4.782-1.848 11.967 1.358 3.609 3.75 6.632 5.777 8.214 7.461 18.578 22.137 32.921 32.65 32.921 10.508 0 25.186-14.344 32.646-32.921 2.031-1.582 4.42-4.604 5.776-8.214 2.693-7.185.895-10.538-1.845-11.967zM147.349 40.985h-19.424c.125 1.104.231 2.222.315 3.365h19.108v-3.365zM186.626 29.385h-61.013c.328 1.095.614 2.224.886 3.367h60.127v-3.367z">
                                </path>
                                <path
                                    d="M197.701 5.875a7.841 7.841 0 0 0-5.556-2.301h-86.454a36.867 36.867 0 0 1 10.159 7.856h76.295v54.99H171.12l-15.915 15.917V66.42h-25.759c-1.237 2.901-3.004 5.645-5.041 7.858h22.943v21.606c0 .878.521 1.714 1.384 2.073a2.24 2.24 0 0 0 2.447-.487l23.195-23.193h17.771a7.837 7.837 0 0 0 5.556-2.303A7.828 7.828 0 0 0 200 66.419V11.43a7.832 7.832 0 0 0-2.299-5.555z">
                                </path>
                                <path d="M186.626 17.79h-66.063a45.35 45.35 0 0 1 1.844 3.367h64.219V17.79z"></path>
                            </g>
                        </svg>
                        <span class="ms-3">Incident & Request</span>
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
                    <li><a href="{{ route('requests.index') }}" onclick="showFullScreenLoader();"
                            onclick="showFullScreenLoader();"
                            class="{{ request()->routeIs('requests.*') && !request()->routeIs('requests.completed') && !request()->routeIs('incidents.showCompletedDetail') ? 'bg-purple-100 text-purple-700' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700' }} block py-1 px-2 rounded text-sm hover:bg-gray-100">Request</a>
                    </li>
                    <li><a href="{{ route('requests.completed') }}" onclick="showFullScreenLoader();"
                            onclick="showFullScreenLoader();"
                            class="{{ request()->routeIs('requests.completed') || request()->routeIs('requests.showCompletedDetail') ? 'bg-purple-100 text-purple-700' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700' }} block py-1 px-2 rounded text-sm hover:bg-gray-100">Completed
                            Request</a></li>
                </ul>
            </li>
            @can('outletlistmenu')
                {{-- Outlet List --}}
                <li>
                    <a href="{{ route('outlets.index') }}" onclick="showFullScreenLoader();"
                        class="flex items-center p-2 rounded-lg group {{ request()->routeIs('outlets.*') ? 'bg-purple-100 text-purple-700' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700' }} text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-store-icon lucide-store">
                            <path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7" />
                            <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8" />
                            <path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4" />
                            <path d="M2 7h20" />
                            <path
                                d="M22 7v3a2 2 0 0 1-2 2a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 16 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 12 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 8 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 4 12a2 2 0 0 1-2-2V7" />
                        </svg>
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
                        <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24"
                            height="24" fill="currentColor" viewBox="0 0 24 24">
                            <path fill-rule="evenodd"
                                d="M12 6a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7Zm-1.5 8a4 4 0 0 0-4 4 2 2 0 0 0 2 2h7a2 2 0 0 0 2-2 4 4 0 0 0-4-4h-3Zm6.82-3.096a5.51 5.51 0 0 0-2.797-6.293 3.5 3.5 0 1 1 2.796 6.292ZM19.5 18h.5a2 2 0 0 0 2-2 4 4 0 0 0-4-4h-1.1a5.503 5.503 0 0 1-.471.762A5.998 5.998 0 0 1 19.5 18ZM4 7.5a3.5 3.5 0 0 1 5.477-2.889 5.5 5.5 0 0 0-2.796 6.293A3.501 3.501 0 0 1 4 7.5ZM7.1 12H6a4 4 0 0 0-4 4 2 2 0 0 0 2 2h.5a5.998 5.998 0 0 1 3.071-5.238A5.505 5.505 0 0 1 7.1 12Z"
                                clip-rule="evenodd" />
                        </svg>
                        <span class="ms-3">Management User</span>
                    </a>
                </li>
            @endcan
            {{-- Permission Settings --}}
            @can('permissionsettingsmenu')
                <li>
                    <a href="{{ route('permissions.index') }}" onclick="showFullScreenLoader();"
                        class="flex items-center p-2 rounded-lg group 
                    {{ request()->routeIs('permissions.*') ? 'bg-purple-100 text-purple-700' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700' }} text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700">
                        <?xml version="1.0" ?><svg xmlns="http://www.w3.org/2000/svg"
                            xmlns:xlink="http://www.w3.org/1999/xlink" width="24" height="24" version="1.1"
                            viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve">
                            <g
                                id="_x31_1_x2C__Maintenance_x2C__Metal_Gear_x2C__Electronics_x2C__Repair_x2C__Settings_x2C__Configuration_x2C__Cogwheel_x2C__Tools_and_Utensils">
                                <g id="XMLID_144_">
                                    <g>
                                        <path
                                            d="M375.492,110.997c-2.52,0-4.755-1.588-5.564-3.952c-1.608-4.704-3.532-9.353-5.719-13.819     c-1.088-2.222-0.623-4.911,1.158-6.692c10.124-10.125,10.122-26.51,0-36.631l-12.866-12.867c-10.1-10.099-26.532-10.098-36.632,0     c-1.781,1.781-4.47,2.246-6.692,1.158c-4.459-2.184-9.108-4.108-13.817-5.719c-2.365-0.809-3.953-3.045-3.953-5.574     c0-14.283-11.62-25.902-25.902-25.902h-18.202c-14.283,0-25.902,11.62-25.902,25.913c0,2.519-1.588,4.755-3.953,5.564     c-4.709,1.61-9.357,3.534-13.817,5.719c-2.222,1.088-4.912,0.623-6.693-1.158c-10.099-10.098-26.532-10.099-36.632,0     l-12.865,12.866c-10.124,10.124-10.123,26.509-0.001,36.632c1.781,1.781,2.247,4.47,1.158,6.693     c-2.185,4.461-4.109,9.11-5.718,13.817c-0.809,2.364-3.045,3.953-5.574,3.953c-14.283,0-25.902,11.62-25.902,25.902v18.202     c0,14.283,11.62,25.903,25.913,25.903c2.52,0,4.755,1.588,5.564,3.953c1.61,4.708,3.534,9.356,5.719,13.817     c1.088,2.222,0.623,4.912-1.158,6.693c-10.124,10.124-10.123,26.509-0.001,36.631l12.866,12.866     c10.124,10.123,26.51,10.122,36.632,0c1.781-1.78,4.472-2.247,6.692-1.159c4.459,2.185,9.108,4.109,13.818,5.72     c2.364,0.809,3.953,3.044,3.953,5.574c0,14.283,11.62,25.903,25.902,25.903h18.202c14.283,0,25.902-11.62,25.902-25.913     c0-2.519,1.588-4.755,3.953-5.564c4.711-1.611,9.359-3.535,13.816-5.719c2.225-1.089,4.913-0.623,6.693,1.158     c10.124,10.123,26.509,10.122,36.632,0l12.866-12.866c10.123-10.124,10.122-26.509,0-36.631     c-1.781-1.781-2.247-4.471-1.158-6.693c2.187-4.464,4.111-9.113,5.719-13.817c0.809-2.364,3.044-3.953,5.574-3.953     c14.283,0,25.902-11.62,25.902-25.903v-18.202C401.405,122.617,389.785,110.997,375.492,110.997z M312.973,202.569     c-31.191,31.191-81.944,31.192-113.137,0c-31.192-31.192-31.192-81.945,0-113.138c31.264-31.264,81.87-31.268,113.137,0     C344.165,120.624,344.165,171.377,312.973,202.569z" />
                                    </g>
                                    <path
                                        d="M505.731,297.375c-10.956-19.203-35.361-25.874-54.561-14.92l-111.318,63.507c-4.576-19.994-22.495-34.961-43.857-34.961    h-108.43c-31.011-25.137-74.363-26.413-106.57-4.839v-25.161c0-5.523-4.477-10-10-10h-60c-5.523,0-10,4.477-10,10v220    c0,5.523,4.477,10,10,10h60c5.523,0,10-4.477,10-10v-10.03c164.087-0.002,156.873,0.008,157.264-0.009    c6.279-0.273,12.513-2.079,18.031-5.225l234.518-133.788C510.018,340.997,516.691,316.554,505.731,297.375z M60.995,491.001h-40    v-200h40V491.001z M480.9,334.574L246.383,468.363c-2.747,1.566-5.705,2.443-8.795,2.607H80.995V332.016    c26.03-26.609,68.359-28.182,96.291-3.52c1.827,1.613,4.181,2.504,6.619,2.504h112.09c13.785,0,25,11.215,25,25    c0,13.785-11.215,25-25,25h-121.58c-5.523,0-10,4.477-10,10c0,5.523,4.477,10,10,10h121.58c20.104,0,37.167-13.253,42.922-31.48    l122.164-69.694c9.595-5.473,21.795-2.15,27.282,7.466C493.847,316.891,490.49,329.107,480.9,334.574z" />
                                </g>
                            </g>
                            <g id="Layer_1" />
                        </svg>
                        <span class="ms-3">Permissions Settings</span>
                    </a>
                </li>
            @endcan
            @can('notifpermission')
                <li>
                    <a href="{{ route('notification-preferences.index') }}" onclick="showFullScreenLoader();"
                        class="flex items-center p-2 rounded-lg group 
                    {{ request()->routeIs('notification-preferences.*') ? 'bg-purple-100 text-purple-700' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700' }} text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700">
                        <?xml version="1.0" ?><svg xmlns="http://www.w3.org/2000/svg"
                            xmlns:xlink="http://www.w3.org/1999/xlink" width="24" height="24" version="1.1"
                            viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve">
                            <g
                                id="_x31_1_x2C__Maintenance_x2C__Metal_Gear_x2C__Electronics_x2C__Repair_x2C__Settings_x2C__Configuration_x2C__Cogwheel_x2C__Tools_and_Utensils">
                                <g id="XMLID_144_">
                                    <g>
                                        <path
                                            d="M375.492,110.997c-2.52,0-4.755-1.588-5.564-3.952c-1.608-4.704-3.532-9.353-5.719-13.819     c-1.088-2.222-0.623-4.911,1.158-6.692c10.124-10.125,10.122-26.51,0-36.631l-12.866-12.867c-10.1-10.099-26.532-10.098-36.632,0     c-1.781,1.781-4.47,2.246-6.692,1.158c-4.459-2.184-9.108-4.108-13.817-5.719c-2.365-0.809-3.953-3.045-3.953-5.574     c0-14.283-11.62-25.902-25.902-25.902h-18.202c-14.283,0-25.902,11.62-25.902,25.913c0,2.519-1.588,4.755-3.953,5.564     c-4.709,1.61-9.357,3.534-13.817,5.719c-2.222,1.088-4.912,0.623-6.693-1.158c-10.099-10.098-26.532-10.099-36.632,0     l-12.865,12.866c-10.124,10.124-10.123,26.509-0.001,36.632c1.781,1.781,2.247,4.47,1.158,6.693     c-2.185,4.461-4.109,9.11-5.718,13.817c-0.809,2.364-3.045,3.953-5.574,3.953c-14.283,0-25.902,11.62-25.902,25.902v18.202     c0,14.283,11.62,25.903,25.913,25.903c2.52,0,4.755,1.588,5.564,3.953c1.61,4.708,3.534,9.356,5.719,13.817     c1.088,2.222,0.623,4.912-1.158,6.693c-10.124,10.124-10.123,26.509-0.001,36.631l12.866,12.866     c10.124,10.123,26.51,10.122,36.632,0c1.781-1.78,4.472-2.247,6.692-1.159c4.459,2.185,9.108,4.109,13.818,5.72     c2.364,0.809,3.953,3.044,3.953,5.574c0,14.283,11.62,25.903,25.902,25.903h18.202c14.283,0,25.902-11.62,25.902-25.913     c0-2.519,1.588-4.755,3.953-5.564c4.711-1.611,9.359-3.535,13.816-5.719c2.225-1.089,4.913-0.623,6.693,1.158     c10.124,10.123,26.509,10.122,36.632,0l12.866-12.866c10.123-10.124,10.122-26.509,0-36.631     c-1.781-1.781-2.247-4.471-1.158-6.693c2.187-4.464,4.111-9.113,5.719-13.817c0.809-2.364,3.044-3.953,5.574-3.953     c14.283,0,25.902-11.62,25.902-25.903v-18.202C401.405,122.617,389.785,110.997,375.492,110.997z M312.973,202.569     c-31.191,31.191-81.944,31.192-113.137,0c-31.192-31.192-31.192-81.945,0-113.138c31.264-31.264,81.87-31.268,113.137,0     C344.165,120.624,344.165,171.377,312.973,202.569z" />
                                    </g>
                                    <path
                                        d="M505.731,297.375c-10.956-19.203-35.361-25.874-54.561-14.92l-111.318,63.507c-4.576-19.994-22.495-34.961-43.857-34.961    h-108.43c-31.011-25.137-74.363-26.413-106.57-4.839v-25.161c0-5.523-4.477-10-10-10h-60c-5.523,0-10,4.477-10,10v220    c0,5.523,4.477,10,10,10h60c5.523,0,10-4.477,10-10v-10.03c164.087-0.002,156.873,0.008,157.264-0.009    c6.279-0.273,12.513-2.079,18.031-5.225l234.518-133.788C510.018,340.997,516.691,316.554,505.731,297.375z M60.995,491.001h-40    v-200h40V491.001z M480.9,334.574L246.383,468.363c-2.747,1.566-5.705,2.443-8.795,2.607H80.995V332.016    c26.03-26.609,68.359-28.182,96.291-3.52c1.827,1.613,4.181,2.504,6.619,2.504h112.09c13.785,0,25,11.215,25,25    c0,13.785-11.215,25-25,25h-121.58c-5.523,0-10,4.477-10,10c0,5.523,4.477,10,10,10h121.58c20.104,0,37.167-13.253,42.922-31.48    l122.164-69.694c9.595-5.473,21.795-2.15,27.282,7.466C493.847,316.891,490.49,329.107,480.9,334.574z" />
                                </g>
                            </g>
                            <g id="Layer_1" />
                        </svg>
                        <span class="ms-3">Notif Permissions</span>
                    </a>
                </li>
            @endcan
            @can('lognotif')
                <li>
                    <a href="{{ route('notifications.index') }}" onclick="showFullScreenLoader();"
                        class="flex items-center p-2 rounded-lg group 
                    {{ request()->routeIs('notifications.*') ? 'bg-purple-100 text-purple-700' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700' }} text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700">
                        <?xml version="1.0" ?><svg xmlns="http://www.w3.org/2000/svg"
                            xmlns:xlink="http://www.w3.org/1999/xlink" width="24" height="24" version="1.1"
                            viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve">
                            <g
                                id="_x31_1_x2C__Maintenance_x2C__Metal_Gear_x2C__Electronics_x2C__Repair_x2C__Settings_x2C__Configuration_x2C__Cogwheel_x2C__Tools_and_Utensils">
                                <g id="XMLID_144_">
                                    <g>
                                        <path
                                            d="M375.492,110.997c-2.52,0-4.755-1.588-5.564-3.952c-1.608-4.704-3.532-9.353-5.719-13.819     c-1.088-2.222-0.623-4.911,1.158-6.692c10.124-10.125,10.122-26.51,0-36.631l-12.866-12.867c-10.1-10.099-26.532-10.098-36.632,0     c-1.781,1.781-4.47,2.246-6.692,1.158c-4.459-2.184-9.108-4.108-13.817-5.719c-2.365-0.809-3.953-3.045-3.953-5.574     c0-14.283-11.62-25.902-25.902-25.902h-18.202c-14.283,0-25.902,11.62-25.902,25.913c0,2.519-1.588,4.755-3.953,5.564     c-4.709,1.61-9.357,3.534-13.817,5.719c-2.222,1.088-4.912,0.623-6.693-1.158c-10.099-10.098-26.532-10.099-36.632,0     l-12.865,12.866c-10.124,10.124-10.123,26.509-0.001,36.632c1.781,1.781,2.247,4.47,1.158,6.693     c-2.185,4.461-4.109,9.11-5.718,13.817c-0.809,2.364-3.045,3.953-5.574,3.953c-14.283,0-25.902,11.62-25.902,25.902v18.202     c0,14.283,11.62,25.903,25.913,25.903c2.52,0,4.755,1.588,5.564,3.953c1.61,4.708,3.534,9.356,5.719,13.817     c1.088,2.222,0.623,4.912-1.158,6.693c-10.124,10.124-10.123,26.509-0.001,36.631l12.866,12.866     c10.124,10.123,26.51,10.122,36.632,0c1.781-1.78,4.472-2.247,6.692-1.159c4.459,2.185,9.108,4.109,13.818,5.72     c2.364,0.809,3.953,3.044,3.953,5.574c0,14.283,11.62,25.903,25.902,25.903h18.202c14.283,0,25.902-11.62,25.902-25.913     c0-2.519,1.588-4.755,3.953-5.564c4.711-1.611,9.359-3.535,13.816-5.719c2.225-1.089,4.913-0.623,6.693,1.158     c10.124,10.123,26.509,10.122,36.632,0l12.866-12.866c10.123-10.124,10.122-26.509,0-36.631     c-1.781-1.781-2.247-4.471-1.158-6.693c2.187-4.464,4.111-9.113,5.719-13.817c0.809-2.364,3.044-3.953,5.574-3.953     c14.283,0,25.902-11.62,25.902-25.903v-18.202C401.405,122.617,389.785,110.997,375.492,110.997z M312.973,202.569     c-31.191,31.191-81.944,31.192-113.137,0c-31.192-31.192-31.192-81.945,0-113.138c31.264-31.264,81.87-31.268,113.137,0     C344.165,120.624,344.165,171.377,312.973,202.569z" />
                                    </g>
                                    <path
                                        d="M505.731,297.375c-10.956-19.203-35.361-25.874-54.561-14.92l-111.318,63.507c-4.576-19.994-22.495-34.961-43.857-34.961    h-108.43c-31.011-25.137-74.363-26.413-106.57-4.839v-25.161c0-5.523-4.477-10-10-10h-60c-5.523,0-10,4.477-10,10v220    c0,5.523,4.477,10,10,10h60c5.523,0,10-4.477,10-10v-10.03c164.087-0.002,156.873,0.008,157.264-0.009    c6.279-0.273,12.513-2.079,18.031-5.225l234.518-133.788C510.018,340.997,516.691,316.554,505.731,297.375z M60.995,491.001h-40    v-200h40V491.001z M480.9,334.574L246.383,468.363c-2.747,1.566-5.705,2.443-8.795,2.607H80.995V332.016    c26.03-26.609,68.359-28.182,96.291-3.52c1.827,1.613,4.181,2.504,6.619,2.504h112.09c13.785,0,25,11.215,25,25    c0,13.785-11.215,25-25,25h-121.58c-5.523,0-10,4.477-10,10c0,5.523,4.477,10,10,10h121.58c20.104,0,37.167-13.253,42.922-31.48    l122.164-69.694c9.595-5.473,21.795-2.15,27.282,7.466C493.847,316.891,490.49,329.107,480.9,334.574z" />
                                </g>
                            </g>
                            <g id="Layer_1" />
                        </svg>
                        <span class="ms-3">Log Notifications</span>
                    </a>
                </li>
            @endcan
            {{-- Settings --}}
            <li>
                <a href="{{ route('profile.edit') }}" onclick="showFullScreenLoader();"
                    class="flex items-center p-2 rounded-lg group text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('profile.edit') ? 'bg-purple-100 text-purple-700' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24"
                        height="24" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M10.83 5a3.001 3.001 0 0 0-5.66 0H4a1 1 0 1 0 0 2h1.17a3.001 3.001 0 0 0 5.66 0H20a1 1 0 1 0 0-2h-9.17ZM4 11h9.17a3.001 3.001 0 0 1 5.66 0H20a1 1 0 1 1 0 2h-1.17a3.001 3.001 0 0 1-5.66 0H4a1 1 0 1 1 0-2Zm1.17 6H4a1 1 0 1 0 0 2h1.17a3.001 3.001 0 0 0 5.66 0H20a1 1 0 1 0 0-2h-9.17a3.001 3.001 0 0 0-5.66 0Z" />
                    </svg>
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
                        <svg class="w-6 h-6 text-red-500 dark:text-white" aria-hidden="true"
                            xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                            viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="2" d="M18 18V6h-5v12h5Zm0 0h2M4 18h2.5m3.5-5.5V12M6 6l7-2v16l-7-2V6Z" />
                        </svg>
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
