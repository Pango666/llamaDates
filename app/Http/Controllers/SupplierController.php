<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $r)
    {
        $q = trim((string) $r->get('q', ''));

        $suppliers = Supplier::when($q, function ($qq) use ($q) {
            $qq->where('name', 'like', "%$q%")
               ->orWhere('contact', 'like', "%$q%")
               ->orWhere('phone', 'like', "%$q%");
        })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.inv.suppliers.index', compact('suppliers', 'q'));
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
            'contact' => ['nullable', 'string', 'max:120'],
            'phone'   => ['nullable', 'string', 'max:50'],
            'tax_id'  => ['nullable', 'string', 'max:50'],
        ]);

        $sup = Supplier::create($data);

        return redirect()
            ->route('admin.inv.suppliers.edit', $sup)
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
            'contact' => ['nullable', 'string', 'max:120'],
            'phone'   => ['nullable', 'string', 'max:50'],
            'tax_id'  => ['nullable', 'string', 'max:50'],
        ]);

        $supplier->update($data);

        return back()->with('ok', 'Proveedor actualizado');
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
