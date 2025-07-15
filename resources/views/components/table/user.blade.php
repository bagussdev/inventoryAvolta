<div class="w-full overflow-x-auto">
    <div class="min-w-full inline-block align-middle">
        <div class="overflow-hidden shadow sm:rounded-lg" id="user-list">
            <table
                class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs sm:text-sm text-center text-gray-600 dark:text-gray-300">
                <thead
                    class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-700 dark:text-gray-400 whitespace-nowrap">
                    <tr>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="no">No</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="name">Nama</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="email">Email</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="phone">Phone</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="role">Role</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="department">Department</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="department">Location</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="status">Status</th>
                        <th class="px-4 py-2 md:px-6 md:py-3">Actions</th>
                    </tr>
                </thead>
                @include('partials.users-tbody', ['users' => $users])
            </table>
        </div>
    </div>
</div>
<p id="last-updated-display" class="text-xs text-gray-400 mt-2">
    Last updated at: {{ $users->max('updated_at') }}
</p>
@if ($showPagination)
    <x-per-page-selector :items="$users" route="users.index" :perPage="$perPage" :search="$search" :showPagination="true" />
@endif
@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/list.js/2.3.1/list.min.js"></script>
    <script>
        const userList = new List('user-list', {
            valueNames: ['no', 'name', 'email', 'phone', 'role', 'department', 'location', 'status']
        });

        document.querySelectorAll('#user-list .sort').forEach(button => {
            button.addEventListener('click', function() {
                const field = this.dataset.sort;
                const isAsc = this.classList.toggle('asc');
                this.classList.toggle('desc', !isAsc);
                userList.sort(field, {
                    order: isAsc ? 'asc' : 'desc'
                });
            });
        });

        console.log("Polling User loaded...");
        let lastKnownUpdate = "{{ $users->max('updated_at') }}";

        function refreshUserTbody() {
            const search = '{{ request('search') }}';
            const perPage = '{{ request('per_page', 5) }}';
            const tbody = document.querySelector('tbody.list');

            fetch(`/users/tbody?search=${search}&per_page=${perPage}`)
                .then(res => res.text())
                .then(html => {
                    if (tbody) {
                        tbody.innerHTML = html;
                        userList.reIndex();
                    }
                })
                .catch(err => console.error("Failed to refresh users:", err));
        }

        async function checkUserUpdate() {
            try {
                const res = await fetch('/users/last-updated');
                const data = await res.json();

                if (data.last_updated !== lastKnownUpdate) {
                    lastKnownUpdate = data.last_updated;
                    refreshUserTbody();
                }

                const updatedEl = document.getElementById('last-updated-display');
                if (updatedEl) {
                    updatedEl.textContent = 'Last updated at: ' + data.last_updated;
                }
            } catch (err) {
                console.error('Error checking user updates:', err);
            }
        }

        setInterval(checkUserUpdate, 10000);
    </script>
@endpush
