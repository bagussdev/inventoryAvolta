@forelse ($unreadNotifications as $notif)
    <li
        class="px-4 py-3 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer {{ $loop->odd ? 'bg-gray-50 dark:bg-gray-900' : '' }}">
        <a href="{{ route($notif->reference_type . '.show', $notif->reference_id) }}">
            {!! $notif->message !!}
            <p class="text-xs text-gray-400 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
        </a>
    </li>
@empty
    <li class="px-4 py-3 text-center text-gray-400 dark:text-gray-500">
        No new notifications
    </li>
@endforelse
