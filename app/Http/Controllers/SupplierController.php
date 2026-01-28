<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $r)
    {
        $q = trim((string) $r->get('q', ''));
        $state = $r->get('state', 'all');

        $suppliers = Supplier::query()
            ->when($q, function ($qq) use ($q) {
                $qq->where(function($query) use ($q) {
                    $query->where('name', 'like', "%$q%")
                          ->orWhere('contact', 'like', "%$q%")
                          ->orWhere('phone', 'like', "%$q%");
                });
            })
            ->when($state !== 'all', function ($qq) use ($state) {
                $qq->where('active', $state === 'active');
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $activeCount = Supplier::where('active', true)->count();
        $inactiveCount = Supplier::where('active', false)->count();

        return view('admin.inv.suppliers.index', compact('suppliers', 'q', 'state', 'activeCount', 'inactiveCount'));
    }

    public function toggle(Supplier $supplier)
    {
        $supplier->update(['active' => !$supplier->active]);
        return back()->with('ok', $supplier->active ? 'Proveedor activado' : 'Proveedor desactivado');
    }

    public function create()
    {
        $supplier = new Supplier();

        return view('admin.inv.suppliers.create', compact('supplier'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'name'    => ['required', 'string', 'max:150'],
            'email'   => ['nullable', 'email', 'max:100'],
            'contact' => ['nullable', 'string', 'max:120'],
            'phone'   => ['nullable', 'string', 'max:50'],
            'tax_id'  => ['nullable', 'string', 'max:50'],
        ]);

        $sup = Supplier::create($data);

        return redirect()
            ->route('admin.inv.suppliers.index')
            ->with('ok', 'Proveedor creado');
    }

    public function edit(Supplier $supplier)
    {
        return view('admin.inv.suppliers.edit', compact('supplier'));
    }

    public function update(Request $r, Supplier $supplier)
    {
        $data = $r->validate([
            'name'    => ['required', 'string', 'max:150'],
            'email'   => ['nullable', 'email', 'max:100'],
            'contact' => ['nullable', 'string', 'max:120'],
            'phone'   => ['nullable', 'string', 'max:50'],
            'tax_id'  => ['nullable', 'string', 'max:50'],
        ]);

        $supplier->update($data);

        return redirect()
            ->route('admin.inv.suppliers.index')
            ->with('ok', 'Proveedor actualizado');
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->products()->exists()) {
            return back()->withErrors('No se puede eliminar: el proveedor tiene productos asociados.');
        }

        $supplier->delete();

        return redirect()
            ->route('admin.inv.suppliers.index')
            ->with('ok', 'Proveedor eliminado');
    }
}
