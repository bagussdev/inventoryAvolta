<div class="relative">
    <button id="dateFilterToggle_{{ $formId }}" type="button"
        class="text-xs sm:text-sm px-3 py-1.5 sm:px-4 sm:py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition flex items-center gap-1">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
            class="w-4 h-4">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M6.75 3v2.25M17.25 3v2.25M3 18.75V8.25a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 8.25v10.5A2.25 2.25 0 0 1 18.75 21H5.25A2.25 2.25 0 0 1 3 18.75ZM16.5 7.5h-9" />
        </svg>
        <span class="hidden sm:inline-block">Filter by Date</span>
    </button>
    {{-- Dropdown dengan input tanggal --}}
    <div id="dateFilterDropdown_{{ $formId }}"
        class="absolute z-10 hidden bg-white dark:bg-gray-800 shadow-lg rounded-md mt-2 p-4 border border-gray-200 dark:border-gray-700 w-64 top-full left-0">
        <div class="flex flex-col gap-2">
            <div>
                <label for="start_date_{{ $formId }}"
                    class="block text-xs font-medium text-gray-700 dark:text-gray-300">From:</label>
                <input type="date" id="start_date_{{ $formId }}" name="start_date" value="{{ $startDate }}"
                    class="mt-1 block w-full text-sm rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>
            <div>
                <label for="end_date_{{ $formId }}"
                    class="block text-xs font-medium text-gray-700 dark:text-gray-300">To:</label>
                <input type="date" id="end_date_{{ $formId }}" name="end_date" value="{{ $endDate }}"
                    class="mt-1 block w-full text-sm rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>
            <div class="flex justify-end gap-2 mt-2">
                <button type="button" id="clearDateFilterBtn_{{ $formId }}"
                    class="text-xs px-3 py-1 bg-gray-500 text-white rounded-md hover:bg-gray-600">Clear</button>
                <button type="submit"
                    class="text-xs px-3 py-1 bg-purple-600 text-white rounded-md hover:bg-purple-700">Apply</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const formId = '{{ $formId }}'; // Ambil ID form dari properti komponen
            const dateFilterToggle = document.getElementById('dateFilterToggle_' + formId);
            const dateFilterDropdown = document.getElementById('dateFilterDropdown_' + formId);
            const filterForm = document.getElementById(formId); // Dapatkan form induk
            const clearDateFilterBtn = document.getElementById('clearDateFilterBtn_' + formId);
            const startDateInput = document.getElementById('start_date_' + formId);
            const endDateInput = document.getElementById('end_date_' + formId);

            if (dateFilterToggle && dateFilterDropdown && filterForm && clearDateFilterBtn) {
                dateFilterToggle.addEventListener('click', function(event) {
                    event
                        .stopPropagation(); // Mencegah event click menyebar ke document dan langsung menutup dropdown
                    dateFilterDropdown.classList.toggle('hidden');
                });

                // Menyembunyikan dropdown saat mengklik di luar area dropdown atau tombolnya
                document.addEventListener('click', function(event) {
                    if (!dateFilterDropdown.contains(event.target) && !dateFilterToggle.contains(event
                            .target)) {
                        dateFilterDropdown.classList.add('hidden');
                    }
                });

                // Mengosongkan input tanggal dan mengirimkan form
                clearDateFilterBtn.addEventListener('click', function() {
                    startDateInput.value = '';
                    endDateInput.value = '';
                    filterForm.submit(); // Kirimkan form induk
                });
            }
        });
    </script>
@endpush
