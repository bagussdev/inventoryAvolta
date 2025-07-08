@props(['data'])
<div class="relative overflow-x-auto shadow-md sm:rounded-lg">
    <table class="min-w-full text-sm text-left text-gray-500 dark:text-gray-400 text-center">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr>
                <th class="px-4 py-3">No</th>
                <th class="px-4 py-3">Request ID</th>
                <th class="px-4 py-3">Reported By</th>
                <th class="px-4 py-3">Item</th>
                <th class="px-4 py-3">Qty</th>
                <th class="px-4 py-3">Location</th>
                <th class="px-4 py-3">Date</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $request)
                <tr
                    class="border-b dark:border-gray-700 {{ $loop->odd ? 'bg-white dark:bg-gray-900' : 'bg-gray-50 dark:bg-gray-800' }}">
                    <td class="px-4 py-3">{{ $loop->iteration }}</td>
                    <td class="px-4 py-3">{{ $request->unique_id }}</td>
                    <td class="px-4 py-3">{{ $request->user->name ?? '-' }}</td>
                    <td class="px-4 py-3">{{ $request->item_request }}</td>
                    <td class="px-4 py-3">{{ $request->qty ?? '-' }}</td>
                    <td class="px-4 py-3">{{ $request->store->site_code ?? '-' }}</td>
                    <td class="px-4 py-3">{{ \Carbon\Carbon::parse($request->created_at)->format('d M Y') }}</td>
                    <td class="px-4 py-3">
                        @php
                            $status = strtolower($request->status);
                            $color = match ($status) {
                                'resolved', 'completed' => 'bg-green-100 text-green-800',
                                'in progress' => 'bg-blue-100 text-blue-800',
                                'pending' => 'bg-red-100 text-red-800',
                                'waiting' => 'bg-yellow-100 text-yellow-600',
                                default => 'bg-gray-100 text-gray-600',
                            };
                        @endphp
                        <span class="inline-block px-2 py-1 text-xs font-semibold rounded {{ $color }}">
                            {{ ucfirst($request->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <x-buttons.action-button text="Detail" color="purple"
                            href="{{ route('requests.show', $request->id) }}" onclick="showFullScreenLoader();" />
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="py-4 italic text-gray-500">No requests found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
