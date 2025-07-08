<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Store;
use App\Models\Transaction;
use App\Models\Sparepart;
use App\Models\Equipment;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TransactionsExport;

class TransactionController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $this->authorize('historytransactionsmenu');

        $perPage = $request->input('per_page', 5);
        $search = $request->input('search');
        // --- TAMBAHAN BARU: Ambil input filter tanggal ---
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        $transactionsQuery = Transaction::with(['item', 'user']);

        // 1. Filter Pencarian (sesuai kode Anda)
        $transactionsQuery->when($search, function ($query) use ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('serial_number', 'like', "%{$search}%")
                    ->orWhere('supplier', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%")
                    ->orWhereHas('item', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%")
                            ->orWhere('brand', 'like', "%{$search}%")
                            ->orWhere('model', 'like', "%{$search}%");
                    });
            });
        });

        // 2. Filter Department (sesuai kode Anda)
        if (!$isMaster) {
            if ($user->department_id) {
                $transactionsQuery->whereHas('item', function ($q) use ($user) {
                    $q->where('department_id', $user->department_id);
                });
            } else {
                // Jika user tidak punya department, kembalikan query kosong
                $transactionsQuery->whereNull('id');
            }
        }

        // --- TAMBAHAN BARU: Filter berdasarkan rentang tanggal ---
        $transactionsQuery->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
            // Menggunakan whereBetween untuk rentang tanggal
            $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        });

        // 3. Paginate & render
        $transactions = $transactionsQuery->latest()
            ->paginate($perPage)
            // --- PERUBAHAN DI SINI: appends() untuk mempertahankan semua filter ---
            ->appends(compact('search', 'perPage', 'startDate', 'endDate'));

        // 4. Pass semua variabel ke view
        // --- PERUBAHAN DI SINI: compact() untuk semua variabel filter ---
        return view('transactions.index', compact('transactions', 'search', 'perPage', 'startDate', 'endDate'));
    }
    public function export(Request $request)
    {
        // Pastikan pengguna memiliki izin untuk melihat riwayat transaksi
        $this->authorize('historytransactionsmenu');

        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        // --- QUERY SAMA DENGAN INDEX, TANPA PAGINATE ---
        $query = Transaction::with(['item', 'user']);

        // Filter Pencarian
        $query->when($search, function ($q) use ($search) {
            $q->where('serial_number', 'like', "%{$search}%")
                ->orWhere('supplier', 'like', "%{$search}%")
                ->orWhere('id', 'like', "%{$search}%")
                ->orWhereHas('item', function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%");
                });
        });

        // Filter Department
        if (!$isMaster) {
            if ($user->department_id) {
                $query->whereHas('item', function ($q) use ($user) {
                    $q->where('department_id', $user->department_id);
                });
            } else {
                $query->whereNull('id');
            }
        }

        // Filter Tanggal
        $query->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
            $q->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        });

        // Ambil semua data yang sudah difilter
        $filteredTransactions = $query->get();

        // Mengirimkan koleksi data ke class export dan mengunduhnya
        return Excel::download(new TransactionsExport($filteredTransactions), 'transactions_export_' . date('Y-m-d_Hi') . '.xlsx');
    }


    public function create()
    {
        $this->authorize('historytransactionsmenu');

        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        // Ambil item sesuai departemen
        $items = Item::query()
            ->when(!$isMaster, function ($query) use ($user) {
                if ($user->department_id) {
                    $query->where('department_id', $user->department_id);
                } else {
                    // Jika user tidak punya departemen, kosongkan data
                    $query->whereNull('id');
                }
            })
            ->get();

        return view('transactions.create', compact('items'));
    }


    public function store(Request $request)
    {
        $this->authorize('historytransactionsmenu');

        $validated = $request->validate([
            'items_id' => 'required|exists:items,id',
            'serial_number' => 'nullable|string|max:255|required_if:type,equipment',
            'qty' => 'required|integer|min:1',
            'photoitems' => 'required|image|mimes:jpeg,jpg,png,webp|max:2048',
            'attachmentfile' => 'required|file|mimes:pdf,jpeg,jpg,png,webp|max:4096',
            'notes' => 'nullable|string|max:1000',
            'supplier' => 'required|string|max:255',
            'type' => 'required|in:equipment,sparepart',
        ], [
            'serial_number.required_if' => 'Field S/N wajib diisi jika tipe adalah equipment.',
            'photoitems.required' => 'Foto barang wajib diunggah.',
            'photoitems.image' => 'File foto harus berupa gambar.',
            'photoitems.max' => 'Ukuran file foto maksimal 2MB.',
            'attachmentfile.required' => 'Invoice atau surat wajib diunggah.',
            'attachmentfile.max' => 'Ukuran file lampiran maksimal 4MB.',
        ]);

        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        if (!$isMaster) {
            $item = Item::find($request->items_id);

            if (!$item || $item->department_id !== $user->department_id) {
                return back()->with('error', 'Anda tidak memiliki izin untuk melakukan transaksi pada item ini.')->withInput();
            }
        }

        try {
            // Simpan file
            $validated['photoitems'] = $request->file('photoitems')->store('transaction_photos', 'public');
            $validated['attachmentfile'] = $request->file('attachmentfile')->store('transaction_attachments', 'public');
            $validated['users_id'] = Auth::id();
            // Simpan transaksi
            $transaction = Transaction::create($validated);

            $storageStore = Store::where('name', 'Storage Center')->first();
            if (!$storageStore) {
                return back()->with('error', 'Lokasi Storage Center belum tersedia di database.')->withInput();
            }

            if ($validated['type'] === 'equipment') {
                Equipment::create([
                    'items_id' => $transaction->items_id,
                    'transactions_id' => $transaction->id,
                    'serial_number' => $validated['serial_number'],
                    'location' => $storageStore->id,
                    'status' => 'available'
                ]);
            } else {
                $sparepart = Sparepart::firstOrNew([
                    'items_id' => $validated['items_id']
                ]);

                $sparepart->transactions_id = $transaction->id;

                $sparepart->qty = ($sparepart->qty ?? 0) + $validated['qty'];

                // Update status
                if ($sparepart->qty == 0) {
                    $sparepart->status = 'empty';
                } elseif ($sparepart->qty < 5) {
                    $sparepart->status = 'low';
                } else {
                    $sparepart->status = 'available';
                }

                $sparepart->save();
            }

            return redirect()->route('transactions.index')->with('success', 'Transaction berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $this->authorize('historytransactionsmenu');

        $transaction = Transaction::with('item')->findOrFail($id);

        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        // 🔐 Cek: hanya boleh edit transaksi yang berasal dari departemen sendiri
        if (!$isMaster && $transaction->item->department_id !== $user->department_id) {
            abort(403, 'Anda tidak memiliki izin untuk mengedit transaksi ini.');
        }

        // 🔄 Ambil daftar item untuk dropdown (sesuai departemen)
        $items = Item::query()
            ->when(!$isMaster, function ($query) use ($user) {
                $query->where('department_id', $user->department_id);
            })
            ->get();

        return view('transactions.edit', compact('transaction', 'items'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize('historytransactionsmenu');

        $transaction = Transaction::findOrFail($id);

        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        // 🔐 Cek akses departemen
        if (!$isMaster && $transaction->item->department_id !== $user->department_id) {
            abort(403, 'Anda tidak memiliki izin untuk mengubah transaksi ini.');
        }

        $validated = $request->validate([
            'serial_number' => 'nullable|string|max:255|required_if:type,equipment',
            'qty' => 'required|numeric|min:1',
            'photoitems' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
            'attachmentfile' => 'nullable|file|mimes:pdf,jpeg,jpg,png,webp|max:4096',
            'notes' => 'nullable|string|max:1000',
            'supplier' => 'required|string|max:255',
        ], [
            'serial_number.required_if' => 'Field S/N wajib diisi jika tipe adalah equipment.',
        ]);

        try {
            // Simpan file baru jika ada
            if ($request->hasFile('photoitems')) {
                $validated['photoitems'] = $request->file('photoitems')->store('transaction_photos', 'public');
            } else {
                unset($validated['photoitems']);
            }

            if ($request->hasFile('attachmentfile')) {
                $validated['attachmentfile'] = $request->file('attachmentfile')->store('transaction_attachments', 'public');
            } else {
                unset($validated['attachmentfile']);
            }

            // Update transaksi
            $transaction->update($validated);

            // Jika tipe equipment, update juga equipment terkait
            if ($transaction->type === 'equipment') {
                $equipment = Equipment::where('transactions_id', $transaction->id)->first();

                if ($equipment) {
                    $equipment->update([
                        'serial_number' => $validated['serial_number'],
                    ]);
                }
            }

            return redirect()->route('transactions.index')->with('success', 'Transaksi dan data equipment berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui: ' . $e->getMessage())->withInput();
        }
    }
    public function show($id)
    {
        $transaction = Transaction::with('item')->findOrFail($id);
        return view('transactions.show', compact('transaction'));
    }
    public function downloadTemplate()
    {
        return response()->download(storage_path('app/public/templates/transactions_template.xlsx'));
    }
    // public function importSave(Request $request)
    // {
    //     $request->validate([
    //         'json_data' => 'required|json',
    //         'compressed_file' => 'required|file|mimetypes:application/zip,application/x-zip-compressed,application/x-rar-compressed,application/x-7z-compressed|max:51200', // max 50MB
    //     ], [
    //         'compressed_file.mimetypes' => 'Only .zip, .rar, or .7z files are allowed.'
    //     ]);

    //     $jsonData = json_decode($request->json_data, true);
    //     $headers = array_map('strtolower', $jsonData[0] ?? []);
    //     unset($jsonData[0]);

    //     $requiredColumns = ['items_id', 'qty', 'supplier', 'type', 'photoitems', 'attachmentfile'];
    //     foreach ($requiredColumns as $column) {
    //         if (!in_array($column, $headers)) {
    //             return redirect()->route('transactions.index')
    //                 ->with('error', "Missing required column: $column");
    //         }
    //     }

    //     $missingItems = [];
    //     foreach ($jsonData as $row) {
    //         $rowAssoc = array_combine($headers, $row);
    //         if (!isset($rowAssoc['items_id'])) continue;
    //         $itemExists = Item::where('id', $rowAssoc['items_id'])->exists();
    //         if (!$itemExists) $missingItems[] = $rowAssoc['items_id'];
    //     }

    //     if (!empty($missingItems)) {
    //         return redirect()->route('transactions.index')
    //             ->with('error', 'Import failed: some item IDs are missing in the database: ' . implode(', ', $missingItems));
    //     }

    //     // Save and extract compressed file
    //     $compressedFile = $request->file('compressed_file');
    //     $extension = strtolower($compressedFile->getClientOriginalExtension());

    //     $compressedPath = $compressedFile->storeAs('temp_compressed', uniqid() . '.' . $extension, 'public');
    //     $compressedFullPath = storage_path('app/public/' . $compressedPath);
    //     $extractTo = storage_path('app/public/tmp_zip_' . time());
    //     mkdir($extractTo, 0755, true);

    //     $zip = new ZipArchive;
    //     if ($zip->open($compressedFullPath) === TRUE) {
    //         $zip->extractTo($extractTo);
    //         $zip->close();
    //     } else {
    //         return redirect()->route('transactions.index')
    //             ->with('error', 'Failed to open compressed file.');
    //     }

    //     if (!file_exists($extractTo . '/transaction_photos') || !file_exists($extractTo . '/transaction_attachments')) {
    //         return redirect()->route('transactions.index')
    //             ->with('error', 'ZIP must contain folders named "transaction_photos" and "transaction_attachments".');
    //     }

    //     // Process and save data
    //     foreach ($jsonData as $row) {
    //         $rowAssoc = array_combine($headers, $row);

    //         $photoPathInZip = $extractTo . '/transaction_photos/' . $rowAssoc['photoitems'];
    //         $attachmentPathInZip = $extractTo . '/transaction_attachments/' . $rowAssoc['attachmentfile'];

    //         if (!file_exists($photoPathInZip) || !file_exists($attachmentPathInZip)) {
    //             continue;
    //         }

    //         $newPhotoPath = 'transaction_photos/' . uniqid() . '_' . $rowAssoc['photoitems'];
    //         $newAttachmentPath = 'transaction_attachments/' . uniqid() . '_' . $rowAssoc['attachmentfile'];

    //         Storage::disk('public')->put($newPhotoPath, file_get_contents($photoPathInZip));
    //         Storage::disk('public')->put($newAttachmentPath, file_get_contents($attachmentPathInZip));

    //         $transaction = Transaction::create([
    //             'items_id' => $rowAssoc['items_id'],
    //             'qty' => $rowAssoc['qty'],
    //             'supplier' => $rowAssoc['supplier'],
    //             'type' => $rowAssoc['type'],
    //             'photoitems' => $newPhotoPath,
    //             'attachmentfile' => $newAttachmentPath,
    //             'notes' => $rowAssoc['notes'] ?? null,
    //         ]);

    //         if ($rowAssoc['type'] === 'sparepart') {
    //             $sparepart = Sparepart::firstOrCreate(
    //                 ['items_id' => $rowAssoc['items_id']],
    //                 ['supplier' => $rowAssoc['supplier'], 'photo' => $newPhotoPath]
    //             );
    //             $sparepart->increment('qty', $rowAssoc['qty']);
    //         }
    //     }

    //     return redirect()->route('transactions.index')->with('success', 'Transaction imported successfully.');
    // }
}
