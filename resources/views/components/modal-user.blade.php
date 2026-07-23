<div id="userDetailModal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex items-center justify-center px-4"
    onclick="handleOutsideClick(event)">

    <div class="relative bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg w-full max-w-md"
        onclick="event.stopPropagation()">

        <h3 class="text-lg font-bold mb-1 text-center text-gray-800 dark:text-gray-100">
            User Detail
        </h3>

        <p class="text-xs text-center text-gray-500 dark:text-gray-400 mb-5">
            User information details
        </p>

        <div id="userDetailContent" class="text-sm space-y-3 sm:px-2">

            <div class="rounded-lg bg-gray-50 dark:bg-gray-700 px-4 py-3 border border-gray-100 dark:border-gray-600">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Name</p>
                <p id="detailName" class="font-semibold text-gray-800 dark:text-gray-100"></p>
            </div>

            <div class="rounded-lg bg-gray-50 dark:bg-gray-700 px-4 py-3 border border-gray-100 dark:border-gray-600">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Location</p>
                <p id="detailLocation" class="font-semibold text-gray-800 dark:text-gray-100"></p>
            </div>

            <div class="rounded-lg bg-gray-50 dark:bg-gray-700 px-4 py-3 border border-gray-100 dark:border-gray-600">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Email</p>
                <a id="detailEmail" href="#"
                    class="block font-semibold text-blue-600 dark:text-blue-400 hover:underline break-all"></a>
            </div>

            <div class="rounded-lg bg-gray-50 dark:bg-gray-700 px-4 py-3 border border-gray-100 dark:border-gray-600">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Phone</p>
                <a id="detailPhone" href="#"
                    class="block font-semibold text-green-600 dark:text-green-400 hover:underline break-all"
                    target="_blank" rel="noopener noreferrer"></a>
            </div>
        </div>

        <button onclick="closeUserModal()"
            class="mt-6 w-full rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2.5 transition shadow-md">
            Close
        </button>
    </div>
</div>
