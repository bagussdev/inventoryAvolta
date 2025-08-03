<div id="{{ $id }}" class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex items-center justify-center"
    onclick="outsideClickClose(event, '{{ $id }}')">

    <div id="{{ $id }}-content"
        class="bg-white dark:bg-gray-800 rounded-xl shadow-lg w-full max-w-md p-6 relative mx-4 sm:mx-0 transform transition-all duration-300 scale-0 opacity-0">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
            <button type="button" onclick="closeModal('{{ $id }}')"
                class="text-gray-400 hover:text-gray-700 dark:hover:text-white hover:bg-gray-200 rounded-full p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form action="{{ $action }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Attachment</label>
                <input type="file" name="attachment" id="attachment-input" accept="image/*,video/*,application/pdf"
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
            </div>

            <div class="mb-4" id="attachment-preview">
                @if (!empty($attachment))
                    @php
                        $url = $attachment; // Pastikan hanya path relatif disimpan di DB
                        $ext = pathinfo($attachment, PATHINFO_EXTENSION);
                    @endphp

                    @if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif']))
                        <img id="preview-img-initial" src="{{ $url }}" alt="Attachment"
                            class="max-h-48 rounded shadow w-full object-contain">
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const img = document.getElementById('preview-img-initial');
                                if (img) {
                                    new Viewer(img, {
                                        toolbar: true,
                                        navbar: false,
                                        title: false
                                    });
                                }
                            });
                        </script>
                    @elseif(in_array(strtolower($ext), ['mp4', 'mov', 'avi']))
                        <video src="{{ $url }}" controls class="rounded shadow w-full max-h-64 mt-2"></video>
                    @else
                        <a href="{{ $url }}" target="_blank" class="text-blue-600 underline">View Current
                            File</a>
                    @endif
                @endif
            </div>


            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                <textarea name="notes" rows="3"
                    class="mt-1 block w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">{{ $notes }}</textarea>
            </div>

            <div class="flex justify-center">
                <x-buttons.action-button type="submit" text="Update" color="purple" />
            </div>
        </form>
    </div>
</div>

@push('scripts')
    <script>
        function openModal(id) {
            const modal = document.getElementById(id);
            const content = document.getElementById(`${id}-content`);
            modal.classList.remove('hidden');
            setTimeout(() => {
                content.classList.remove('scale-0', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeModal(id) {
            const modal = document.getElementById(id);
            const content = document.getElementById(`${id}-content`);
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-0', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        function outsideClickClose(event, id) {
            if (event.target.id === id) {
                closeModal(id);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('attachment-input');
            const preview = document.getElementById('attachment-preview');

            input?.addEventListener('change', function() {
                const file = this.files[0];
                if (!file) return;

                const reader = new FileReader();
                const ext = file.name.split('.').pop().toLowerCase();
                preview.innerHTML = '';

                if (['jpg', 'jpeg', 'png', 'gif'].includes(ext)) {
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'max-h-48 rounded shadow w-full object-contain';
                        img.id = 'preview-img';
                        preview.appendChild(img);

                        new Viewer(img, {
                            toolbar: true,
                            title: false,
                            navbar: false
                        });
                    };
                    reader.readAsDataURL(file);
                } else if (['mp4', 'mov', 'avi'].includes(ext)) {
                    const video = document.createElement('video');
                    video.src = URL.createObjectURL(file);
                    video.controls = true;
                    video.className = 'rounded shadow w-full max-h-64 mt-2';
                    preview.appendChild(video);
                } else {
                    const link = document.createElement('a');
                    link.href = URL.createObjectURL(file);
                    link.target = '_blank';
                    link.className = 'text-blue-600 underline';
                    link.innerText = 'Preview uploaded file';
                    preview.appendChild(link);
                }
            });
        });
    </script>
@endpush
