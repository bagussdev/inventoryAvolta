<div class="relative" x-data="{ open: false }">
    {{-- Bell Icon --}}
    <button id="notificationBell" @click="open = !open; if(open) markNotificationsRead();"
        class="relative flex items-center justify-center w-10 h-10 text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-full hover:ring-2 ring-purple-300 transition">
        <span class="sr-only">Toggle notification menu</span>
        {{-- SVG bell --}}
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
            class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M14.857 17.104A4.001 4.001 0 0112 18a4.001 4.001 0 01-2.857-0.896M18 8a6 6 0 10-12 0c0 7-3 9-3 9h18s-3-2-3-9z" />
        </svg>
        {{-- Red dot --}}
        <span id="notificationDot"
            class="absolute top-1 right-1 w-2.5 h-2.5 bg-red-500 rounded-full animate-ping hidden"></span>
    </button>

    {{-- Dropdown --}}
    <div x-show="open" @click.away="open = false" x-transition
        class="absolute left-1/2 -translate-x-1/2 sm:translate-x-0 sm:left-auto sm:right-0 mt-2 w-72 max-w-xs bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-[60]">
        <div class="px-4 py-3 border-b dark:border-gray-700 text-sm font-semibold text-gray-700 dark:text-gray-200">
            Notifications
        </div>

        <ul id="notificationList"
            class="max-h-60 overflow-y-auto text-sm text-gray-700 dark:text-gray-300 divide-y divide-gray-200 dark:divide-gray-700">
            @include('partials.notifications-list')
        </ul>
    </div>
</div>


@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/list.js/2.3.1/list.min.js"></script>
    <script>
        let lastNotifUpdate = null;
        console.log('🔄 Polling...');
        async function checkNotificationUpdate() {
            try {
                const res = await fetch("{{ route('notifications.lastUpdated') }}");
                if (!res.ok) throw new Error('Gagal fetch notifikasi');

                const data = await res.json();
                const latest = data.last_updated;
                const dot = document.getElementById('notificationDot');

                if (!lastNotifUpdate || latest !== lastNotifUpdate) {
                    // hanya fetch jika ada update
                    const unreadRes = await fetch("{{ route('notifications.unreadCount') }}");
                    if (!unreadRes.ok) throw new Error('Gagal fetch unread count');

                    const unread = await unreadRes.json();

                    if (unread.count > 0) {
                        dot.classList.remove('hidden');
                    } else {
                        dot.classList.add('hidden');
                    }

                    lastNotifUpdate = latest;
                    const notifListRes = await fetch("{{ route('notifications.html') }}");
                    const html = await notifListRes.text();
                    document.getElementById('notificationList').innerHTML = html;
                }
            } catch (err) {
                console.error('❌ Polling notifikasi error:', err);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            checkNotificationUpdate(); // inisialisasi pertama
            setInterval(checkNotificationUpdate, 10000); // polling 10 detik, tapi hanya action jika ada perubahan
        });


        function markNotificationsRead() {
            const dot = document.getElementById('notificationDot');

            // Jika tidak ada notifikasi baru, tidak perlu mark as read
            if (dot.classList.contains('hidden')) {
                console.log('🔕 Tidak ada notif baru. Skip mark-as-read.');
                return;
            }

            // Jika ada dot merah, berarti masih ada yang belum dibaca
            fetch("{{ route('notifications.markAsRead') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json'
                }
            }).then(() => {
                console.log('🔔 Notifications marked as read');
                dot.classList.add('hidden');
                checkNotificationUpdate(); // optional recheck
            });
        }
    </script>
@endpush
