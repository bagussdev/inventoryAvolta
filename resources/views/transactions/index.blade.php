<x-app-layout>
    <x-dashboard.sidebar>
        <x-alert-information></x-alert-information>

        @include('transactions.upload-modal')

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mt-10 w-full gap-4">
            <h2 class="font-bold text-xl sm:text-2xl whitespace-nowrap truncate">
                Transaction List
            </h2>

            <div class="flex flex-wrap gap-3 items-center">
                {{-- PERUBAHAN: Menambahkan id pada form dan mengganti HTML filter dengan komponen --}}
                <form id="filterForm" method="GET" action="{{ route('transactions.index') }}"
                    class="flex gap-2 items-center" onsubmit="showFullScreenLoader();">

                    {{-- PENGGUNAAN KOMPONEN DATE FILTER DROPDOWN DI SINI --}}
                    <x-date-filter-dropdown :action="route('maintenances.index')" :startDate="request('start_date')" :endDate="request('end_date')" formId="filterForm" />

                    <a href="{{ route('transactions.export', array_merge(request()->query(), ['export' => 1])) }}"
                        class="text-xs sm:text-sm px-3 py-1.5 sm:px-4 sm:py-2 text-white bg-gray-600 hover:bg-gray-700 focus:ring-4 focus:ring-gray-300 font-medium rounded-md focus:outline-none dark:bg-gray-500 dark:hover:bg-gray-600 dark:focus:ring-gray-700 text-center">
                        Excel
                    </a>

                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..."
                        class="text-xs sm:text-sm px-2 py-1.5 sm:px-3 sm:py-2 rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-purple-500 w-36 sm:w-44" />
                    <input type="hidden" name="per_page" value="{{ $perPage ?? 5 }}">
                    <button type="submit" onsubmit="showFullScreenLoader();"
                        class="text-xs sm:text-sm px-3 py-1.5 sm:px-3 sm:py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition">
                        Search
                    </button>
                </form>

                @can('historytransactions.create')
                    <a href="{{ route('transactions.create') }}" onclick="showFullScreenLoader();"
                        class="text-xs sm:text-sm px-3 py-1.5 sm:px-4 sm:py-2 text-white bg-purple-600 hover:bg-purple-700 focus:ring-4 focus:ring-purple-300 font-medium rounded-md focus:outline-none dark:bg-purple-500 dark:hover:bg-purple-600 dark:focus:ring-purple-700 text-center">
                        New Transaction
                    </a>
                @endcan
                @can('historytransactions.uploadExcel')
                    <button onclick="document.getElementById('uploadModal').classList.remove('hidden')"
                        class="text-xs sm:text-sm px-3 py-1.5 sm:px-4 sm:py-2 text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-300 font-medium rounded-md focus:outline-none dark:bg-green-500 dark:hover:bg-green-600 dark:focus:ring-green-700 text-center">
                        Upload Excel
                    </button>
                @endcan
            </div>
        </div>

        <hr class="h-[3px] my-8 bg-gray-200 border-0 dark:bg-gray-700 w-full">

        <x-table.transaction :transactions="$transactions" :perPage="$perPage" :showPagination="true" />

    </x-dashboard.sidebar>
</x-app-layout>
