<x-app-layout>
    <x-dashboard.sidebar>
        <x-alert-information></x-alert-information>
        <h1 class="mt-5 text-2xl font-bold mb-6">Notification Preferences</h1>
        <div class="mt-5 max-w-6xl mx-auto p-4 bg-white rounded-xl shadow">

            <form method="POST" action="{{ route('notification-preferences.save') }}"
                onsubmit="return confirmAndLoad('Are you sure to save notif permission?')">
                @csrf

                <div class="overflow-x-auto">
                    <div class="max-h-[300px] overflow-y-auto">
                        <table class="w-full border text-sm whitespace-nowrap">
                            <thead class="sticky top-0 z-10 bg-gray-100">
                                <tr>
                                    <th class="px-4 py-2 text-left">Notification Type</th>
                                    @foreach ($roles as $role)
                                        <th class="px-4 py-2 text-center">{{ $role->name }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($notificationTypes as $typeKey => $typeLabel)
                                    <tr class="border-t">
                                        <td class="px-4 py-2">{{ $typeLabel }}</td>

                                        @foreach ($roles as $role)
                                            <td class="px-4 py-2 text-center">
                                                <input type="checkbox" name="preferences[{{ $typeKey }}][]"
                                                    value="{{ $role->id }}"
                                                    {{ in_array($role->id, $savedPreferences[$typeKey] ?? []) ? 'checked' : '' }}>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-6 flex justify-start">
                    <x-buttons.action-button text="Save Preferences" color="purple" type="submit" />
                </div>
            </form>
        </div>
    </x-dashboard.sidebar>
</x-app-layout>
