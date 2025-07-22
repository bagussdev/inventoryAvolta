<div id="uploadModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg w-full max-w-3xl max-h-[90vh] overflow-y-auto transform transition-all duration-300 scale-95 opacity-0"
        x-data="{
            previewData: [],
            showModal: false,
            handleFile(event) {
                const file = event.target.files[0];
                const reader = new FileReader();
                reader.onload = (e) => {
                    const data = new Uint8Array(e.target.result);
                    const workbook = XLSX.read(data, { type: 'array' });
                    const sheetName = workbook.SheetNames[0];
                    const sheet = workbook.Sheets[sheetName];
                    const json = XLSX.utils.sheet_to_json(sheet, { header: 1 });
                    this.previewData = json;
                };
                reader.readAsArrayBuffer(file);
            },
            submit() {
                showFullScreenLoader();
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route('items.import.save') }}';
        
                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = '{{ csrf_token() }}';
                form.appendChild(csrf);
        
                const jsonInput = document.createElement('input');
                jsonInput.type = 'hidden';
                jsonInput.name = 'json_data';
                jsonInput.value = JSON.stringify(this.previewData);
                form.appendChild(jsonInput);
        
                document.body.appendChild(form);
                form.submit();
            }
        }" x-init="$el.classList.replace('scale-95', 'scale-100');
        $el.classList.replace('opacity-0', 'opacity-100')">

        <h2 class="text-lg font-semibold mb-4 text-gray-800 dark:text-white">Upload Excel Outlet</h2>

        <input type="file" accept=".xlsx,.xls" @change="handleFile"
            class="mb-4 block w-full border rounded px-3 py-2 text-sm" />

        <!-- Table Preview -->
        <template x-if="previewData.length">
            <div class="overflow-auto max-h-96">
                <table class="min-w-full divide-y divide-gray-300 dark:divide-gray-600 text-sm">
                    <thead class="bg-gray-100 dark:bg-gray-700">
                        <tr>
                            <template x-for="(cell, index) in previewData[0]">
                                <th class="px-4 py-2 text-left" x-text="cell"></th>
                            </template>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                        <template x-for="(row, rIndex) in previewData.slice(1)" :key="rIndex">
                            <tr class="border-t">
                                <template x-for="cell in row">
                                    <td class="px-4 py-2 border whitespace-nowrap text-xs sm:text-sm" x-text="cell">
                                    </td>
                                </template>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </template>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row justify-end gap-3 mt-4">
            <a href="{{ route('items.template.download') }}"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm w-full sm:w-auto text-center">
                Download Template
            </a>

            <button type="button" onclick="document.getElementById('uploadModal').classList.add('hidden')"
                class="px-4 py-2 bg-gray-400 hover:bg-gray-500 text-white rounded-md text-sm w-full sm:w-auto">
                Cancel
            </button>

            <button x-show="previewData.length" @click="submit"
                class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-md text-sm w-full sm:w-auto">
                Simpan ke Database
            </button>
        </div>
    </div>
</div>
