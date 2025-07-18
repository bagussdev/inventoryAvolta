<x-app-layout>
    <x-dashboard.sidebar>
        <x-alert-information></x-alert-information>

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mt-10 w-full gap-4">
            <h2 class="font-bold text-xl sm:text-2xl whitespace-nowrap truncate">
                Request List
            </h2>

            <div class="flex flex-wrap gap-3 items-center">
                <form id="filterForm" method="GET" action="{{ route('requests.index') }}" class="flex gap-2 items-center"
                    onsubmit="showFullScreenLoader();">
                    <x-date-filter-dropdown :action="route('requests.index')" :startDate="request('start_date')" :endDate="request('end_date')" formId="filterForm" />

                    <a href="{{ route('requests.export', array_merge(request()->query(), ['export' => 1])) }}"
                        class="text-xs sm:text-sm px-3 py-1.5 sm:px-4 sm:py-2 text-white bg-gray-600 hover:bg-gray-700 focus:ring-4 focus:ring-gray-300 font-medium rounded-md focus:outline-none dark:bg-gray-500 dark:hover:bg-gray-600 dark:focus:ring-gray-700 text-center">
                        Excel
                    </a>

                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..."
                        class="text-xs sm:text-sm px-2 py-1.5 sm:px-3 sm:py-2 rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-purple-500 w-full sm:w-44" />
                    <input type="hidden" name="per_page" value="{{ $perPage ?? 5 }}">
                    <button type="submit"
                        class="text-xs sm:text-sm px-3 py-1.5 sm:px-3 sm:py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition">
                        Search
                    </button>
                </form>
                @can('request.create')
                    <a href="{{ route('requests.create') }}" onclick="showFullScreenLoader();"
                        class="text-xs sm:text-sm px-3 py-1.5 sm:px-4 sm:py-2 text-white bg-purple-600 hover:bg-purple-700 focus:ring-4 focus:ring-purple-300 font-medium rounded-md focus:outline-none dark:bg-purple-500 dark:hover:bg-purple-600 dark:focus:ring-purple-700 text-center">
                        Create New Request
                    </a>
                @endcan
            </div>
        </div>

        <hr class="h-[3px] my-8 bg-gray-200 border-0 dark:bg-gray-700 w-full">

        <x-table.request :requests="$requests" :perPage="$perPage ?? 5" :showPagination="true" />


    </x-dashboard.sidebar>
</x-app-layout>
