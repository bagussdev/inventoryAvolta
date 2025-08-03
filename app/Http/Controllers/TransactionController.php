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
use App\Models\NotificationPreference;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TransactionController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $this->authorize('historytransactionsmenu');

        $perPage = $request->input('per_page', 5);
        $perPage = $perPage === 'all' ? 'all' : (int) $perPage;

        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        $transactionsQuery = Transaction::with(['item', 'user']);

        // Search filter
        if ($search) {
            $transactionsQuery->where(function ($q) use ($search) {
                $q->where('serial_number', 'like', "%{$search}%")
                    ->orWhere('supplier', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%")
                    ->orWhereHas('item', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%")
                            ->orWhere('brand', 'like', "%{$search}%")
                            ->orWhere('category', 'like', "%{$search}%")
                            ->orWhere('model', 'like', "%{$search}%");
                    })->orWhereHas('user', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Department filter
        if (!$isMaster) {
            if ($user->department_id) {
                $transactionsQuery->whereHas('item', function ($q) use ($user) {
                    $q->where('department_id', $user->department_id);
                });
            } else {
                $transactionsQuery->whereNull('id');
            }
        }

        // Date range filter
        if ($startDate && $endDate) {
            $transactionsQuery->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }

        // Pagination or all
        $isFiltered = $search || ($startDate && $endDate);

        if ($perPage === 'all' || $isFiltered) {
            $transactions = $transactionsQuery->orderBy('created_at', 'desc')->get();
        } else {
            $transactions = $transactionsQuery
                ->orderBy('created_at', 'desc')
                ->paginate($perPage)
                ->appends(compact('search', 'perPage', 'startDate', 'endDate'));
        }

        return view('transactions.index', compact('transactions', 'search', 'perPage', 'startDate', 'endDate', 'isFiltered'));
    }

    public function tbody(Request $request)
    {
        $perPage = $request->input('per_page', 5);
        $perPage = $perPage === 'all' ? 'all' : (int) $perPage;

        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        $query = Transaction::with(['item', 'user']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('serial_number', 'like', "%{$search}%")
                    ->orWhere('supplier', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%")
                    ->orWhereHas('item', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%")
                            ->orWhere('brand', 'like', "%{$search}%")
                            ->orWhere('category', 'like', "%{$search}%")
                            ->orWhere('model', 'like', "%{$search}%");
                    })->orWhereHas('user', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if (!$isMaster && $user->department_id) {
            $query->whereHas('item', function ($q) use ($user) {
                $q->where('department_id', $user->department_id);
            });
        }

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }

        $isFiltered = $search || ($startDate && $endDate);

        if ($perPage === 'all' || $isFiltered) {
            $transactions = $query->orderBy('created_at', 'desc')->get();
        } else {
            $transactions = $query
                ->orderBy('created_at', 'desc')
                ->paginate($perPage)
                ->appends(compact('search', 'perPage', 'startDate', 'endDate'));
        }

        return view('partials.transactions-tbody', compact('transactions', 'isFiltered'));
    }

    public function lastUpdated(Request $request)
    {
        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        $query = Transaction::query();

        // Filter departemen jika bukan master
        if (!$isMaster) {
            if ($user->department_id) {
                $query->whereHas('item', function ($q) use ($user) {
                    $q->where('department_id', $user->department_id);
                });
            } else {
                $query->whereNull('id');
            }
        }

        $lastUpdated = $query->max('updated_at');

        return response()->json([
            'last_updated' => $lastUpdated
        ]);
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

    private function getNotificationTargets(string $type, int $departmentId = null): array
    {
        $preferences = NotificationPreference::where('type', $type)->pluck('role_id')->toArray();

        $targets = [];

        foreach ($preferences as $roleId) {
            if (in_array($roleId, [1])) { // Master tanpa department
                $targets[] = ['role_id' => $roleId];
            } elseif ($departmentId) {
                $targets[] = ['role_id' => $roleId, 'department_id' => $departmentId];
            }
        }

        return $targets;
    }
    private function notifyByType(string $type, $itemId, $referenceType, $referenceId)
    {
        $item = Item::find($itemId);
        if (!$item) return;

        $targets = $this->getNotificationTargets($type, $item->department_id);
        $user = Auth::user();

        $itemLabel = "<b>" . strtoupper($item->name) . "</b> (" . strtoupper($item->brand) . " " . strtoupper($item->model) . ")";
        $asType = $type === 'equipment_create' ? 'equipment' : 'sparepart';

        $message = "$itemLabel has been added as <span class='font-bold'>$asType</span> by <b>{$user->name}</b>.";

        NotificationService::send(
            $targets,
            $type,
            'New Item Transaction',
            $message,
            $referenceType,
            $referenceId
        );
    }

    private function notifyUpdate(string $type, $itemId, $referenceType, $referenceId)
    {
        $item = Item::find($itemId);
        if (!$item) return;

        $targets = $this->getNotificationTargets($type, $item->department_id);
        $user = Auth::user();

        $itemLabel = "<b>" . strtoupper($item->name) . "</b> (" . strtoupper($item->brand) . " " . strtoupper($item->model) . ")";
        $message = "$itemLabel has been <span class='font-bold'>updated</span> by <b>{$user->name}</b>.";

        NotificationService::send(
            $targets,
            $type,
            'Item Updated',
            $message,
            $referenceType,
            $referenceId
        );
    }

    public function store(Request $request)
    {
        $this->authorize('historytransactionsmenu');

        $validated = $request->validate([
            'items_id' => 'required|exists:items,id',
            'serial_number' => 'nullable|string|max:255|required_if:type,equipment',
            'qty' => 'required|integer|min:1',
            'photoitems' => 'required|image|mimes:jpeg,jpg,png,webp|max:10240',
            'attachmentfile' => 'required|file|mimes:pdf,jpeg,jpg,png,webp|max:10240',
            'notes' => 'nullable|string|max:1000',
            'supplier' => 'required|string|max:255',
            'type' => 'required|in:equipment,sparepart',
        ], [
            'serial_number.required_if' => 'Field S/N wajib diisi jika tipe adalah equipment.',
            'photoitems.required' => 'Foto barang wajib diunggah.',
            'photoitems.image' => 'File foto harus berupa gambar.',
            'photoitems.max' => 'Ukuran file foto maksimal 10MB.',
            'attachmentfile.required' => 'Invoice atau surat wajib diunggah.',
            'attachmentfile.max' => 'Ukuran file lampiran maksimal 20MB.',
        ]);

        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        if (!$isMaster) {
            $item = Item::find($request->items_id);

            if (!$item || $item->department_id !== $user->department_id) {
                return back()->with('error', 'Anda tidak memiliki izin untuk melakukan transaksi pada item ini.')->withInput();
            }
        }

        if ($validated['type'] === 'equipment') {
            $existingEquipment = Equipment::where('serial_number', $validated['serial_number'])->first();
            if ($existingEquipment) {
                return back()->with('error', 'This Serial Number has already been registered to another equipment.')->withInput();
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
                $this->notifyByType('equipment_create', $transaction->items_id, 'transactions', $transaction->id);
            } else {
                $sparepart = Sparepart::firstOrCreate(
                    ['items_id' => $validated['items_id']],
                    [
                        'transactions_id' => $transaction->id,
                        'qty' => 0, // default
                        'status' => 'available', // default
                    ]
                );

                // Update qty dan transaction id setiap saat
                $sparepart->transactions_id = $transaction->id;
                $sparepart->qty += $validated['qty'];

                // Update status
                if ($sparepart->qty === 0) {
                    $sparepart->status = 'empty';
                } elseif ($sparepart->qty < 5) {
                    $sparepart->status = 'low';
                } else {
                    $sparepart->status = 'available';
                }

                $sparepart->save();
                $this->notifyByType('sparepart_create', $transaction->items_id, 'transactions', $transaction->id);
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

        // Cek duplicate serial number jika type adalah equipment
        if ($transaction->type === 'equipment' && !empty($validated['serial_number'])) {
            $duplicateSN = Equipment::where('serial_number', $validated['serial_number'])
                ->where('transactions_id', '!=', $transaction->id)
                ->first();

            if ($duplicateSN) {
                return back()->with('error', 'This Serial Number is already registered to another equipment.')->withInput();
            }
        }

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

            $type = $transaction->type === 'equipment' ? 'equipment_update' : 'sparepart_update';
            $this->notifyUpdate($type, $transaction->items_id, 'transactions', $transaction->id);

            return redirect()->route('transactions.index')->with('success', 'Transaksi dan data equipment berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui: ' . $e->getMessage())->withInput();
        }
    }
    public function show($id)
    {
        $this->authorize('historytransactionsmenu');

        $transaction = Transaction::with('item')->findOrFail($id);
        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        // Cek hak akses departemen
        if (!$isMaster && $transaction->item->department_id !== $user->department_id) {
            abort(403, 'You do not have permission to view this transaction.');
        }

        return view('transactions.show', compact('transaction'));
    }

    public function downloadTemplate()
    {
        return response()->download(storage_path('app/public/templates/transactions_template.xlsx'));
    }
    public function json()
    {
        $this->authorize('historytransactionsmenu');

        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        $query = Item::query();

        if (!$isMaster) {
            $query->where('department_id', $user->department_id);
        }

        return response()->json($query->get());
    }

    public function destroy($id)
    {
        $this->authorize('historytransactions.delete');

        $transaction = Transaction::findOrFail($id);

        if ($transaction->type === 'equipment') {
            $equipment = Equipment::where('items_id', $transaction->items_id)
                ->where('serial_number', $transaction->serial_number)
                ->first();

            if ($equipment) {
                // Cek apakah equipment digunakan di maintenance atau incident
                $usedInMaintenance = DB::table('maintenances')->where('equipment_id', $equipment->id)->exists();
                $usedInIncident = DB::table('incidents')->where('item_problem', $equipment->id)->exists();

                if ($usedInMaintenance || $usedInIncident) {
                    return redirect()->back()->with('error', 'Cannot delete: Equipment is in use.');
                }

                // Validasi status harus 'available' atau 'broken'
                if (!in_array(strtolower($equipment->status), ['available', 'broken'])) {
                    return redirect()->back()->with('error', 'Cannot delete: Equipment status must be "available" or "broken".');
                }

                $equipment->delete();
            }
        } elseif ($transaction->type === 'sparepart') {
            $sparepart = Sparepart::where('items_id', $transaction->items_id)->first();

            if ($sparepart) {
                if ($sparepart->qty < $transaction->qty) {
                    return back()->with('error', 'Cannot delete: Sparepart stock insufficient.');
                }

                $sparepart->qty -= $transaction->qty;

                // Update status sparepart
                if ($sparepart->qty <= 0) {
                    $sparepart->status = 'empty';
                } elseif ($sparepart->qty < 5) {
                    $sparepart->status = 'low';
                } else {
                    $sparepart->status = 'available';
                }

                $sparepart->save(); // sparepart tetap ada, hanya qty dikurangi
            }
        }

        // Hapus file jika ada
        if ($transaction->photoitems && Storage::disk('public')->exists($transaction->photoitems)) {
            Storage::disk('public')->delete($transaction->photoitems);
        }

        if ($transaction->attachmentfile && Storage::disk('public')->exists($transaction->attachmentfile)) {
            Storage::disk('public')->delete($transaction->attachmentfile);
        }

        $transaction->delete(); // hanya hapus baris transaksi

        return redirect()->route('transactions.index')->with('success', 'Transaction deleted successfully.');
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
