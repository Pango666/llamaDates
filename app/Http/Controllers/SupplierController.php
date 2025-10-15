<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $r)
    {
        $q = trim((string)$r->get('q',''));
        $suppliers = Supplier::when($q, function($qq) use($q){
                $qq->where('name','like',"%$q%")
                   ->orWhere('email','like',"%$q%")
                   ->orWhere('phone','like',"%$q%");
            })
            ->orderBy('name')
            ->paginate(15)->withQueryString();

        return view('admin.inv.suppliers.index', compact('suppliers','q'));
    }

    public function create()
    {
        $supplier = new Supplier(['active'=>true]);
        return view('admin.inv.suppliers.create', compact('supplier'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'name'  => ['required','string','max:120'],
            'phone' => ['nullable','string','max:60'],
            'email' => ['nullable','email','max:120'],
            'notes' => ['nullable','string','max:500'],
            'active'=> ['nullable','boolean'],
        ]);
        $data['active'] = (bool)($data['active'] ?? true);

        $s = Supplier::create($data);
        return redirect()->route('admin.inv.suppliers.edit',$s)->with('ok','Proveedor creado');
    }

    public function edit(Supplier $proveedore) // por resource names Laravel pluraliza raro → $proveedore
    {
        $supplier = $proveedore;
        return view('admin.inv.suppliers.edit', compact('supplier'));
    }

    public function update(Request $r, Supplier $proveedore)
    {
        $supplier = $proveedore;
        $data = $r->validate([
            'name'  => ['required','string','max:120'],
            'phone' => ['nullable','string','max:60'],
            'email' => ['nullable','email','max:120'],
            'notes' => ['nullable','string','max:500'],
            'active'=> ['nullable','boolean'],
        ]);
        $data['active'] = (bool)($data['active'] ?? false);

        $supplier->update($data);
        return back()->with('ok','Proveedor actualizado');
    }

    public function destroy(Supplier $proveedore)
    {
        $proveedore->delete();
        return redirect()->route('admin.inv.suppliers.index')->with('ok','Proveedor eliminado');
    }
}
