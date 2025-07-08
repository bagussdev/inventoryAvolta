<?php

namespace App\Http\Controllers;

use App\Models\UsedSparepart;
use App\Models\Sparepart;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UsedSparepartsExport;

class UsedSparepartController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $this->authorize('historytransactionsmenu');

        $perPage = $request->input('per_page', 5);
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        $usedsQuery = UsedSparepart::query()
            // Memuat relasi sparepart, itemnya, serta maintenance dan incident untuk reference
            ->with(['sparepart.item', 'maintenance', 'incident'])

            // 1. Filter Pencarian (sesuai kode Anda)
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('sparepart.item', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    })
                        ->orWhere('qty', 'like', "%{$search}%")
                        ->orWhere('note', 'like', "%{$search}%")
                        ->orWhere(function ($q) use ($search) {
                            if (strtolower($search) === 'maintenance') {
                                $q->whereNotNull('maintenance_id');
                            } elseif (strtolower($search) === 'incident') {
                                $q->whereNotNull('incident_id');
                            }
                        })
                        ->orWhere('maintenance_id', 'like', "%{$search}%")
                        ->orWhere('incident_id', 'like', "%{$search}%");
                });
            });

        // --- KEMBALIKAN LOGIKA GATE ISMASTER DI SINI ---
        // 2. Filter Department (jika bukan Master)
        if (!$isMaster) {
            if ($user->department_id) {
                $usedsQuery->whereHas('sparepart.item', function ($q) use ($user) {
                    $q->where('department_id', $user->department_id);
                });
            } else {
                // Jika user tidak punya department, kembalikan query kosong
                $usedsQuery->whereNull('id');
            }
        }

        // --- TAMBAHAN BARU: Filter berdasarkan rentang tanggal ---
        $usedsQuery->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        });

        // 3. Paginate & render
        $useds = $usedsQuery->latest('created_at')
            ->paginate($perPage);

        // appends() untuk mempertahankan semua filter pada pagination link
        $useds->appends(compact('search', 'perPage', 'startDate', 'endDate'));

        // Pass semua variabel ke view
        return view('used_spareparts.index', compact('useds', 'search', 'perPage', 'startDate', 'endDate'));
    }

    /**
     * Export the filtered used spareparts to an Excel file.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        // --- TAMBAHAN BARU: Otorisasi untuk fungsi export ---
        $this->authorize('historytransactionsmenu');

        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        $usedsQuery = UsedSparepart::query()
            ->with(['sparepart.item', 'maintenance', 'incident']);

        // 1. Filter Pencarian (sesuai kode di index)
        if ($search) {
            $usedsQuery->whereHas('sparepart.item', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%");
            })
                ->orWhere('qty', 'like', "%{$search}%")
                ->orWhere('note', 'like', "%{$search}%")
                ->orWhere(function ($q) use ($search) {
                    if (strtolower($search) === 'maintenance') {
                        $q->whereNotNull('maintenance_id');
                    } elseif (strtolower($search) === 'incident') {
                        $q->whereNotNull('incident_id');
                    }
                })
                ->orWhere('maintenance_id', 'like', "%{$search}%")
                ->orWhere('incident_id', 'like', "%{$search}%");
        }

        // --- TAMBAHAN BARU: LOGIKA GATE ISMASTER UNTUK EXPORT ---
        // 2. Filter Department (jika bukan Master)
        if (!$isMaster) {
            if ($user->department_id) {
                $usedsQuery->whereHas('sparepart.item', function ($q) use ($user) {
                    $q->where('department_id', $user->department_id);
                });
            } else {
                $usedsQuery->whereNull('id');
            }
        }

        // 3. Filter Tanggal (sesuai kode di index)
        if ($startDate && $endDate) {
            $usedsQuery->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }

        // Ambil semua data yang sudah difilter (tanpa paginate)
        $filteredUseds = $usedsQuery->latest('created_at')->get();

        // Mengirimkan koleksi data ke class export
        $filename = 'used_spareparts_filtered_' . date('Y-m-d_H-i') . '.xlsx';

        return Excel::download(new UsedSparepartsExport($filteredUseds), $filename);
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
    public function show(string $id)
    {
        //
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
