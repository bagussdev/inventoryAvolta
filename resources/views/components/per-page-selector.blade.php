@props(['route', 'perPage' => 5, 'search' => '', 'items', 'showPagination' => true])

@php
    use Illuminate\Pagination\LengthAwarePaginator;
@endphp

@if ($showPagination)
    <div class="mt-6 px-4 pb-3 flex flex-col sm:flex-row justify-between items-center gap-4">

        {{-- Pagination Links (jika paginated) --}}
        @if ($items instanceof LengthAwarePaginator)
            <div>
                {{ $items->appends(['per_page' => $perPage, 'search' => $search])->links() }}
            </div>
        @else
            <div class="text-sm text-gray-500 dark:text-gray-400 italic">
                Showing all {{ $items->count() }} records.
            </div>
        @endif

        {{-- Dropdown per_page --}}
        <div class="flex items-center gap-4 flex-wrap justify-end">
            <form method="GET" action="{{ route($route) }}" onsubmit="showFullScreenLoader();">
                <input type="hidden" name="search" value="{{ $search }}">
                <div class="flex items-center gap-1">
                    <label for="per_page" class="text-sm text-gray-600 dark:text-gray-300">Show</label>
                    <select name="per_page" id="per_page" onchange="this.form.submit()"
                        class="text-sm w-16 px-2 py-1 rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-purple-500">
                        <option value="5" {{ $perPage == 5 ? 'selected' : '' }}>5</option>
                        <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20</option>
                        <option value="all" {{ $perPage == 'all' ? 'selected' : '' }}>All</option>
                    </select>
                    <span class="text-sm text-gray-600 dark:text-gray-400">per page</span>
                </div>
            </form>
        </div>
    </div>
@endif
