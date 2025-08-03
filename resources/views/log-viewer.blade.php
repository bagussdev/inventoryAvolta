<x-app-layout>
    <x-dashboard.sidebar>
        <div class="mt-5 mb-4 flex flex-col sm:flex-row justify-between gap-4 sm:items-center">
            <h2 class="text-xl sm:text-2xl font-bold">Log Error Viewer</h2>

            <form method="GET" action="{{ route('log.viewer') }}" class="flex flex-wrap gap-2">
                <input type="text" name="search" value="{{ request('search') }}"
                    class="px-3 py-2 border border-gray-300 rounded-md text-sm dark:bg-gray-700 dark:text-white"
                    placeholder="Search logs...">
                <button type="submit"
                    class="bg-purple-600 text-white px-4 py-2 rounded-md text-sm hover:bg-purple-700">
                    Search
                </button>
            </form>
        </div>

        @if ($logs->count())
            <div class="overflow-auto bg-white dark:bg-gray-800 rounded-lg shadow">
                <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-200">
                    <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-100">
                        <tr>
                            <th class="px-4 py-2 w-14">No</th>
                            <th class="px-4 py-2 w-60">Date</th>
                            <th class="px-4 py-2">Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($logs as $i => $line)
                            <tr
                                class="border-t border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-2">
                                    {{ $logs instanceof \Illuminate\Pagination\LengthAwarePaginator ? $logs->firstItem() + $i : $i + 1 }}
                                </td>
                                <td class="px-4 py-2">
                                    {{ \Illuminate\Support\Str::of($line)->match('/\[(.*?)\]/') }}
                                </td>
                                <td class="px-4 py-2 whitespace-pre-line">
                                    {{ $line }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="bg-white dark:bg-gray-800 text-center text-gray-500 p-6 rounded-md shadow mt-4">
                No log entries found.
            </div>
        @endif

        <div class="mt-4">
            <x-per-page-selector :route="'log.viewer'" :items="$logs" :perPage="$perPage" :search="$search"
                :showPagination="$showPagination" />
        </div>
    </x-dashboard.sidebar>
</x-app-layout>
