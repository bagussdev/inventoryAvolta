<?php

namespace App\Http\Controllers;

use App\Models\Laptop;
use App\Models\Desktop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DeletedItemsController extends Controller
{
    use AuthorizesRequests;
    protected function buildQuery(Request $request)
    {
        $search = $request->input('search');

        // Ambil soft deleted laptop
        $laptops = Laptop::onlyTrashed()
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('hostname', 'like', "%{$search}%")
                        ->orWhere('serialnumber', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%")
                        ->orWhere('user', 'like', "%{$search}%");
                });
            })
            ->selectRaw("'laptop' as type, id, hostname, serialnumber, model, brand, user, location, status, deleted_at");

        // Ambil soft deleted desktop
        $desktops = Desktop::onlyTrashed()
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('hostname', 'like', "%{$search}%")
                        ->orWhere('serialnumber', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%")
                        ->orWhere('user', 'like', "%{$search}%");
                });
            })
            ->selectRaw("'desktop' as type, id, hostname, serialnumber, model, brand, user, location, status, deleted_at");

        // UNION kedua query
        $query = $laptops->unionAll($desktops);

        return $query;
    }

    public function index(Request $request)
    {
        $this->authorize('deleteditemsmenu');

        $perPageInput = $request->input('per_page', 5);
        $forceAll = $perPageInput === 'all' || ($request->input('search') && !$request->has('per_page'));

        $query = $this->buildQuery($request);

        if ($forceAll) {
            $total = $query->count();
            $items = $query->orderByDesc('deleted_at')->paginate(max($total, 1))->appends($request->query());
            $perPage = 'all';
        } else {
            $perPage = is_numeric($perPageInput) ? (int)$perPageInput : 5;
            $items = $query->orderByDesc('deleted_at')->paginate($perPage)->appends($request->query());
        }

        $baseOffset = ($items instanceof \Illuminate\Pagination\LengthAwarePaginator)
            ? (($items->currentPage() - 1) * $items->perPage())
            : 0;

        $search = $request->input('search');

        return view('deleteditems.index', compact('items', 'perPage', 'baseOffset', 'search'));
    }

    public function restore(Request $request, $type, $id)
    {
        $this->authorize('deleteditemsmenu');

        if ($type === 'laptop') {
            $item = Laptop::onlyTrashed()->findOrFail($id);
        } elseif ($type === 'desktop') {
            $item = Desktop::onlyTrashed()->findOrFail($id);
        } else {
            abort(404, 'Invalid type');
        }

        $item->restore();

        return redirect()->route('deleteditems.index')->with('success', ucfirst($type) . ' berhasil direstore.');
    }

    public function forceDelete(Request $request, $type, $id)
    {
        $this->authorize('deleteditemsmenu');

        if ($type === 'laptop') {
            $item = Laptop::onlyTrashed()->findOrFail($id);
        } elseif ($type === 'desktop') {
            $item = Desktop::onlyTrashed()->findOrFail($id);
        } else {
            abort(404, 'Invalid type');
        }

        $item->forceDelete();

        return redirect()->route('deleteditems.index')->with('success', ucfirst($type) . ' berhasil dihapus permanen.');
    }
}
