{{-- Modal Pending --}}
<div id="pendingModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex justify-center items-center p-4">
    <div class="bg-white dark:bg-gray-800 w-full max-w-md p-6 rounded-lg shadow-lg">
        <h3 class="text-lg font-semibold mb-4 text-center text-gray-900 dark:text-white">Mark Incident as Pending</h3>
        <form method="POST" action="{{ route('incidents.pending', $incident->id) }}"
            onsubmit="return confirmAndLoad('Are you Sure to mark incident as Pending?')">
            @csrf
            <label for="notes_pending" class="block mb-2 text-sm text-gray-700 dark:text-gray-300">Notes</label>
            <textarea name="notes" id="notes_pending" rows="4" required
                class="w-full rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm px-3 py-2"></textarea>

            <div class="flex justify-end mt-4 gap-2">
                <button type="button" onclick="closePendingModal()"
                    class="bg-gray-600 text-white px-3 py-1.5 text-sm sm:px-3 sm:py-2 sm:text-sm rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">Cancel</button>
                <button type="submit"
                    class="bg-red-600 text-white px-3 py-1.5 text-sm sm:px-3 sm:py-2 sm:text-sm rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">Confirm
                    Pending</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
    <script>
        function openPendingModal() {
            document.getElementById('pendingModal').classList.remove('hidden');
        }

        function closePendingModal() {
            document.getElementById('pendingModal').classList.add('hidden');
        }
    </script>
@endpush
