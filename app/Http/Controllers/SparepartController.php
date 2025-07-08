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
            // Pastikan user punya department_id untuk difilter
            if ($user->department_id) {
                // Filter spareparts berdasarkan departemen dari item-nya
                $sparepartsQuery->whereHas('item', function ($query) use ($user) {
                    $query->where('department_id', $user->department_id);
                });
            } else {
                // Jika user tidak punya departemen, tampilkan data kosong
                // Ini akan mengembalikan query yang tidak memiliki hasil
                $sparepartsQuery->whereNull('id');
            }
        }

        // 6. Urutkan berdasarkan yang terbaru diupdate
        $spareparts = $sparepartsQuery
            ->orderBy('updated_at', 'desc')
            ->paginate($perPage)
            ->appends(compact('search', 'perPage'));

        // 7. Kirim data ke view
        return view('spareparts.index', compact('spareparts', 'search', 'perPage'));
    }

    /**
     * Show the form for creating a new resource.
     */
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
        // Riwayat transaksi masuk (in)
        $transactionIns = Transaction::where('items_id', $sparepart->items_id)
            ->select('qty', 'notes', 'created_at', 'id')
            ->get()
            ->map(function ($trx) {
                return [
                    'date' => $trx->created_at->format('Y-m-d'),
                    'type' => 'in',
                    'qty' => $trx->qty,
                    'note' => $trx->notes ?? '-',
                    'reference' => 'Transaction #' . $trx->id,
                    'ref_type' => 'transaction',
                    'ref_id' => $trx->id,
                ];
            });

        // Riwayat pemakaian sparepart (out)
        $usedOuts = UsedSparepart::whereHas('sparepart', function ($query) use ($sparepart) {
            $query->where('items_id', $sparepart->items_id);
        })->get()
            ->map(function ($used) {
                $isMaintenance = !is_null($used->maintenance_id);
                $refType = $isMaintenance ? 'maintenance' : 'incident';
                $refId = $isMaintenance ? $used->maintenance_id : $used->incident_id;

                return [
                    'date' => $used->created_at->format('Y-m-d'),
                    'type' => 'out',
                    'qty' => $used->qty,
                    'note' => $used->note ?? '-',
                    'reference' => ucfirst($refType) . ' #' . $refId,
                    'ref_type' => $refType,
                    'ref_id' => $refId,
                ];
            });

        // Gabungkan & urutkan berdasarkan tanggal
        $history = collect($transactionIns)
            ->merge($usedOuts)
            ->sortByDesc('date')
            ->values();

        return view('spareparts.show', compact('sparepart', 'history'));
    }



    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
