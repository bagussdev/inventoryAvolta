<x-app-layout>
    <x-dashboard.sidebar>
        <x-alert-information></x-alert-information>
        <div x-data="permissionManager()" class="mt-5 sm:mt-10 max-w-5xl mx-auto">
            <h1 class="text-2xl font-bold mb-6">Role Permissions</h1>

            <!-- Tabel Role -->
            <table class="w-full border text-sm text-left">
                <thead class="bg-gray-100 text-gray-700 text-center">
                    <tr>
                        <th class="py-2 px-4">Role</th>
                        <th class="py-2 px-4">Permissions</th>
                        <th class="py-2 px-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y">
                    @foreach ($roles as $role)
                        <tr>
                            <td class="py-2 px-4 font-semibold">{{ $role->name }}</td>
                            <td class="py-2 px-4 relative">
                                @php
                                    $labels = $role->permissions->pluck('label');
                                    $firstFew = $labels->take(5);
                                    $hasMore = $labels->count() > 5;
                                @endphp

                                <div class="flex flex-wrap gap-1">
                                    @foreach ($firstFew as $label)
                                        <span
                                            class="bg-purple-100 text-purple-700 text-xs px-2 py-1 rounded">{{ $label }}</span>
                                    @endforeach

                                    @if ($hasMore)
                                        <div x-data="{ show: false }" class="relative">
                                            <button @click="show = !show"
                                                class="text-xs bg-gray-200 hover:bg-gray-300 text-gray-700 px-2 py-1 rounded">
                                                ...
                                            </button>

                                            <div x-show="show" @click.outside="show = false" x-cloak
                                                class="absolute z-50 bg-white border rounded shadow-md mt-2 p-2 max-w-xs max-h-48 overflow-y-auto">
                                                @foreach ($labels->slice(3) as $label)
                                                    <span
                                                        class="block text-sm text-gray-700 border-b py-1 last:border-0">{{ $label }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </td>

                            <td class="py-2 px-4 text-center">
                                <button @click="openModal({{ $role->id }})"
                                    class="px-3 py-1 bg-purple-600 hover:bg-purple-700 text-white rounded text-sm">
                                    Edit
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Modal -->
            <div x-show="open" x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                <div @click.outside="open = false"
                    class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg w-full max-w-2xl max-h-[90vh] overflow-y-auto">

                    <h2 class="text-lg font-semibold mb-4">Edit Permissions</h2>

                    <!-- TomSelect Multi -->
                    <select id="permissionSelect" x-ref="select" name="permissions[]" multiple
                        class="w-full mb-4 rounded border-gray-300 text-sm">
                        @foreach ($permissions as $permission)
                            <option value="{{ $permission->name }}">{{ $permission->label }}</option>
                        @endforeach
                    </select>

                    <!-- List Permissions -->
                    <div class="grid grid-cols-2 gap-2">
                        @foreach ($permissions as $permission)
                            <div class="cursor-pointer px-3 py-2 rounded border border-gray-300 dark:border-gray-600 hover:bg-purple-100 dark:hover:bg-purple-700"
                                :class="{
                                    'bg-purple-200 dark:bg-purple-700 text-white': selected.includes(
                                        '{{ $permission->name }}')
                                }"
                                @click="togglePermission('{{ $permission->name }}')">
                                {{ $permission->label }}
                            </div>
                        @endforeach
                    </div>

                    <!-- Submit -->
                    <form :action="`{{ url('/permissions') }}/${selectedRole}`" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Permissions input hidden (TomSelect binding here) -->
                        <input type="hidden" name="permissions" :value="JSON.stringify(selected)">

                        <div class="flex justify-end gap-2 mt-6">
                            <button type="button" @click="open = false" onsubmit="showFullScreenLoader();"
                                class="px-4 py-2 bg-gray-400 hover:bg-gray-500 text-white rounded-md text-sm">
                                Cancel
                            </button>
                            <button type="submit" onsubmit="showFullScreenLoader();"
                                class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-md text-sm">
                                Save
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        <!-- Script -->
        <script>
            function permissionManager() {
                return {
                    open: false,
                    selectedRole: null,
                    roles: @json($roles),
                    permissions: @json($permissions),
                    selected: [],
                    tom: null,

                    openModal(id) {
                        this.selectedRole = id;
                        const role = this.roles.find(r => r.id === id);
                        this.selected = role.permissions.map(p => p.name);
                        this.open = true;

                        this.$nextTick(() => {
                            if (this.tom) this.tom.destroy();

                            this.tom = new TomSelect(this.$refs.select, {
                                persist: false,
                                create: false,
                                items: this.selected,
                                plugins: ['remove_button'],
                                render: {
                                    item: function(data, escape) {
                                        return `<div class="item tom-selected-item">
                        ${escape(data.text)}`;
                                    }
                                },
                                onItemAdd: (value) => {
                                    if (!this.selected.includes(value)) this.selected.push(value);
                                },
                                onItemRemove: (value) => {
                                    this.selected = this.selected.filter(v => v !== value);
                                },
                            });

                        });
                    },

                    togglePermission(value) {
                        if (this.selected.includes(value)) {
                            this.selected = this.selected.filter(v => v !== value);
                            this.tom.removeItem(value);
                        } else {
                            this.selected.push(value);
                            this.tom.addItem(value);
                        }
                    },
                }
            }
        </script>

        <!-- Tambahkan CDN Tom Select -->
        {{-- @push('scripts')
            <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
            <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
        @endpush --}}
    </x-dashboard.sidebar>
</x-app-layout>
