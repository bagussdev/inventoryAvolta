<div class="w-full overflow-x-auto">
    <div class="min-w-full inline-block align-middle">
        <div class="overflow-hidden shadow sm:rounded-lg" id="sparepart-list">
            <table
                class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs sm:text-sm md:text-base lg:text-[15px] text-center text-gray-600 dark:text-gray-300">
                <thead
                    class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-700 dark:text-gray-400 whitespace-nowrap">
                    <tr>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="no">No</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="name">Name</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="model">Model</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="brand">Brand</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="supplier">Supplier</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="stock">Stock</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="status">Status</th>
                        <th class="px-4 py-2 md:px-6 md:py-3">Actions</th>
                    </tr>
                </thead>
                @include('partials.spareparts-tbody', [
                    'spareparts' => $spareparts,
                    'search' => $search,
                    'perPage' => $perPage,
                ])
            </table>
        </div>
    </div>
</div>

<p id="last-updated-display" class="text-xs text-gray-400 mt-2">
    Last updated at: {{ $spareparts->max('updated_at') }}
</p>
@if ($showPagination)
    <x-per-page-selector :items="$spareparts" route="spareparts.index" :perPage="$perPage" :search="$search"
        :showPagination="true" />
@endif
@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/list.js/2.3.1/list.min.js"></script>
    <script>
        const sparepartList = new List('sparepart-list', {
            valueNames: ['no', 'name', 'supplier', 'stock', 'status']
        });

        console.log("Polling Spareparts loaded...");

        let lastKnownUpdate = "{{ $spareparts->max('updated_at') }}";

        function refreshSparepartsTbody() {
            const search = '{{ request('search') }}';
            const perPage = '{{ request('per_page', 5) }}';
            const tbody = document.querySelector('tbody.list');

            fetch(`/spareparts/tbody?search=${search}&per_page=${perPage}`)
                .then(res => res.text())
                .then(html => {
                    if (tbody) {
                        tbody.innerHTML = html;
                        sparepartList.reIndex();
                    }
                })
                .catch(err => {
                    console.error("Failed to fetch updated spareparts:", err);
                });
        }

        async function checkSparepartsUpdate() {
            try {
                const res = await fetch('/spareparts/last-updated');
                const data = await res.json();

                if (data.last_updated !== lastKnownUpdate) {
                    lastKnownUpdate = data.last_updated;
                    refreshSparepartsTbody();
                }
                const updatedEl = document.getElementById('last-updated-display');
                if (updatedEl) {
                    updatedEl.textContent = 'Last updated at: ' + data.last_updated;
                }
            } catch (err) {
                console.error('Error checking spareparts updates:', err);
            }
        }

        setInterval(checkSparepartsUpdate, 10000); // 10 detik polling
    </script>
@endpush
