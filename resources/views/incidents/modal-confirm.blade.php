<div id="sparepartModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex justify-center items-center p-4">
    <div
        class="bg-white dark:bg-gray-900 w-full max-w-2xl p-6 rounded-md shadow-lg transform transition-all scale-100 opacity-100">
        <h3 class="text-xl font-bold mb-4 text-center text-gray-800 dark:text-gray-200">Input Spareparts Used
        </h3>
        {{-- Tambahkan header untuk kolom di modal --}}
        <div class="grid grid-cols-12 gap-2 text-sm font-semibold text-gray-600 dark:text-gray-300 mb-2">
            <div class="col-span-4">Sparepart</div>
            <div class="col-span-2">Stock</div> {{-- Kolom Stock baru --}}
            <div class="col-span-2">Qty</div>
            <div class="col-span-3">Note</div>
            <div class="col-span-1"></div> {{-- Untuk tombol remove --}}
        </div>
        <div id="sparepartRows" class="space-y-3">
        </div>
        <button type="button" onclick="addSparepartRow()" class="text-blue-500 text-sm mt-4 hover:underline">+
            Add More
            Items</button>
        <div class="flex justify-end gap-3 mt-6">
            <button onclick="closeSparepartModal()"
                class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">Cancel</button>
            <button type="button" onclick="applySpareparts()"
                class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">Apply</button>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        let sparepartCount = 0;
        // Ambil data sparepart dari PHP ke JavaScript.
        // Menggunakan keyBy('id') agar objek allSpareparts memiliki ID sparepart sebagai kuncinya,
        // sehingga mudah diakses: allSpareparts[ID_SPAREPART].stock
        const allSpareparts = @json(
            $spareparts->mapWithKeys(function ($sp) {
                return [$sp->id => ['qty' => $sp->qty]];
            }));

        function openSparepartModal() {
            const modal = document.getElementById('sparepartModal');
            modal.classList.remove('hidden');
            // Tambahkan animasi jika diperlukan
            setTimeout(() => {
                modal.querySelector('.transform').classList.remove('scale-0', 'opacity-0');
                modal.querySelector('.transform').classList.add('scale-100', 'opacity-100');
            }, 50);
        }

        function closeSparepartModal() {
            const modal = document.getElementById('sparepartModal');
            // Hapus animasi
            modal.querySelector('.transform').classList.remove('scale-100', 'opacity-100');
            modal.querySelector('.transform').classList.add('scale-0', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        function addSparepartRow() {
            const row = `
                <div class="grid grid-cols-12 gap-2 items-center">
                    <div class="col-span-4">
                        <select name="spareparts[${sparepartCount}][id]"
                            class="w-full text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white sparepart-select focus:ring-purple-500 focus:border-purple-500"
                            onchange="updateSparepartStock(this)">
                            <option value="">-- Choose Sparepart --</option>
                            @foreach ($spareparts as $sp)
                                <option value="{{ $sp->id }}">{{ $sp->item->name ?? '' }} ({{ $sp->item->model ?? '' }}) ({{ $sp->item->brand ?? '' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <input type="text" readonly value="0" class="col-span-2 border rounded-md text-sm px-3 py-2 bg-gray-100 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400" placeholder="Stock" />
                    <input type="number" name="spareparts[${sparepartCount}][qty]" class="col-span-2 border rounded-md text-sm px-3 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-purple-500 focus:border-purple-500" min="1" placeholder="Qty" />
                    <input type="text" name="spareparts[${sparepartCount}][note]" class="col-span-3 border rounded-md text-sm px-3 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-purple-500 focus:border-purple-500" placeholder="Note" />
                    <button type="button" onclick="removeSparepartRow(this)" class="col-span-1 text-red-500 hover:text-red-700 focus:outline-none">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x-circle"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                    </button>
                </div>
            `;
            document.getElementById('sparepartRows').insertAdjacentHTML('beforeend', row);
            sparepartCount++;
        }


        // Fungsi untuk memperbarui nilai stok
        function updateSparepartStock(selectElement) {
            const selectedSparepartId = selectElement.value;
            const stockInput = selectElement.closest('.grid').querySelector('input[type="text"]');

            if (selectedSparepartId && allSpareparts[selectedSparepartId]) {
                stockInput.value = allSpareparts[selectedSparepartId].qty ?? 0;
            } else {
                stockInput.value = '0';
            }
        }


        function removeSparepartRow(button) {
            button.closest('.grid').remove();
        }

        function applySpareparts() {
            const rows = document.querySelectorAll('#sparepartRows > .grid');
            const container = document.getElementById('sparepartsContainer');
            container.innerHTML = '';
            let isValid = true;

            rows.forEach((row, index) => {
                const select = row.querySelector('select');
                const stockInput = row.querySelector('input[type="text"]');
                const qtyInput = row.querySelector('input[type="number"]');
                const noteInput = row.querySelector('input[type="text"]:not([readonly])');

                const selectedId = select.value;
                const stock = parseInt(stockInput.value) || 0;
                const qty = parseInt(qtyInput.value) || 0;

                if (!selectedId) {
                    alert(`Row ${index + 1}: Sparepart must be selected.`);
                    isValid = false;
                    return;
                }

                if (qty <= 0) {
                    alert(`Row ${index + 1}: Quantity must be greater than 0.`);
                    isValid = false;
                    return;
                }

                if (qty > stock) {
                    alert(`Row ${index + 1}: Quantity cannot exceed stock.`);
                    isValid = false;
                    return;
                }

                // Inject ulang hidden inputs
                container.insertAdjacentHTML('beforeend', `
            <input type="hidden" name="spareparts[${index}][id]" value="${selectedId}">
            <input type="hidden" name="spareparts[${index}][qty]" value="${qty}">
            <input type="hidden" name="spareparts[${index}][note]" value="${noteInput.value}">
        `);
            });

            if (isValid) {
                closeSparepartModal();
            }
        }

        function openSparepartModal() {
            const modal = document.getElementById('sparepartModal');
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.querySelector('.transform').classList.remove('scale-0', 'opacity-0');
                modal.querySelector('.transform').classList.add('scale-100', 'opacity-100');
            }, 50);
        }

        function closeSparepartModal() {
            const modal = document.getElementById('sparepartModal');
            modal.querySelector('.transform').classList.remove('scale-100', 'opacity-100');
            modal.querySelector('.transform').classList.add('scale-0', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        document.addEventListener("DOMContentLoaded", function() {
            // Hanya tambahkan baris pertama jika belum ada
            if (document.getElementById('sparepartRows').children.length === 0) {
                addSparepartRow();
            }
        });
    </script>
    <script>
        document.getElementById('attachment').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const imgPreview = document.getElementById('preview-image');
            const videoPreview = document.getElementById('preview-video');
            const previewContainer = document.getElementById('attachment-preview');

            imgPreview.classList.add('hidden');
            videoPreview.classList.add('hidden');

            if (file) {
                const fileType = file.type;

                if (fileType.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        imgPreview.src = event.target.result;
                        imgPreview.classList.remove('hidden');

                        // Viewer.js instance
                        const viewer = new Viewer(imgPreview, {
                            inline: false,
                            toolbar: true,
                            title: false
                        });

                        // Re-init on image reload
                        imgPreview.onload = () => {
                            viewer.update();
                        };
                    };
                    reader.readAsDataURL(file);
                } else if (fileType.startsWith('video/')) {
                    const videoURL = URL.createObjectURL(file);
                    videoPreview.src = videoURL;
                    videoPreview.classList.remove('hidden');
                }
            }
        });
    </script>
@endpush
