<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sparepart;
use App\Models\UsedSparepart;
use App\Models\Transaction;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class SparepartController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        // 1. Otorisasi
        $this->authorize('sparepartsmenu');

        // 2. Ambil parameter dari request
        $search = $request->search;
        $perPage = $request->input('per_page', 5);

        // 3. Dapatkan user yang sedang login dan cek role-nya
        $user = Auth::user();
        $isMaster = Gate::allows('isMaster'); // Cek Gate 'isMaster'

        // 4. Mulai query
        $sparepartsQuery = Sparepart::query()
            ->with(['item.department', 'transaction', 'usedSpareparts']) // Eager load relasi item dan department-nya
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    // Filter langsung dari tabel spareparts
                    $q->where('status', 'like', "%{$search}%")
                        ->orWhere('qty', 'like', "%{$search}%")

                        // Filter dari relasi 'transaction'
                        ->orWhereHas('transaction', function ($sub) use ($search) {
                            $sub->where('supplier', 'like', "%{$search}%");
                        })

                        // Filter dari relasi 'item'
                        ->orWhereHas('item', function ($sub) use ($search) {
                            $sub->where('name', 'like', "%{$search}%")
                                ->orWhere('brand', 'like', "%{$search}%")
                                ->orWhere('model', 'like', "%{$search}%");
                        });
                });
            });

        // 5. Terapkan filter departemen jika user BUKAN Master
        if (!$isMaster) {
            if ($user->role_id === 5) {
                abort(403, 'You do not have permission to view spareparts data.');
            } elseif (in_array($user->role_id, [2, 3, 4])) {
                if ($user->department_id) {
                    $sparepartsQuery->whereHas('item', function ($query) use ($user) {
                        $query->where('department_id', $user->department_id);
                    });
                } else {
                    abort(403, 'Your department is not registered.');
                }
            }
        }

        // 6. Urutkan berdasarkan yang terbaru diupdate
        $isFiltered = $search;
        if ($perPage === 'all' || $isFiltered) {
            $spareparts = $sparepartsQuery
                ->orderBy('updated_at', 'desc')
                ->get();
        } else {
            $perPage = (int) ($perPage ?: 5);
            $spareparts = $sparepartsQuery
                ->orderBy('updated_at', 'desc')
                ->paginate($perPage)
                ->appends($request->query());
        }

        // 7. Kirim data ke view
        return view('spareparts.index', compact('spareparts', 'search', 'perPage'));
    }
    public function tbody(Request $request)
    {
        $search = $request->search;
        $perPage = $request->input('per_page', 5);
        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        $sparepartsQuery = Sparepart::query()
            ->with(['item.department', 'transaction', 'usedSpareparts'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('status', 'like', "%{$search}%")
                        ->orWhere('qty', 'like', "%{$search}%")
                        ->orWhereHas('transaction', fn($sub) => $sub->where('supplier', 'like', "%{$search}%"))
                        ->orWhereHas('item', fn($sub) => $sub
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('brand', 'like', "%{$search}%")
                            ->orWhere('model', 'like', "%{$search}%"));
                });
            });

        if (!$isMaster) {
            if ($user->role_id === 5) {
                abort(403, 'You do not have permission to view spareparts data.');
            } elseif (in_array($user->role_id, [2, 3, 4])) {
                if ($user->department_id) {
                    $sparepartsQuery->whereHas('item', function ($query) use ($user) {
                        $query->where('department_id', $user->department_id);
                    });
                } else {
                    abort(403, 'Your department is not registered.');
                }
            }
        }

        $isFiltered = $search;
        if ($perPage === 'all' || $isFiltered) {
            $spareparts = $sparepartsQuery
                ->orderBy('updated_at', 'desc')
                ->get();
        } else {
            $perPage = (int) ($perPage ?: 5);
            $spareparts = $sparepartsQuery
                ->orderBy('updated_at', 'desc')
                ->paginate($perPage)
                ->appends($request->query());
        }

        return view('partials.spareparts-tbody', compact('spareparts'))->render();
    }

    public function lastUpdated(Request $request)
    {
        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        $query = Sparepart::query();

        if (!$isMaster && $user->department_id) {
            $query->whereHas('item', function ($q) use ($user) {
                $q->where('department_id', $user->department_id);
            });
        }

        $lastUpdated = $query->max('updated_at');

        return response()->json(['last_updated' => $lastUpdated]);
    }

    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    // public function show($id)
    // {
    //     // Ambil data sparepart beserta item-nya
    //     $sparepart = Sparepart::with('item')->findOrFail($id);

    //     // Ambil semua transaksi yang berkaitan dengan items_id
    //     $transactions = Transaction::where('items_id', $sparepart->items_id)
    //         ->where('type', 'sparepart')
    //         ->orderByDesc('created_at')
    //         ->get();

    //     // Hitung jumlah pemakaian dari tabel used_spareparts
    //     $usedQty = UsedSparepart::where('spareparts_id', $sparepart->id)->sum('qty');

    //     // Hitung total qty masuk dari transaksi
    //     $totalQty = $transactions->sum('qty');

    //     // Hitung stok saat ini
    //     $stock = $totalQty - $usedQty;

    //     // Tentukan status berdasarkan stok
    //     $sparepart->qty = $stock;

    //     // Update status dan qty di database (jika ingin disimpan)
    //     $sparepart->save();

    //     return view('spareparts.show', compact('sparepart', 'transactions', 'stock'));
    // }

    public function show(Sparepart $sparepart)
    {
        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        // Cek otorisasi akses
        if (!$isMaster) {
            if ($user->role_id === 5) {
                abort(403, 'Unauthorized: User is not allowed to access sparepart detail.');
            }

            if (in_array($user->role_id, [2, 3, 4])) {
                if (!$sparepart->item || $sparepart->item->department_id !== $user->department_id) {
                    abort(403, 'Unauthorized: This sparepart is not under your department.');
                }
            }
        }
        // Ambil semua transaksi masuk dari tabel transactions
        $transactionIns = Transaction::where('items_id', $sparepart->items_id)
            ->select('qty', 'notes', 'created_at', 'id')
            ->get()
            ->map(function ($trx) {
                return [
                    'date' => $trx->created_at->format('Y-m-d'),
                    'type' => 'in',
                    'qty' => $trx->qty,
                    'note' => $trx->notes ?? '-',
                    'reference' => 'TRX' . str_pad($trx->id, 5, '0', STR_PAD_LEFT),
                    'ref_type' => 'transaction',
                    'ref_id' => $trx->id,
                ];
            });

        // Ambil transaksi keluar dari used_spareparts
        $usedOuts = UsedSparepart::whereHas('sparepart', function ($query) use ($sparepart) {
            $query->where('items_id', $sparepart->items_id);
        })
            ->with(['maintenance', 'incident', 'request']) // eager load relasi
            ->get()
            ->map(function ($used) {
                // Tentukan tipe referensi
                $refType = null;
                $refId = null;
                $reference = '-';

                if ($used->maintenance_id) {
                    $refType = 'maintenance';
                    $refId = $used->maintenance_id;
                    $reference = 'MNT' . str_pad($refId, 5, '0', STR_PAD_LEFT);
                } elseif ($used->incident_id) {
                    $refType = 'incident';
                    $refId = $used->incident_id;
                    $reference = $used->incident?->unique_id ?? 'INC-' . str_pad($refId, 4, '0', STR_PAD_LEFT);
                } elseif ($used->request_id) {
                    $refType = 'request';
                    $refId = $used->request_id;
                    $reference = $used->request?->unique_id ?? 'REQ-' . str_pad($refId, 4, '0', STR_PAD_LEFT);
                }

                return [
                    'date' => $used->created_at->format('Y-m-d'),
                    'type' => 'out',
                    'qty' => $used->qty,
                    'note' => $used->note ?? '-',
                    'reference' => $reference,
                    'ref_type' => $refType,
                    'ref_id' => $refId,
                ];
            });

        $latestPhotoTransaction = Transaction::where('items_id', $sparepart->items_id)
            ->whereNotNull('photoitems')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->first();

        // Gabungkan dan urutkan berdasarkan tanggal terbaru
        $history = collect($transactionIns)
            ->merge($usedOuts)
            ->sortByDesc('date')
            ->values();
        $totalIn = $history->where('type', 'in')->sum('qty');
        $totalOut = $history->where('type', 'out')->sum('qty');
        $totalStock = $totalIn - $totalOut;
        return view('spareparts.show', compact('sparepart', 'history', 'totalIn', 'totalOut', 'totalStock', 'latestPhotoTransaction'));
    }
}
