<x-app-layout>
    <x-dashboard.sidebar>
        <x-alert-information></x-alert-information>

        {{-- Back button --}}
        @if ($maintenance->status === 'completed')
            <div class="mt-4">
                <a href="{{ route('maintenances.completed') }}" onclick="showFullScreenLoader();"
                    class="inline-flex items-center text-gray-700 hover:text-purple-600 dark:text-gray-300 dark:hover:text-white transition duration-150 ease-in-out">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 32 32" class="mr-2">
                        <path fill="#101820"
                            d="M30,29a1,1,0,0,1-.81-.41l-2.12-2.92A18.66,18.66,0,0,0,15,18.25V22a1,1,0,0,1-1.6.8l-12-9a1,1,0,0,1,0-1.6l12-9A1,1,0,0,1,15,4V8.24A19,19,0,0,1,31,27v1a1,1,0,0,1-.69.95A1.12,1.12,0,0,1,30,29ZM14,16.11h.1A20.68,20.68,0,0,1,28.69,24.5l.16.21a17,17,0,0,0-15-14.6,1,1,0,0,1-.89-1V6L3.67,13,13,20V17.11a1,1,0,0,1,.33-.74A1,1,0,0,1,14,16.11Z" />
                    </svg>
                    <span class="text-sm font-medium">Back</span>
                </a>
            </div>
        @else
            <div class="mt-4">
                <a href="{{ route('maintenances.index') }}" onclick="showFullScreenLoader();"
                    class="inline-flex items-center text-gray-700 hover:text-purple-600 dark:text-gray-300 dark:hover:text-white transition duration-150 ease-in-out">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 32 32"
                        class="mr-2">
                        <path fill="#101820"
                            d="M30,29a1,1,0,0,1-.81-.41l-2.12-2.92A18.66,18.66,0,0,0,15,18.25V22a1,1,0,0,1-1.6.8l-12-9a1,1,0,0,1,0-1.6l12-9A1,1,0,0,1,15,4V8.24A19,19,0,0,1,31,27v1a1,1,0,0,1-.69.95A1.12,1.12,0,0,1,30,29ZM14,16.11h.1A20.68,20.68,0,0,1,28.69,24.5l.16.21a17,17,0,0,0-15-14.6,1,1,0,0,1-.89-1V6L3.67,13,13,20V17.11a1,1,0,0,1,.33-.74A1,1,0,0,1,14,16.11Z" />
                    </svg>
                    <span class="text-sm font-medium">Back</span>
                </a>
            </div>
        @endif


        {{-- Title --}}
        <div class="flex justify-between items-center mt-4 w-full max-w-full">
            <h2 class="font-bold text-xl sm:text-2xl">
                Maintenance Details
            </h2>
        </div>

        <hr class="h-[3px] my-4 bg-gray-200 border-0 dark:bg-gray-700 w-full">

        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md text-sm text-gray-700 dark:text-gray-300">

            {{-- Detail Maintenance --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-center">
                    <div class="w-40 font-medium">ID</div>
                    <div class="flex-1">: {{ $maintenance->id }}</div>
                </div>
                <div class="flex items-center">
                    <div class="w-40 font-medium">Store</div>
                    <div class="flex-1">: {{ $maintenance->equipment->store->name ?? '-' }}</div>
                </div>
                <div class="flex items-center">
                    <div class="w-40 font-medium">Item</div>
                    <div class="flex-1">: {{ $maintenance->equipment->item->name ?? '-' }}</div>
                </div>
                <div class="flex items-center">
                    <div class="w-40 font-medium">Maintenance Scheduled</div>
                    <div class="flex-1">: {{ \Carbon\Carbon::parse($maintenance->maintenance_date)->format('d M Y') }}
                    </div>
                </div>
                <div class="flex items-center">
                    <div class="w-40 font-medium">Model</div>
                    <div class="flex-1">: {{ $maintenance->equipment->item->model ?? '-' }}</div>
                </div>

                <div class="flex items-center">
                    <div class="w-40 font-medium">PIC Staff</div>
                    <div class="flex-1">: {{ $maintenance->staff->name ?? '-' }} /
                        {{ $maintenance->staff->role->name ?? '-' }}</div>
                </div>
                <div class="flex items-center">
                    <div class="w-40 font-medium">Brand</div>
                    <div class="flex-1">: {{ $maintenance->equipment->item->brand ?? '-' }}</div>
                </div>
                <div class="flex items-center">
                    <div class="w-40 font-medium">Resolved At</div>
                    <div class="flex-1">: {{ \Carbon\Carbon::parse($maintenance->updated_at)->format('d M Y') }}
                    </div>
                </div>
                <div class="flex items-center">
                    <div class="w-40 font-medium">Confirm By</div>
                    <div class="flex-1">: {{ $maintenance->confirm->name ?? 'N/A' }}
                    </div>
                </div>
                <div class="flex items-center">
                    <div class="w-40 font-medium">Status</div>
                    <div class="flex-1">:
                        <span
                            class="px-3 py-1 text-xs font-medium rounded-md {{ match (strtolower($maintenance->status)) {
                                'completed' => 'bg-green-100 text-green-800',
                                'maintenance' => 'bg-red-100 text-red-600',
                                'in progress' => 'bg-blue-100 text-blue-800',
                                'resolved' => 'bg-purple-100 text-purple-800',
                                default => 'bg-gray-100 text-gray-600',
                            } }}">
                            {{ ucfirst($maintenance->status) }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Notes dan Attachment --}}
            <div class="mt-6">
                <p class="font-semibold">Notes:</p>
                <p class="italic text-gray-600 dark:text-gray-300">{{ $maintenance->notes ?? 'No notes provided.' }}
                </p>
            </div>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Attachment --}}
                <div>
                    <h4 class="text-md font-semibold mb-2">Attachment</h4>

                    @if ($maintenance->attachment)
                        <div id="attachment-viewer" class="max-w-full">
                            @php
                                $ext = pathinfo($maintenance->attachment, PATHINFO_EXTENSION);
                                $url = asset('storage/' . $maintenance->attachment);
                            @endphp

                            @if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif']))
                                <img src="{{ $url }}" alt="Attachment"
                                    class="cursor-zoom-in max-h-60 rounded shadow w-full object-contain" />
                            @elseif(in_array(strtolower($ext), ['mp4', 'mov', 'avi']))
                                <video src="{{ $url }}" controls
                                    class="rounded shadow w-full max-h-96 mt-2"></video>
                            @else
                                <a href="{{ $url }}" target="_blank" class="text-blue-600 underline"
                                    onclick="showFullScreenLoader();">View
                                    Attachment</a>
                            @endif
                        </div>
                    @else
                        <p class="text-gray-500 italic">No attachment uploaded.</p>
                    @endif
                </div>

                {{-- Used Spareparts --}}
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <h3 class="font-bold text-md text-gray-800 dark:text-gray-200">Used Spareparts</h3>
                    </div>

                    <div class="text-sm">
                        @if ($maintenance->usedSpareParts->isEmpty())
                            <p id="no-spareparts-message" class="text-gray-500 italic">No spareparts used for this
                                maintenance.</p>
                        @else
                            <ul id="used-spareparts-list" class="list-disc ml-6">
                                @foreach ($maintenance->usedSpareParts as $used)
                                    <li>
                                        {{ $used->sparepart->item->name ?? 'Unknown' }} — Qty: {{ $used->qty }}
                                        @if ($used->note)
                                            <span class="text-gray-500 italic">({{ $used->note }})</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    {{-- Tombol Edit dan Add Spareparts --}}
                    <div class="mt-4 text-left flex gap-2">
                        @if (strtolower($maintenance->status) === 'completed')
                            <button type="button" onclick="openSparepartModal()"
                                class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                                ⚙️ Manage Spareparts
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Form untuk submit sparepart, diisi oleh JS --}}
            <form id="sparepartsForm" action="{{ route('maintenances.updateSpareparts', $maintenance->id) }}"
                method="POST" onsubmit="showFullScreenLoader();">
                @csrf
                @method('PUT')
                <div id="sparepartsContainer" class="hidden"></div>
                <div id="deleted_ids" class="hidden"></div>
            </form>

        </div>

        {{-- === MODAL UNTUK MANAGE SPAREPART === --}}
        @if ($maintenance->status === 'completed')
            <div id="sparepartModal"
                class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex justify-center items-center p-4">
                <div
                    class="bg-white dark:bg-gray-900 w-full max-w-2xl p-6 rounded-md shadow-lg transform transition-all scale-0 opacity-0 duration-300">
                    <h3 class="text-xl font-bold mb-4 text-center text-gray-800 dark:text-gray-200">Manage Used
                        Spareparts
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
                    <button type="button" onclick="addSparepartRow()"
                        class="text-blue-500 text-sm mt-4 hover:underline">+
                        Add Item</button>
                    <div class="flex justify-end gap-3 mt-6">
                        <button onclick="closeSparepartModal()" type="button"
                            class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">Cancel</button>
                        <button type="button" onclick="confirmApplyChanges()"
                            class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">Apply
                            Changes</button>
                    </div>
                </div>
            </div>
        @endif
        @push('scripts')
            {{-- Script for Viewer.js --}}
            <script>
                function showFullScreenLoader() {
                    document.getElementById('loading-overlay').classList.remove('hidden');
                }

                function hideFullScreenLoader() {
                    document.getElementById('loading-overlay').classList.add('hidden');
                }
                document.addEventListener("DOMContentLoaded", function() {
                    const viewerContainer = document.getElementById('attachment-viewer');
                    if (viewerContainer) {
                        new Viewer(viewerContainer.querySelector('img'), {
                            toolbar: true,
                            title: false,
                            navbar: false,
                        });
                    }
                });
            </script>

            {{-- SCRIPT MODAL MANAGE SPAREPART (REVISED) --}}
            <script>
                // Ambil data sparepart dari PHP ke JavaScript.
                const allSpareparts = @json($spareparts->keyBy('id'));
                let usedSpareparts = @json($maintenance->usedSpareParts);

                let deletedUsedSparepartIds = [];

                // --- FUNGSI UTILITY (Didefinisikan di awal) ---

                /**
                 * Memvalidasi kuantitas yang dimasukkan di input.
                 * @param {HTMLElement} qtyInput - Elemen input kuantitas.
                 */
                function validateQty(qtyInput) {
                    const row = qtyInput.closest('.sparepart-row');
                    const selectElement = row.querySelector('select');
                    const errorSpan = row.querySelector('.validation-error');
                    const selectedId = selectElement.value;
                    const qty = parseInt(qtyInput.value);
                    const maxQty = parseInt(qtyInput.max);

                    if (!selectedId || qtyInput.value === '') {
                        errorSpan.textContent = ''; // Kosongkan
                        errorSpan.classList.add('invisible');
                        qtyInput.classList.remove('border-red-500');
                        return;
                    }

                    if (qty <= 0 || isNaN(qty)) {
                        errorSpan.textContent = 'Qty must be greater than 0';
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
                 * Memperbarui tampilan stok yang tersedia berdasarkan sparepart yang dipilih.
                 * @param {HTMLElement} selectElement - Elemen <select> yang berubah.
                 */
                function updateStockDisplay(selectElement) {
                    const selectedSparepartId = selectElement.value;
                    const row = selectElement.closest('.sparepart-row');
                    const stockInput = row.querySelector('input[placeholder="Available Stock"]');
                    const qtyInput = row.querySelector('input[placeholder="Qty"]');
                    const usedId = row.getAttribute('data-used-id');
                    const oldSparepartIdForThisRow = row.getAttribute('data-old-sparepart-id');

                    const spinner = row.querySelector('.spinner');

                    // Tampilkan fullscreen loader
                    showFullScreenLoader();

                    if (spinner) spinner.classList.remove('hidden');
                    stockInput.value = '...'; // show temporary text

                    if (!selectedSparepartId || !allSpareparts[selectedSparepartId]) {
                        stockInput.value = '0';
                        qtyInput.max = '';
                        if (spinner) spinner.classList.add('hidden');
                        hideFullScreenLoader(); // tutup jika error/empty
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

                            if (spinner) spinner.classList.add('hidden');
                            validateQty(qtyInput);
                        })
                        .catch(error => {
                            console.error("Error fetching stock:", error);
                            stockInput.value = '0';
                            qtyInput.max = '';
                            if (spinner) spinner.classList.add('hidden');
                        })
                        .finally(() => {
                            hideFullScreenLoader(); // pastikan loader ditutup
                        });
                }

                function confirmRemoveRow(button) {
                    const row = button.closest('.sparepart-row');
                    const usedId = row.getAttribute('data-used-id');
                    const select = row.querySelector('select');
                    const sparepartName = select.options[select.selectedIndex] ? select.options[select.selectedIndex].text :
                        'this item';

                    if (confirm(`Are you sure you want to delete "${sparepartName}"? This action will restore the stock.`)) {
                        // Tambahkan ID ke array deletedUsedSparepartIds jika ada
                        if (usedId) {
                            deletedUsedSparepartIds.push(usedId);
                        }
                        row.remove(); // Hapus baris dari DOM modal
                    }
                }

                function addSparepartRow(usedSp = null) {
                    if (Object.keys(allSpareparts).length === 0) {
                        alert('No spare parts available in the inventory.');
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
                                min="1" placeholder="Qty" oninput="validateQty(this)" />
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
                                <button type="button" onclick="confirmRemoveRow(this)"
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

                // --- FUNGSI UTAMA ---

                /**
                 * Membuka modal untuk mengelola sparepart.
                 */
                function openSparepartModal() {
                    const modal = document.getElementById('sparepartModal');
                    const sparepartRowsContainer = document.getElementById('sparepartRows');
                    sparepartRowsContainer.innerHTML = '';
                    deletedUsedSparepartIds = []; // Reset daftar yang akan dihapus

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
                 * Menutup modal.
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
                 * Mengkonfirmasi dan menerapkan perubahan sparepart.
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
                        alert('Please correct the errors in the form before applying changes.');
                        return;
                    }

                    if (confirm('Are you sure you want to apply these changes?')) {
                        submitSparepartsForm();
                    }
                }

                /**
                 * Mengirimkan form sparepart ke backend.
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
                        alert('No changes to apply.');
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
    </x-dashboard.sidebar>
</x-app-layout>
