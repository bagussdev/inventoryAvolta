<x-app-layout>
    <x-dashboard.sidebar>
        <x-alert-information></x-alert-information>

        {{-- Ini adalah section Back Button --}}
        <div class="mt-4">
            <a href="{{ route('maintenances.index') }}" onclick="showFullScreenLoader();"
                class="inline-flex items-center text-gray-700 hover:text-purple-600 dark:text-gray-300 dark:hover:text-white transition duration-150 ease-in-out">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 32 32" class="mr-2">
                    <path fill="#101820"
                        d="M30,29a1,1,0,0,1-.81-.41l-2.12-2.92A18.66,18.66,0,0,0,15,18.25V22a1,1,0,0,1-1.6.8l-12-9a1,1,0,0,1,0-1.6l12-9A1,1,0,0,1,15,4V8.24A19,19,0,0,1,31,27v1a1,1,0,0,1-.69.95A1.12,1.12,0,0,1,30,29ZM14,16.11h.1A20.68,20.68,0,0,1,28.69,24.5l.16.21a17,17,0,0,0-15-14.6,1,1,0,0,1-.89-1V6L3.67,13,13,20V17.11a1,1,0,0,1,.33-.74A1,1,0,0,1,14,16.11Z" />
                </svg>
                <span class="text-sm font-medium">Back</span>
            </a>
        </div>

        {{-- Judul Halaman --}}
        <div class="flex justify-between items-center mt-4 w-full max-w-full">
            <h2 class="font-bold text-xl sm:text-2xl">
                Confirm Maintenance
            </h2>
        </div>

        <hr class="h-[3px] my-4 bg-gray-200 border-0 dark:bg-gray-700 w-full">

        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md text-sm text-gray-700 dark:text-gray-300">

            <h3 class="font-bold text-lg mb-4 text-gray-800 dark:text-gray-200">Maintenance Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-center">
                    <div class="w-40 font-medium">Maintenance Id</div>
                    <div class="flex-1">: {{ $maintenance->id ?? '-' }}</div>
                </div>
                <div class="flex items-center">
                    <div class="w-40 font-medium">Store Location</div>
                    <div class="flex-1">: {{ $maintenance->equipment->store->name ?? '-' }}</div>
                </div>

                <div class="flex items-center">
                    <div class="w-40 font-medium">Item Maintenance</div>
                    <div class="flex-1">: {{ $maintenance->equipment->item->name ?? '-' }}</div>
                </div>
                <div class="flex items-center">
                    <div class="w-40 font-medium">User In Charge</div>
                    <div class="flex-1">: {{ $maintenance->staff->name ?? 'N/A' }} /
                        {{ $maintenance->staff->role->name ?? 'N/A' }}</div>
                </div>

                <div class="flex items-center">
                    <div class="w-40 font-medium">Since</div>
                    <div class="flex-1">: {{ \Carbon\Carbon::parse($maintenance->created_at)->format('Y-m-d') }}</div>
                </div>
                <div class="flex items-center">
                    <div class="w-40 font-medium">Schedule</div>
                    <div class="flex-1">: {{ \Carbon\Carbon::parse($maintenance->maintenance_date)->format('Y-m-d') }}
                    </div>
                </div>

                <div class="flex items-center">
                    <div class="w-40 font-medium">Confirm By</div>
                    <div class="flex-1">: {{ $maintenance->confirm->name ?? 'N/A' }}</div>
                </div>
                <div class="flex items-center">
                    <div class="w-40 font-medium">Status</div>
                    <div class="flex-1">
                        @php
                            $status = strtolower($maintenance->status);
                            $color = match ($status) {
                                'completed' => 'bg-green-100 text-green-800',
                                'maintenance' => 'bg-red-100 text-red-600',
                                'in progress' => 'bg-blue-100 text-blue-800',
                                'resolved' => 'bg-purple-100 text-purple-800',
                                'not due' => 'bg-gray-100 text-gray-600',
                                default => 'bg-yellow-100 text-yellow-800',
                            };
                        @endphp
                        : <span class="px-3 py-1 text-xs font-medium rounded-md {{ $color }}">
                            {{ ucfirst($maintenance->status) }}
                        </span>
                    </div>
                </div>
            </div>

            <form action="{{ route('maintenances.submitConfirm', $maintenance->id) }}" method="POST"
                enctype="multipart/form-data"
                onsubmit="return confirmAndLoad('Are you sure you want to confirm this maintenance?')">
                @csrf

                <div class="mb-4 mt-4">
                    <label for="attachment"
                        class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Attachment
                        (photo/video)</label>
                    <input type="file" name="attachment" id="attachment" accept="image/*,video/*"
                        class="block w-full text-sm text-gray-700 dark:text-gray-300 file:mr-4 file:py-2 file:px-4
                        file:rounded-md file:border-0 file:text-sm file:font-semibold
                        file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100
                        dark:file:bg-gray-700 dark:file:text-gray-200 dark:hover:file:bg-gray-600
                        border border-gray-300 rounded-md shadow-sm"
                        required>
                </div>
                <div id="attachment-preview" class="mt-4 mb-1">
                    <img id="preview-image" class="max-h-48 hidden" />
                    <video id="preview-video" class="max-h-48 hidden" controls></video>
                </div>

                <div class="mb-6">
                    <label for="notes"
                        class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Notes</label>
                    <textarea name="notes" id="notes" rows="3" required
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm resize-y
                        focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                </div>

                <div class="flex justify-center mb-6">
                    <button type="button" onclick="openSparepartModal()"
                        class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                        + Add Spareparts Used
                    </button>
                </div>
                <div id="sparepartsContainer" class="hidden"></div>
                <div class="mt-6 mb-4 text-center">
                    <button type="submit"
                        class="bg-green-600 text-white px-8 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                        Confirm
                    </button>
                </div>

            </form>
            {{-- Akhir Form Konfirmasi Maintenance --}}

        </div> {{-- Akhir dari satu box besar --}}

        <div id="sparepartModal"
            class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex justify-center items-center p-4">
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
    </x-dashboard.sidebar>
</x-app-layout>
