<div class="w-full overflow-x-auto">
    <div class="min-w-full inline-block align-middle">
        <div class="overflow-hidden shadow sm:rounded-lg" id="outlet-list">
            <table
                class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs sm:text-sm text-center text-gray-600 dark:text-gray-300">
                <thead
                    class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-700 dark:text-gray-400 whitespace-nowrap">
                    <tr>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="no">No</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="name">Name</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="site_code">Site Code</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="since">Since</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="location">Location</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="status">Status</th>
                        <th class="px-4 py-2 md:px-6 md:py-3">Actions</th>
                    </tr>
                </thead>
                @include('partials.outlets-tbody', ['stores' => $stores])
            </table>
        </div>
    </div>
</div>
<p id="last-updated-display" class="text-xs text-gray-400 mt-2">
    Last updated at: {{ $stores->max('updated_at') }}
</p>
@if ($showPagination)
    <x-per-page-selector :items="$stores" route="outlets.index" :perPage="$perPage" :showPagination="true" />
@endif
@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/list.js/2.3.1/list.min.js"></script>
    <script>
        const outletList = new List('outlet-list', {
            valueNames: ['no', 'name', 'site_code', 'since', 'location', 'status']
        });

        console.log("Polling Outlet loaded...");
        let lastKnownUpdate = "{{ $stores->max('updated_at') }}";

        function refreshOutletTbody() {
            const search = '{{ request('search') }}';
            const perPage = '{{ request('per_page', 5) }}';
            const tbody = document.querySelector('tbody.list');

            fetch(`/outlets/tbody?search=${search}&per_page=${perPage}`)
                .then(res => res.text())
                .then(html => {
                    if (tbody) {
                        tbody.innerHTML = html;
                        outletList.reIndex();
                    }
                })
                .catch(err => console.error("Failed to refresh outlets:", err));
        }

        async function checkOutletUpdate() {
            try {
                const res = await fetch('/outlets/last-updated');
                const data = await res.json();

                if (data.last_updated !== lastKnownUpdate) {
                    lastKnownUpdate = data.last_updated;
                    refreshOutletTbody();
                }

                const updatedEl = document.getElementById('last-updated-display');
                if (updatedEl) {
                    updatedEl.textContent = 'Last updated at: ' + data.last_updated;
                }
            } catch (err) {
                console.error('Error checking outlet updates:', err);
            }
        }

        setInterval(checkOutletUpdate, 10000);
    </script>
@endpush
