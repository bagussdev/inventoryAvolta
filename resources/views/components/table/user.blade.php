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
                <tbody class="list whitespace-nowrap">
                    @forelse ($users as $user)
                        <tr
                            class="border-b dark:border-gray-700 {{ $loop->odd ? 'bg-white dark:bg-gray-900' : 'bg-gray-50 dark:bg-gray-800' }}">
                            <td class="px-4 py-2 md:px-6 md:py-3 no">{{ $loop->iteration }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 name">{{ $user->name }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 email">{{ $user->email }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 phone">{{ $user->no_telfon }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 role">{{ $user->role->name }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 department">{{ $user->department->name }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 department">
                                {{ $user->location->name ?? 'Back Office' }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 status">
                                @if ($user->status == 'Y')
                                    <span
                                        class="px-3 py-1 text-xs font-medium rounded-md bg-green-100 text-green-800">Active</span>
                                @else
                                    <span
                                        class="px-3 py-1 text-xs font-medium rounded-md bg-red-100 text-red-700">Non-Active</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 md:px-6 md:py-3">
                                <div class="flex flex-col sm:flex-row items-center justify-center gap-1">
                                    @if ($user->status == 'Y')
                                        <form method="GET" action="{{ route('users.deactive', $user->id) }}"
                                            onsubmit="return confirmAndLoad('Are you sure to deactivate this user?')">
                                            <x-buttons.action-button text="Non-Active" color="red" />
                                        </form>
                                    @else
                                        <form method="GET" action="{{ route('users.active', $user->id) }}"
                                            onsubmit="return confirmAndLoad('Activate this user?')">
                                            <x-buttons.action-button text="Active" color="purple" />
                                        </form>
                                    @endif
                                    <a href="{{ route('users.edit', $user->id) }}" onclick="showFullScreenLoader();">
                                        <x-buttons.action-button text="Edit" color="blue" />
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-xs">No user data found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>


        </div>
    </div>
</div>
@if ($showPagination)
    <div class="mt-6 px-4 pb-3 flex flex-col sm:flex-row justify-between items-center gap-4">
        <div>
            {{ $users->appends(['per_page' => $perPage, 'search' => $search])->links() }}
        </div>
        <div class="flex items-center gap-4 flex-wrap justify-end">
            <form method="GET" action="{{ route('users.index') }}">
                <input type="hidden" name="search" value="{{ $search }}">
                <div class="flex items-center gap-1">
                    <label for="per_page" class="text-sm text-gray-600 dark:text-gray-300">Show</label>
                    <select name="per_page" id="per_page" onchange="this.form.submit()"
                        class="text-sm w-16 px-2 py-1 rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-purple-500">
                        <option value="5" {{ $perPage == 5 ? 'selected' : '' }}>5</option>
                        <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20</option>
                    </select>
                    <span class="text-sm text-gray-600 dark:text-gray-400">per page</span>
                </div>
            </form>
        </div>
    </div>
@endif
@push('scripts')
    <script>
        const userList = new List('user-list', {
            valueNames: ['no', 'name', 'email', 'phone', 'role', 'department', 'status']
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
    </script>
@endpush
