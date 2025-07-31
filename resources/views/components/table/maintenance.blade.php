<div class="w-full overflow-x-auto">
    <div class="min-w-full inline-block align-middle">
        <div class="overflow-hidden shadow sm:rounded-lg" id="maintenance-list">
            <table
                class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs sm:text-sm text-center text-gray-600 dark:text-gray-300">
                <thead
                    class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-700 dark:text-gray-400 whitespace-nowrap">
                    <tr>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="no">No</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="name">Maintenance ID</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="model">Equipment</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="store">Store</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="date">Date</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="freq">Frequency</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="pic">PIC Staff</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="status">Status</th>
                        <th class="px-4 py-2 md:px-6 md:py-3">Actions</th>
                    </tr>
                </thead>
                <x-table.loading-row colspan="8" />
                @include('partials.maintenances-tbody', [
                    'maintenances' => $maintenances,
                    'perPage' => $perPage,
                ])
            </table>
        </div>
    </div>
</div>

<p id="last-updated-display" class="text-xs text-gray-400 mt-2">
    Last updated at: {{ $maintenances->max('updated_at') ?? '-' }}
</p>

@if ($showPagination)
    <x-per-page-selector :items="$maintenances" route="maintenances.index" :perPage="$perPage" :showPagination="true" />
@endif
@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/list.js/2.3.1/list.min.js"></script>
    <script>
        const maintenanceList = new List('maintenance-list', {
            valueNames: ['no', 'name', 'model', 'store', 'date', 'freq', 'status', 'pic']
        });

        console.log("Polling Maintenances loaded...");

        let lastKnownUpdate = "{{ $maintenances->max('updated_at') }}";

        function refreshMaintenanceTbody() {
            const search = '{{ request('search') }}';
            const perPage = '{{ request('per_page', 5) }}';
            const startDate = '{{ request('start_date') }}';
            const endDate = '{{ request('end_date') }}';
            const tbody = document.querySelector('tbody.list');
            const loadingRow = document.getElementById('loading-indicator');

            // Show loading indicator
            loadingRow?.classList.remove('hidden');
            fetch(`/maintenances/tbody?search=${search}&per_page=${perPage}&start_date=${startDate}&end_date=${endDate}`)
                .then(res => res.text())
                .then(html => {
                    if (tbody) {
                        tbody.innerHTML = html;
                        tbody.insertAdjacentHTML('beforeend', `<x-table.loading-row colspan="8" />`);
                        maintenanceList.reIndex();
                        loadingRow?.classList.add('hidden');

                    }
                })
                .catch(err => {
                    console.error("Failed to fetch updated maintenances:", err);
                });
        }

        async function checkMaintenanceUpdate() {
            try {
                const res = await fetch('/maintenances/last-updated');
                const data = await res.json();

                if (data.last_updated !== lastKnownUpdate) {
                    lastKnownUpdate = data.last_updated;
                    refreshMaintenanceTbody();
                }
                const updatedEl = document.getElementById('last-updated-display');
                if (updatedEl) {
                    updatedEl.textContent = 'Last updated at: ' + data.last_updated;
                }
            } catch (err) {
                console.error('Error checking maintenance updates:', err);
            }
        }
        setInterval(checkMaintenanceUpdate, 4000); // 10 detik polling
    </script>
@endpush
