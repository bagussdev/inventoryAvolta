<div class="w-full overflow-x-auto">
    <div class="min-w-full inline-block align-middle">
        <div class="overflow-hidden shadow sm:rounded-lg" id="used-spareparts-list">
            <table
                class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs sm:text-sm md:text-base lg:text-[15px] text-center text-gray-600 dark:text-gray-300">
                <thead
                    class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-700 dark:text-gray-400 whitespace-nowrap">
                    <tr>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="no">No</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="date">Date</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="item">Item</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="qty">QTY</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="type">Type</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="reference">Reference</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="pic">PIC</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="note">Note</th>
                    </tr>
                </thead>
                @include('partials.used_spareparts-tbody', [
                    'useds' => $useds,
                    'search' => $search,
                    'perPage' => $perPage,
                ])
            </table>
        </div>
    </div>
</div>
<p id="last-updated-display" class="text-xs text-gray-400 mt-2">
    Last updated at: {{ $useds->max('updated_at') }}
</p>
@if ($showPagination)
    <x-per-page-selector :items="$useds" route="sparepartused.index" :perPage="$perPage" :search="$search"
        :showPagination="true" />
@endif
@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/list.js/2.3.1/list.min.js"></script>
    <script>
        const usedSparepartsList = new List('used-spareparts-list', {
            valueNames: ['no', 'date', 'item', 'qty', 'type', 'reference', 'note', 'pic']
        });

        console.log("Polling Used Spareparts loaded...");

        let lastKnownUpdate = "{{ $useds->max('updated_at') }}";

        function refreshUsedSparepartsTbody() {
            const search = '{{ request('search') }}';
            const perPage = '{{ request('per_page', 5) }}';
            const startDate = '{{ request('start_date') }}';
            const endDate = '{{ request('end_date') }}';
            const tbody = document.querySelector('tbody.list');

            fetch(`/used-spareparts/tbody?search=${search}&per_page=${perPage}&start_date=${startDate}&end_date=${endDate}`)
                .then(res => res.text())
                .then(html => {
                    if (tbody) {
                        tbody.innerHTML = html;
                        usedSparepartsList.reIndex();
                    }
                })
                .catch(err => {
                    console.error("Failed to fetch updated used spareparts:", err);
                });
        }

        async function checkUsedSparepartsUpdate() {
            try {
                const res = await fetch('/used-spareparts/last-updated');
                const data = await res.json();

                if (data.last_updated !== lastKnownUpdate) {
                    lastKnownUpdate = data.last_updated;
                    refreshUsedSparepartsTbody();
                }
                const updatedEl = document.getElementById('last-updated-display');
                if (updatedEl) {
                    updatedEl.textContent = 'Last updated at: ' + data.last_updated;
                }
            } catch (err) {
                console.error('Error checking used spareparts updates:', err);
            }
        }

        setInterval(checkUsedSparepartsUpdate, 10000); // 10 detik polling
    </script>
@endpush
