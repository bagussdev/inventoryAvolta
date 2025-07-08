<div id="loading-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-[999] hidden flex justify-center items-center">
    <div class="bg-white dark:bg-gray-900 p-6 rounded-lg shadow-lg flex items-center gap-3">
        <svg class="animate-spin h-6 w-6 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none"
            viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
            </circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
        </svg>
        <span class="text-gray-800 dark:text-gray-200 font-medium">Loading data...</span>
    </div>
</div>

<script>
    // Fungsi ini akan tersedia secara global
    function showFullScreenLoader() {
        document.getElementById('loading-overlay').classList.remove('hidden');
    }

    function hideFullScreenLoader() {
        document.getElementById('loading-overlay').classList.add('hidden');
    }
</script>
