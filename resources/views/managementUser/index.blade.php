<x-app-layout>
    <x-dashboard.sidebar>
        <x-alert-information></x-alert-information>

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mt-10 w-full max-w-full gap-4">
            <h2 class="font-bold text-xl sm:text-2xl whitespace-nowrap truncate">
                Management User
            </h2>

            <div class="flex flex-wrap gap-3 items-center">
                {{-- Global Search --}}
                <form method="GET" action="{{ route('users.index') }}" class="flex gap-2 items-center"
                    onsubmit="showFullScreenLoader();">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..."
                        class="text-xs sm:text-sm px-2 py-1.5 sm:px-3 sm:py-2 rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-purple-500 w-36 sm:w-44" />
                    <input type="hidden" name="per_page" value="{{ $perPage ?? 5 }}">

                    <button type="submit" onsubmit="showFullScreenLoader();"
                        class="text-xs sm:text-sm px-3 py-1.5 sm:px-3 sm:py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition">
                        Search
                    </button>
                </form>

                {{-- Add User --}}
                <a href="{{ route('users.create') }}" onclick="showFullScreenLoader();"
                    class="text-xs sm:text-sm px-3 py-1.5 sm:px-4 sm:py-2 text-white bg-purple-600 hover:bg-purple-700 focus:ring-4 focus:ring-purple-300 font-medium rounded-md focus:outline-none dark:bg-purple-500 dark:hover:bg-purple-600 dark:focus:ring-purple-700 text-center">
                    Add User
                </a>
            </div>

        </div>

        <hr class="h-[3px] my-8 bg-gray-200 border-0 dark:bg-gray-700 w-full">

        <div class="relative overflow-x-auto shadow-md sm:rounded-lg mt-5" id="user-list">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400 text-center">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th class="px-6 py-3 cursor-pointer sort" data-sort="no">No</th>
                        <th class="px-6 py-3 cursor-pointer sort" data-sort="name">Nama</th>
                        <th class="px-6 py-3 cursor-pointer sort" data-sort="email">Email</th>
                        <th class="px-6 py-3 cursor-pointer sort" data-sort="phone">Phone Number</th>
                        <th class="px-6 py-3 cursor-pointer sort" data-sort="role">Role</th>
                        <th class="px-6 py-3 cursor-pointer sort" data-sort="department">Department</th>
                        <th class="px-6 py-3 cursor-pointer sort" data-sort="department">Location</th>
                        <th class="px-6 py-3 cursor-pointer sort" data-sort="status">Status</th>
                        <th class="px-6 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="list">
                    @forelse ($users as $user)
                        <tr
                            class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700">
                            <td class="px-6 py-4 no">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4 name">{{ $user->name }}</td>
                            <td class="px-6 py-4 email">{{ $user->email }}</td>
                            <td class="px-6 py-4 phone">{{ $user->no_telfon }}</td>
                            <td class="px-6 py-4 role">{{ $user->role->name }}</td>
                            <td class="px-6 py-4 department">{{ $user->department->name }}</td>
                            <td class="px-6 py-4 department">{{ $user->location->name ?? 'Back Office' }}</td>
                            <td class="px-6 py-4 status">
                                @if ($user->status == 'Y')
                                    <span
                                        class="inline-block px-3 py-1 text-sm font-medium rounded bg-green-100 text-green-800">Active</span>
                                @else
                                    <span
                                        class="inline-block px-3 py-1 text-sm font-medium rounded bg-red-100 text-red-700">Non-Active</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-2 justify-center">
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
                            <td colspan="8" class="text-center py-4">Tidak ada Data User</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-6 px-4 pb-3 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div>
                    {{ $users->appends(['per_page' => $perPage, 'search' => $search])->links() }}
                </div>
                <div class="flex items-center gap-4 flex-wrap justify-end">
                    <form method="GET" action="{{ route('users.index') }}">
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
        </div>

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

    </x-dashboard.sidebar>
</x-app-layout>
