{{-- MAIN MODAL FOR MANAGING SPAREPARTS --}}
@if (isset($request) && (strtolower($request->status) === 'resolved' || strtolower($request->status) === 'completed'))
    <div id="sparepartModal"
        class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex justify-center items-center p-4">
        <div
            class="bg-white dark:bg-gray-900 w-full max-w-2xl p-6 rounded-md shadow-lg transform transition-all scale-0 opacity-0 duration-300">
            <h3 class="text-xl font-bold mb-4 text-center text-gray-800 dark:text-gray-200">Manage Used Spareparts
            </h3>
            <div class="grid grid-cols-12 gap-2 text-sm font-semibold text-gray-600 dark:text-gray-300 mb-2">
                <div class="col-span-5">Sparepart</div>
                <div class="col-span-2">Stock</div>
                <div class="col-span-2">Qty</div>
                <div class="col-span-2">Note</div>
                <div class="col-span-1"></div>
            </div>
            <div id="sparepartRows" class="space-y-3 max-h-80 overflow-y-auto pr-2">
            </div>
            <button type="button" onclick="addSparepartRow()" class="text-blue-500 text-sm mt-4 hover:underline">+
                Add Item</button>
            <div class="flex justify-end gap-3 mt-6">
                <button onclick="closeSparepartModal()" type="button"
                    class="text-xs px-3 py-1.5 sm:px-4 sm:py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    Cancel
                </button>
                <button type="button" onclick="confirmApplyChanges()"
                    class="text-xs px-3 py-1.5 sm:px-4 sm:py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    Apply Changes
                </button>
            </div>
        </div>
    </div>
@endif

{{-- CONFIRM DELETE ROW MODAL --}}
<div id="confirmDeleteRowModal"
    class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex justify-center items-center p-4">
    <div
        class="bg-white dark:bg-gray-900 w-full max-w-sm p-6 rounded-md shadow-lg transform scale-0 opacity-0 transition-all duration-300">
        <h3 class="text-xl font-bold mb-4 text-center text-gray-800 dark:text-gray-200">Confirm Deletion</h3>
        <p id="deleteModalMessage" class="text-gray-700 dark:text-gray-300 text-center mb-6">Are you sure you want to
            delete this item? This action will restore the stock.</p>
        <div class="flex justify-end gap-3">
            <button type="button" onclick="closeConfirmDeleteRowModal()"
                class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">Cancel</button>
            <button type="button" id="confirmDeleteRowButton"
                class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">Delete</button>
        </div>
    </div>
</div>

{{-- CONFIRM APPLY CHANGES MODAL --}}
<div id="confirmApplyChangesModal"
    class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex justify-center items-center p-4">
    <div
        class="bg-white dark:bg-gray-900 w-full max-w-sm p-6 rounded-md shadow-lg transform scale-0 opacity-0 transition-all duration-300">
        <h3 class="text-xl font-bold mb-4 text-center text-gray-800 dark:text-gray-200">Confirm Changes</h3>
        <p class="text-gray-700 dark:text-gray-300 text-center mb-6">Are you sure you want to apply these changes?
        </p>
        <div class="flex justify-end gap-3">
            <button type="button" onclick="closeConfirmApplyChangesModal()"
                class="text-xs px-3 py-1.5 sm:px-4 sm:py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                Cancel
            </button>
            <button type="button" id="applyChangesButton"
                class="text-xs px-3 py-1.5 sm:px-4 sm:py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                Apply Changes
            </button>
        </div>
    </div>
</div>

