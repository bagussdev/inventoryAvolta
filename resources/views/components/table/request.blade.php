<div class="w-full overflow-x-auto">
    <div class="min-w-full inline-block align-middle">
        <div class="overflow-hidden shadow sm:rounded-lg" id="request-list">
            <table
                class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs sm:text-sm text-center text-gray-600 dark:text-gray-300">
                <thead
                    class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-700 dark:text-gray-400 whitespace-nowrap">
                    <tr>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="no">No</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="id">Request ID</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="report">Reported By</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="department">Report To</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="item">Item Req</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="qty">Qty</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="location">Location</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="date">Start</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="status">Status</th>
                        <th class="px-2 py-2 md:px-3 md:py-2">Action</th>
                    </tr>
                </thead>
                @include('partials.requests-tbody', [
                    'requests' => $requests,
                    'perPage' => $perPage,
                ])
            </table>

        </div>
    </div>
</div>
<p id="last-updated-display" class="text-xs text-gray-400 mt-2">
    Last updated at: {{ $requests->max('updated_at') }}
</p>
@if ($showPagination)
    <x-per-page-selector :items="$requests" route="requests.index" :perPage="$perPage" :showPagination="true" />
@endif
@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/list.js/2.3.1/list.min.js"></script>
    <script>
        const requestList = new List('request-list', {
            valueNames: ['no', 'id', 'report', 'department', 'item', 'qty', 'location', 'date', 'status']
        });

        console.log("Polling Request loaded...");

        let lastKnownUpdate = "{{ $requests->max('updated_at') }}";

        function refreshRequestTbody() {
            const search = '{{ request('search') }}';
            const perPage = '{{ request('per_page', 5) }}';
            const startDate = '{{ request('start_date') }}';
            const endDate = '{{ request('end_date') }}';
            const tbody = document.querySelector('tbody.list');

            fetch(`/requests/tbody?search=${search}&per_page=${perPage}&start_date=${startDate}&end_date=${endDate}`)
                .then(res => res.text())
                .then(html => {
                    if (tbody) {
                        tbody.innerHTML = html;
                        requestList.reIndex();
                    }
                })
                .catch(err => console.error("Failed to refresh requests:", err));
        }

        async function checkRequestUpdate() {
            try {
                const res = await fetch('/requests/last-updated');
                const data = await res.json();

                if (data.last_updated !== lastKnownUpdate) {
                    lastKnownUpdate = data.last_updated;
                    refreshRequestTbody();
                }
                const updatedEl = document.getElementById('last-updated-display');
                if (updatedEl) {
                    updatedEl.textContent = 'Last updated at: ' + data.last_updated;
                }
            } catch (err) {
                console.error('Error checking request updates:', err);
            }
        }

        setInterval(checkRequestUpdate, 10000);
    </script>
@endpush
