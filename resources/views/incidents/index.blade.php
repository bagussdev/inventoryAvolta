<x-app-layout>
    <x-dashboard.sidebar>
        <x-alert-information></x-alert-information>

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mt-5 sm:mt-10 w-full gap-4">
            <h2 class="font-bold text-xl sm:text-2xl whitespace-nowrap truncate">
                Incident List
            </h2>

            <div class="flex flex-wrap gap-3 items-center">
                <form id="filterForm" method="GET" action="{{ route('incidents.index') }}" class="flex gap-2 items-center"
                    onsubmit="showFullScreenLoader();">
                    <x-date-filter-dropdown :action="route('incidents.index')" :startDate="request('start_date')" :endDate="request('end_date')" formId="filterForm" />
                    @can('exportexcel')
                        <a href="{{ route('incidents.export', array_merge(request()->query(), ['export' => 1])) }}"
                            class="text-xs sm:text-sm px-3 py-1.5 sm:px-4 sm:py-2 text-white bg-gray-600 hover:bg-gray-700 focus:ring-4 focus:ring-gray-300 font-medium rounded-md focus:outline-none dark:bg-gray-500 dark:hover:bg-gray-600 dark:focus:ring-gray-700 text-center">
                            Excel
                        </a>
                    @endcan
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..."
                        class="text-xs sm:text-sm px-2 py-1.5 sm:px-3 sm:py-2 rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-purple-500 w-full sm:w-44" />
                    <input type="hidden" name="per_page" value="{{ $perPage ?? 5 }}">
                    <button type="submit"
                        class="text-xs sm:text-sm px-3 py-1.5 sm:px-3 sm:py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition">
                        Search
                    </button>
                </form>
                @can('incident.create')
                    <a href="{{ route('incidents.create') }}" onclick="showFullScreenLoader();"
                        class="text-xs sm:text-sm px-3 py-1.5 sm:px-4 sm:py-2 text-white bg-purple-600 hover:bg-purple-700 focus:ring-4 focus:ring-purple-300 font-medium rounded-md focus:outline-none dark:bg-purple-500 dark:hover:bg-purple-600 dark:focus:ring-purple-700 text-center">
                        Create New
                    </a>
                @endcan
            </div>
        </div>

        <hr class="h-[3px] my-8 bg-gray-200 border-0 dark:bg-gray-700 w-full">

        <x-table.incident :incidents="$incidents" :perPage="$perPage" :showPagination="true" />
        @include('components.modal-user')
        @push('scripts')
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