{{-- All JavaScript for the three modals moved here --}}
@push('scripts')
    <script>
        // Get sparepart data from PHP to JavaScript.
        const allSpareparts = @json($spareparts->keyBy('id'));
        // Here we only refer to $request because this partial is within the request folder
        let usedSpareparts = @json($request->usedSpareParts ?? []);

        let deletedUsedSparepartIds = [];

        // --- GLOBAL VARIABLES FOR MODALS ---
        let currentDeleteRowElement = null; // To store the row element to be deleted
        let currentSparepartName = ''; // To store the name of the sparepart to be deleted

        // --- UTILITY FUNCTIONS ---

        /**
         * Validates the quantity entered in the input.
         * @param {HTMLElement} qtyInput - The quantity input element.
         */
        function validateQty(qtyInput) {
            const row = qtyInput.closest('.sparepart-row');
            const selectElement = row.querySelector('select');
            const errorSpan = row.querySelector('.validation-error');
            const selectedId = selectElement.value;
            const qty = parseInt(qtyInput.value);
            const maxQty = parseInt(qtyInput.max);

            if (!selectedId || qtyInput.value === '') {
                errorSpan.textContent = ''; // Clear
                errorSpan.classList.add('invisible');
                qtyInput.classList.remove('border-red-500');
                return;
            }

            if (qty <= 0 || isNaN(qty)) {
                errorSpan.textContent = 'Quantity must be greater than 0';
                errorSpan.classList.remove('invisible');
                qtyInput.classList.add('border-red-500');
            } else if (qty > maxQty) {
                errorSpan.textContent = `Max: ${maxQty}`;
                errorSpan.classList.remove('invisible');
                qtyInput.classList.add('border-red-500');
            } else {
                errorSpan.textContent = '';
                errorSpan.classList.add('invisible');
                qtyInput.classList.remove('border-red-500');
            }
        }


        /**
         * Updates the displayed available stock based on the selected sparepart.
         * @param {HTMLElement} selectElement - The changing <select> element.
         */
        function updateStockDisplay(selectElement) {
            const selectedSparepartId = selectElement.value;
            const row = selectElement.closest('.sparepart-row');
            const stockInput = row.querySelector('input[placeholder="Available Stock"]');
            const qtyInput = row.querySelector('input[placeholder="Quantity"]');
            const usedId = row.getAttribute('data-used-id');
            const oldSparepartIdForThisRow = row.getAttribute('data-old-sparepart-id');

            stockInput.value = '...';

            if (!selectedSparepartId || !allSpareparts[selectedSparepartId]) {
                stockInput.value = '0';
                qtyInput.max = '';
                validateQty(qtyInput);
                return;
            }

            fetch(`/api/spareparts/${selectedSparepartId}/stock`)
                .then(response => response.json())
                .then(data => {
                    let availableStock = data.stock;

                    if (usedId && oldSparepartIdForThisRow == selectedSparepartId) {
                        const oldUsedItem = usedSpareparts.find(item => item.id == usedId);
                        if (oldUsedItem) availableStock += oldUsedItem.qty;
                    }

                    stockInput.value = availableStock;
                    qtyInput.max = availableStock;
                    validateQty(qtyInput);
                })
                .catch(error => {
                    console.error("Error fetching stock:", error);
                    stockInput.value = '0';
                    qtyInput.max = '';
                })
                .finally(() => {
                    // hideFullScreenLoader(); // Ensure loader is hidden, if you use it here
                });
        }

        // --- NEW MODAL FUNCTIONS ---

        /**
         * Opens the confirmation modal for deleting a row.
         * @param {HTMLElement} button - The delete button clicked.
         */
        function openConfirmDeleteRowModal(button) {
            currentDeleteRowElement = button.closest('.sparepart-row');
            const selectElement = currentDeleteRowElement.querySelector('select');
            currentSparepartName = selectElement.options[selectElement.selectedIndex] ? selectElement.options[selectElement
                    .selectedIndex].text :
                'this item';

            const deleteModalMessage = document.getElementById('deleteModalMessage');
            deleteModalMessage.textContent =
                `Are you sure you want to delete "${currentSparepartName}"? This action will restore the stock.`;

            const modal = document.getElementById('confirmDeleteRowModal');
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.querySelector('.transform').classList.remove('scale-0', 'opacity-0');
                modal.querySelector('.transform').classList.add('scale-100', 'opacity-100');
            }, 50);

            document.getElementById('confirmDeleteRowButton').onclick = function() {
                executeRemoveRow();
            };
        }

        /**
         * Closes the confirmation modal for deleting a row.
         */
        function closeConfirmDeleteRowModal() {
            const modal = document.getElementById('confirmDeleteRowModal');
            modal.querySelector('.transform').classList.remove('scale-100', 'opacity-100');
            modal.querySelector('.transform').classList.add('scale-0', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                currentDeleteRowElement = null; // Clear reference
                currentSparepartName = ''; // Clear name
            }, 300);
        }

        /**
         * Executes the row removal after confirmation.
         */
        function executeRemoveRow() {
            if (currentDeleteRowElement) {
                const usedId = currentDeleteRowElement.getAttribute('data-used-id');
                if (usedId) {
                    deletedUsedSparepartIds.push(usedId);
                }
                currentDeleteRowElement.remove(); // Remove row from DOM
            }
            closeConfirmDeleteRowModal(); // Close the modal
        }


        /**
         * Opens the confirmation modal for applying changes.
         */
        function openConfirmApplyChangesModal() {
            const modal = document.getElementById('confirmApplyChangesModal');
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.querySelector('.transform').classList.remove('scale-0', 'opacity-0');
                modal.querySelector('.transform').classList.add('scale-100', 'opacity-100');
            }, 50);

            document.getElementById('applyChangesButton').onclick = function() {
                executeApplyChanges();
            };
        }

        /**
         * Closes the confirmation modal for applying changes.
         */
        function closeConfirmApplyChangesModal() {
            const modal = document.getElementById('confirmApplyChangesModal');
            modal.querySelector('.transform').classList.remove('scale-100', 'opacity-100');
            modal.querySelector('.transform').classList.add('scale-0', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        /**
         * Executes applying changes after confirmation.
         */
        function executeApplyChanges() {
            closeConfirmApplyChangesModal(); // Close the modal
            submitSparepartsForm(); // Submit the form
        }

        // --- END NEW MODAL FUNCTIONS ---


        function addSparepartRow(usedSp = null) {
            if (Object.keys(allSpareparts).length === 0) {
                alert(
                    'No spare parts available in the inventory.'); // Consider a custom modal for this alert too
                return;
            }

            let optionsHtml = '<option value="">-- Select Sparepart --</option>';
            Object.values(allSpareparts).forEach(sp => {
                const isSelected = usedSp && usedSp.spareparts_id == sp.id ? 'selected' : '';
                optionsHtml +=
                    `<option value="${sp.id}" ${isSelected}>${sp.item.name ?? ''} (${sp.item.model ?? ''}) (${sp.item.brand ?? ''})</option>`;
            });

            const usedQty = usedSp ? usedSp.qty : '';
            const usedNote = usedSp && usedSp.note !== null ? usedSp.note : '';
            const usedId = usedSp ? usedSp.id : '';
            const sparepartId = usedSp ? usedSp.spareparts_id : '';

            const newRow = document.createElement('div');
            newRow.className = 'grid grid-cols-12 gap-2 items-center sparepart-row';
            newRow.setAttribute('data-used-id', usedId);
            newRow.setAttribute('data-old-sparepart-id', sparepartId);
            newRow.innerHTML = `
            <div class="col-span-5">
                <div class="flex flex-col gap-1">
                    <select
                        class="w-full text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white sparepart-select focus:ring-purple-500 focus:border-purple-500"
                        onchange="updateStockDisplay(this)">
                        ${optionsHtml}
                    </select>
                    <div class="min-h-[18px]"></div>
                </div>
            </div>
            <div class="col-span-2">
                <div class="flex flex-col gap-1">
                    <input type="text" readonly value="0"
                        class="w-full border rounded-md text-sm px-3 py-2 bg-gray-100 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400"
                        placeholder="Available Stock" />
                    <div class="min-h-[18px]"></div>
                </div>
            </div>
            <div class="col-span-2">
                <div class="flex flex-col gap-1">
                    <input type="number" value="${usedQty}"
                        class="w-full border rounded-md text-sm px-3 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-purple-500 focus:border-purple-500"
                        min="1" placeholder="Quantity" oninput="validateQty(this)" />
                    <span class="text-red-500 text-xs validation-error block min-h-[18px] whitespace-normal leading-snug"></span>
                </div>
            </div>
            <div class="col-span-2">
                <div class="flex flex-col gap-1">
                    <input type="text" value="${usedNote}"
                        class="w-full border rounded-md text-sm px-3 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-purple-500 focus:border-purple-500"
                        placeholder="Note" />
                    <div class="min-h-[18px]"></div>
                </div>
            </div>
            @can('usedspareparts.delete')
                <div class="col-span-1 text-right">
                    <div class="flex flex-col gap-1">
                        <button type="button" onclick="openConfirmDeleteRowModal(this)"
                            class="text-red-500 hover:text-red-700 focus:outline-none">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="feather feather-x-circle">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                <line x1="9" y1="9" x2="15" y2="15"></line>
                            </svg>
                        </button>
                        <div class="min-h-[18px]"></div>
                    </div>
                </div>
            @endcan
            `;
            document.getElementById('sparepartRows').appendChild(newRow);
            const selectElement = newRow.querySelector('select');
            updateStockDisplay(selectElement);
        }

        // --- MAIN FUNCTIONS ---

        /**
         * Opens the modal to manage spareparts.
         */
        function openSparepartModal() {
            const modal = document.getElementById('sparepartModal');
            const sparepartRowsContainer = document.getElementById('sparepartRows');
            sparepartRowsContainer.innerHTML = '';
            deletedUsedSparepartIds = []; // Reset list of deleted items

            if (Array.isArray(usedSpareparts) && usedSpareparts.length > 0) {
                usedSpareparts.forEach(usedSp => {
                    addSparepartRow(usedSp);
                });
            } else {
                addSparepartRow();
            }

            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.querySelector('.transform').classList.remove('scale-0', 'opacity-0');
                modal.querySelector('.transform').classList.add('scale-100', 'opacity-100');
            }, 50);
        }

        /**
         * Closes the modal.
         */
        function closeSparepartModal() {
            const modal = document.getElementById('sparepartModal');
            modal.querySelector('.transform').classList.remove('scale-100', 'opacity-100');
            modal.querySelector('.transform').classList.add('scale-0', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        /**
         * Confirms and applies sparepart changes.
         */
        function confirmApplyChanges() {
            const rows = document.querySelectorAll('#sparepartRows > .sparepart-row');
            let isValid = true;
            rows.forEach(row => {
                const select = row.querySelector('select');
                const qtyInput = row.querySelector('input[type="number"]');
                if (select.value && (parseInt(qtyInput.value) <= 0 || isNaN(parseInt(qtyInput.value)) || parseInt(
                        qtyInput.value) > parseInt(qtyInput.max))) {
                    isValid = false;
                }
            });

            if (!isValid) {
                alert('Please correct the errors in the form before applying changes.'); // Still a native alert
                return;
            }

            openConfirmApplyChangesModal(); // Show custom confirmation modal
        }

        /**
         * Submits the sparepart form to the backend.
         */
        function submitSparepartsForm() {
            const rows = document.querySelectorAll('#sparepartRows > .sparepart-row');
            const sparepartsContainer = document.getElementById('sparepartsContainer');
            const deletedContainer = document.getElementById('deleted_ids');
            sparepartsContainer.innerHTML = '';
            deletedContainer.innerHTML = '';

            let sparepartsData = [];
            rows.forEach(row => {
                const select = row.querySelector('select');
                const qtyInput = row.querySelector('input[type="number"]');
                const noteInput = row.querySelector('input[type="text"]:not([readonly])');
                const usedId = row.getAttribute('data-used-id');

                const selectedId = select.value;
                const qty = parseInt(qtyInput.value);
                const note = noteInput.value;

                if (selectedId && qty > 0 && !isNaN(qty)) {
                    sparepartsData.push({
                        id: selectedId,
                        qty: qty,
                        note: note,
                        used_id: usedId
                    });
                }
            });

            if (sparepartsData.length === 0 && deletedUsedSparepartIds.length === 0) {
                alert('No changes to apply.'); // Still a native alert
                return;
            }

            sparepartsData.forEach((data, index) => {
                sparepartsContainer.insertAdjacentHTML('beforeend', `
                    <input type="hidden" name="spareparts[${index}][id]" value="${data.id}">
                    <input type="hidden" name="spareparts[${index}][qty]" value="${data.qty}">
                    <input type="hidden" name="spareparts[${index}][note]" value="${data.note}">
                    <input type="hidden" name="spareparts[${index}][used_id]" value="${data.used_id}">
                `);
            });

            deletedUsedSparepartIds.forEach((id) => {
                deletedContainer.insertAdjacentHTML('beforeend', `
                    <input type="hidden" name="deleted_ids[]" value="${id}">
                `);
            });

            document.getElementById('sparepartsForm').submit();
        }
    </script>
@endpush
