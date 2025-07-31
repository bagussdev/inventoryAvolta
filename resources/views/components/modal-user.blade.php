<div id="userDetailModal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex items-center justify-center px-4"
    onclick="handleOutsideClick(event)">
    <div class="relative bg-white dark:bg-gray-800 p-6 pt-10 rounded-lg shadow-lg w-full max-w-md"
        onclick="event.stopPropagation()">
        {{-- Tombol Close --}}
        <button onclick="closeUserModal()"
            class="absolute top-3 right-3 text-gray-600 dark:text-gray-300 text-2xl hover:text-red-500">
            &times;
        </button>

        <h3 class="text-lg font-bold mb-4 text-center">User Detail</h3>

        <div id="userDetailContent" class="text-sm space-y-5 justify-center sm:px-5">
            <div class="grid grid-cols-3 gap-2">
                <span class="font-medium">Name</span>
                <span class="col-span-2 pl-3">: <span id="detailName"></span></span>
            </div>
            <div class="grid grid-cols-3 gap-2">
                <span class="font-medium">Location</span>
                <span class="col-span-2 pl-3">: <span id="detailLocation"></span></span>
            </div>
            <div class="grid grid-cols-3 gap-2">
                <span class="font-medium">Email</span>
                <span class="col-span-2 pl-3">: <a id="detailEmail" href="#"
                        class="text-blue-600 underline break-all"></a></span>
            </div>
            <div class="grid grid-cols-3 gap-2">
                <span class="font-medium">Phone</span>
                <span class="col-span-2 pl-3">: <a id="detailPhone" href="#"
                        class="text-green-600 underline break-all" target="_blank" rel="noopener noreferrer"></a></span>
            </div>
        </div>


    </div>
</div>
