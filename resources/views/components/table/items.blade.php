<div class="w-full overflow-x-auto">
    <div class="min-w-full inline-block align-middle">
        <div class="overflow-hidden shadow sm:rounded-lg" id="items-list">
            <table
                class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs sm:text-sm text-center text-gray-600 dark:text-gray-300">
                <thead
                    class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-700 dark:text-gray-400 whitespace-nowrap">
                    <tr>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="no">No</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="id">Items ID</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="name">Name</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="type">Type</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="brand">Brand</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="model">Model</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="category">Category</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="category">Department</th>
                        <th class="px-4 py-2 md:px-6 md:py-3">Actions</th>
                    </tr>
                </thead>

                @include('partials.items-tbody', [
                    'items' => $items,
                    'search' => $search,
                    'perPage' => $perPage,
                ])
            </table>
        </div>
    </div>
</div>
<p id="last-updated-display" class="text-xs text-gray-400 mt-2">
    Last updated at: {{ $items->max('updated_at') }}
</p>
@if ($showPagination)
    <x-per-page-selector :items="$items" route="items.index" :perPage="$perPage" :search="$search" :showPagination="true" />
@endif

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/list.js/2.3.1/list.min.js"></script>
    <script>
        console.log("Polling script loaded...");
        const itemList = new List('items-list', {
            valueNames: ['no', 'id', 'name', 'type', 'brand', 'model', 'category']
        });

        let lastKnownUpdate = "{{ $items->max('updated_at') }}";

        function refreshItemsTbody() {
            const search = '{{ request('search') }}';
            const perPage = '{{ request('per_page', 5) }}';
            const loadingRow = document.getElementById('loading-indicator');
            const tbody = document.querySelector('#items-body');

            if (loadingRow) loadingRow.classList.remove('hidden');

            fetch(`/items/tbody?search=${search}&per_page=${perPage}`)
                .then(res => res.text())
                .then(html => {
                    if (tbody) {
                        tbody.innerHTML = html;
                        itemList.reIndex();
                    }
                })
                .catch(err => {
                    console.error("Failed to fetch updated items:", err);
                })
                .finally(() => {
                    if (loadingRow) loadingRow.classList.add('hidden');
                });
        }

        async function checkForItemUpdates() {
            try {
                const res = await fetch('/items/last-updated');
                const data = await res.json();
                if (data.last_updated !== lastKnownUpdate) {
                    lastKnownUpdate = data.last_updated;
                    refreshItemsTbody();
                }
                const updatedEl = document.getElementById('last-updated-display');
                if (updatedEl) {
                    updatedEl.textContent = 'Last updated at: ' + data.last_updated;
                }
            } catch (err) {
                console.error('Error checking item updates:', err);
            }
        }

        setInterval(checkForItemUpdates, 10000); // periksa setiap 10 detik
    </script>
@endpush
