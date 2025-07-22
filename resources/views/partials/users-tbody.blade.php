<tbody class="list whitespace-nowrap">
    <tr id="loading-indicator" class="hidden">
        <td colspan="8" class="text-center py-4 text-sm text-gray-500">
            <svg aria-hidden="true" role="status" class="inline w-5 h-5 mr-2 text-gray-200 animate-spin fill-purple-600"
                viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                    fill="currentColor" />
                <path
                    d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.544 70.6331 15.2552C75.2735 17.9665 79.3347 21.5619 82.5849 25.841C84.9175 28.9125 86.7992 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                    fill="currentFill" />
            </svg>
            Loading data...
        </td>
    </tr>
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
                    <span class="px-3 py-1 text-xs font-medium rounded-md bg-green-100 text-green-800">Active</span>
                @else
                    <span class="px-3 py-1 text-xs font-medium rounded-md bg-red-100 text-red-700">Non-Active</span>
                @endif
            </td>
            <td class="px-4 py-2 md:px-6 md:py-3">
                <div class="flex flex-row items-center justify-center gap-1">
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
                    <form action="{{ route('users.destroy', $user->id) }}" method="POST"
                        onsubmit="return confirm('Are you sure to delete user?')">
                        @csrf
                        @method('DELETE')
                        <x-buttons.action-button text="Delete" color="red" />
                    </form>
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
