<div class="w-full overflow-x-auto">
    <div class="min-w-full inline-block align-middle">
        <div class="overflow-hidden shadow sm:rounded-lg" id="transaction-list">
            <table
                class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs sm:text-sm md:text-base lg:text-[15px] text-center text-gray-600 dark:text-gray-300">
                <thead
                    class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-700 dark:text-gray-400 whitespace-nowrap">
                    <tr>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="no">No</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="id">Transaction ID</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="type">Category</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="item">Item</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="qty">QTY</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="supplier">Supplier</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="date">Date</th>
                        <th class="px-4 py-2 md:px-6 md:py-3 cursor-pointer sort" data-sort="created_by">Created By</th>
                        <th class="px-4 py-2 md:px-6 md:py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="list whitespace-nowrap">
                    @forelse ($transactions as $transaction)
                        <tr
                            class="border-b dark:border-gray-700 {{ $loop->odd ? 'bg-white dark:bg-gray-900' : 'bg-gray-50 dark:bg-gray-800' }}">
                            <td class="px-4
                            py-2 md:px-6 md:py-3 no">{{ $loop->iteration }}
                            </td>
                            <td class="px-4 py-2 md:px-6 md:py-3 id">{{ $transaction->id }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 capitalize type">{{ strtolower($transaction->type) }}
                            </td>
                            <td class="px-4 py-2 md:px-6 md:py-3 capitalize item">
                                {{ strtolower($transaction->item->name ?? '-') }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 qty">{{ $transaction->qty }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 capitalize supplier">
                                {{ strtolower($transaction->supplier) }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 date">
                                {{ \Carbon\Carbon::parse($transaction->created_at)->format('d M Y') }}</td>
                            <td class="px-4 py-2 md:px-6 md:py-3 capitalize created_by">
                                {{ strtolower($transaction->user->name) }}</td>
                            <td class="px-2 py-2 md:px-4 md:py-3">
                                <div class="flex flex-col sm:flex-row items-center justify-center gap-1">
                                    <x-buttons.action-button text="Edit" color="blue"
                                        href="{{ route('transactions.edit', $transaction->id) }}"
                                        onclick="showFullScreenLoader();" class="" />

                                    <x-buttons.action-button text="Detail" color="purple"
                                        href="{{ route('transactions.show', $transaction->id) }}"
                                        onclick="showFullScreenLoader();" class="" />
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4 text-xs">No transactions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if ($showPagination)
    {{-- Pagination + Per Page --}}
    <div class="mt-6 px-4 pb-3 flex flex-col sm:flex-row justify-between items-center gap-4">
        <div>
            {{ $transactions->appends(request()->query())->links() }}
        </div>
        <div class="flex items-center gap-4 flex-wrap justify-end">
            <form method="GET" action="{{ route('transactions.index') }}" onsubmit="showFullScreenLoader();">
                <input type="hidden" name="search" value="{{ request('search') }}">
                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                <div class="flex items-center gap-1">
                    <label for="per_page" class="text-sm text-gray-600 dark:text-gray-300">Show</label>
                    <select name="per_page" id="per_page" onchange="this.form.submit()"
                        class="text-sm w-16 px-2 py-1 rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-purple-500">
                        <option value="5" {{ ($perPage ?? 5) == 5 ? 'selected' : '' }}>5</option>
                        <option value="10" {{ ($perPage ?? 5) == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ ($perPage ?? 5) == 20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ ($perPage ?? 5) == 50 ? 'selected' : '' }}>50</option>
                    </select>
                    <span class="text-sm text-gray-600 dark:text-gray-400">per page</span>
                </div>
            </form>
        </div>
    </div>
@endif

@push('scripts')
    <script>
        const transactionList = new List('transaction-list', {
            valueNames: ['no', 'id', 'type', 'item', 'qty', 'sn', 'supplier', 'date', 'created_by']
        });
    </script>
@endpush
