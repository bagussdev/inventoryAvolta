<div class="w-full overflow-x-auto">
    <div class="min-w-full inline-block align-middle">
        <div class="overflow-hidden shadow sm:rounded-lg" id="equipments-list">
            <table
                class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs sm:text-sm text-center text-gray-600 dark:text-gray-300">
                <thead
                    class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-700 dark:text-gray-400 whitespace-nowrap">
                    <tr>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="no">No</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="name">Name</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="sn">S/N</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="sn">Alias</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="qty">Qty</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="location">Location</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="status">Status</th>
                        @if (!Auth::user()->role_id === 5)
                            <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="transaction">
                                Transaction ID</th>
                        @endif
                        <th class="px-4 py-2 md:px-6 md:py-3">Actions</th>
                    </tr>
                </thead>
                @include('partials.equipments-tbody', [
                    'equipments' => $equipments,
                    'search' => $search,
                    'perPage' => $perPage,
                ])
            </table>
        </div>
    </div>
</div>
<p id="last-updated-display" class="text-xs text-gray-400 mt-2">
    Last updated at: {{ $equipments->max('updated_at') }}
</p>

@if ($showPagination)
    <x-per-page-selector :items="$equipments" route="equipments.index" :perPage="$perPage" :search="$search"
        :showPagination="true" />
@endif

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/list.js/2.3.1/list.min.js"></script>
    <script>
        console.log("Polling script loaded...");
        const equipmentList = new List('equipments-list', {
            valueNames: ['no', 'name', 'sn', 'qty', 'location', 'status', 'transaction']
        });

        let lastKnownUpdate = null;

        async function initLastUpdate() {
            try {
                const res = await fetch('/equipments/last-updated');
                const data = await res.json();
                lastKnownUpdate = data.last_updated;
                const display = document.getElementById('last-updated-display');
                if (display) display.innerText = "Last updated at: " + lastKnownUpdate;
            } catch (err) {
                console.error('Failed to get initial update timestamp:', err);
            }
        }

        function refreshEquipmentsTbody() {
            const search = '{{ request('search') }}';
            const perPage = '{{ request('per_page', 5) }}';
            const loadingRow = document.getElementById('loading-indicator');
            const tbody = document.querySelector('#equipments-body');

            if (loadingRow) loadingRow.classList.remove('hidden');

            fetch(`/equipments/tbody?search=${search}&per_page=${perPage}`)
                .then(res => res.text())
                .then(html => {
                    if (tbody) {
                        tbody.innerHTML = html;
                        equipmentList.reIndex();
                    }
                })
                .catch(err => {
                    console.error("Failed to fetch updated equipments:", err);
                })
                .finally(() => {
                    if (loadingRow) loadingRow.classList.add('hidden');
                });
        }

        async function checkForUpdates() {
            try {
                const res = await fetch('/equipments/last-updated');
                const data = await res.json();

                if (data.last_updated !== lastKnownUpdate) {
                    lastKnownUpdate = data.last_updated;
                    const display = document.getElementById('last-updated-display');
                    if (display) display.innerText = "Last updated at: " + lastKnownUpdate;
                    refreshEquipmentsTbody();
                }
                const updatedEl = document.getElementById('last-updated-display');
                if (updatedEl) {
                    updatedEl.textContent = 'Last updated at: ' + data.last_updated;
                }
            } catch (err) {
                console.error('Error checking updates:', err);
            }
        }

        // Jalankan saat pertama kali halaman dibuka
        initLastUpdate();
        setInterval(checkForUpdates, 10000); // Cek perubahan setiap 10 detik
    </script>
@endpush
