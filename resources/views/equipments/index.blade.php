<x-app-layout>
    <x-dashboard.sidebar>
        <x-alert-information></x-alert-information>

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mt-5 sm:mt-10 w-full gap-4">
            <h2 class="font-bold text-xl sm:text-2xl whitespace-nowrap truncate">
                Equipment List
            </h2>

            <form method="GET" action="{{ route('equipments.index') }}" class="flex gap-2"
                onsubmit="showFullScreenLoader();">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..."
                    class="text-xs sm:text-sm px-2 py-1.5 sm:px-3 sm:py-2 rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-purple-500 w-36 sm:w-44" />
                <button type="submit"
                    class="text-xs sm:text-sm px-3 py-1.5 sm:px-3 sm:py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition">
                    Search
                </button>
            </form>
        </div>

        <hr class="h-[3px] my-8 bg-gray-200 border-0 dark:bg-gray-700 w-full">

        <x-table.equipments :equipments="$equipments" :perPage="$perPage" :search="$search" :showPagination="true" />

    </x-dashboard.sidebar>
</x-app-layout>
