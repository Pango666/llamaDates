<?php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InventoryMovementController extends Controller
{
    public function index(Request $r)
    {
        $qProd = (int) $r->get('product_id', 0);
        $qLoc  = (int) $r->get('location_id', 0);
        $qUser = (int) $r->get('user_id', 0);
        $type  = $r->get('type', 'all'); // all|in|out
        $from  = $r->get('from');
        $to    = $r->get('to');

        $movs = InventoryMovement::with([
            'product:id,name,sku,unit',
            'location:id,name',
            'user:id,name',
        ])
            ->when($qProd, fn($qq) => $qq->where('product_id', $qProd))
            ->when($qLoc,  fn($qq) => $qq->where('location_id', $qLoc))
            ->when($qUser, fn($qq) => $qq->where('user_id', $qUser)) // 🔹 aquí se aplica el filtro
            ->when(in_array($type, ['in', 'out']), fn($qq) => $qq->where('type', $type))
            ->when($from, fn($qq) => $qq->whereDate('created_at', '>=', $from))
            ->when($to,   fn($qq) => $qq->whereDate('created_at', '<=', $to))
            ->orderByDesc('created_at')
            ->paginate(20)->withQueryString();

        $products  = Product::orderBy('name')->get(['id', 'name', 'sku', 'unit']);
        $locations = Location::orderBy('name')->get(['id', 'name']);
        $users = User::whereIn('role', ['admin', 'asistente', 'odontologo'])
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.inv.movs.index', compact(
            'movs',
            'products',
            'locations',
            'users',      // 🔹
            'qProd',
            'qLoc',
            'qUser',      // 🔹
            'type',
            'from',
            'to'
        ));
    }

    // InventoryMovementController@create
    public function create()
    {
        $movement  = new InventoryMovement(['type' => 'in', 'qty' => 1]);
        $products  = Product::orderBy('name')->get(['id', 'name', 'sku', 'unit']);
        $locations = Location::orderBy('name')->get(['id', 'name']);   // 👈 aquí también

        return view('admin.inv.movs.create', compact('movement', 'products', 'locations'));
    }


    public function store(Request $r)
    {
        $data = $r->validate([
            'product_id'              => ['required', 'exists:products,id'],
            'location_id'             => ['required', 'exists:locations,id'],
            'type'                    => ['required', Rule::in(InventoryMovement::types())], // in|out|adjust|transfer
            'qty'                     => ['required', 'numeric', 'min:0.0001'],
            'unit_cost'               => ['nullable', 'numeric', 'min:0'],
            'purchase_invoice_number' => ['nullable', 'string', 'max:60'],
            'lot'                     => ['nullable', 'string', 'max:60'],
            'expires_at'              => ['nullable', 'date'],
            'note'                    => ['nullable', 'string', 'max:500'],
        ]);

        $userId = optional($r->user())->id;

        DB::transaction(function () use ($data, $userId) {
            $product = Product::lockForUpdate()->findOrFail($data['product_id']);

            $unitCost = (float) ($data['unit_cost'] ?? 0);

            // Crear movimiento
            $mov = InventoryMovement::create([
                'product_id'              => $product->id,
                'location_id'             => $data['location_id'],
                'type'                    => $data['type'],
                'qty'                     => (float) $data['qty'],
                'unit_cost'               => $unitCost,
                'purchase_invoice_number' => $data['purchase_invoice_number'] ?? null,
                'lot'                     => $data['lot'] ?? null,
                'expires_at'              => $data['expires_at'] ?? null,
                'appointment_id'          => null,
                'user_id'                 => $userId,
                'note'                    => $data['note'] ?? null,
            ]);

            // Recalcular y guardar snapshot de stock en products.stock
            $stock = InventoryMovement::where('product_id', $product->id)
                ->selectRaw(
                    'COALESCE(SUM(
                        CASE
                            WHEN type IN ("in","adjust","transfer") THEN qty
                            WHEN type = "out" THEN -qty
                            ELSE 0
                        END
                    ), 0) AS stock'
                )
                ->value('stock');

            $product->update(['stock' => $stock]);
        });

        return redirect()->route('admin.inv.movs.index')->with('ok', 'Movimiento registrado');
    }
}
