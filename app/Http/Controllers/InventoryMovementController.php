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
        $query = $this->buildQuery($r);

        $stats = [
            'total_moves' => (clone $query)->count(),
            'total_in'    => (clone $query)->where('type', 'in')->count(),
            'total_out'   => (clone $query)->where('type', 'out')->count(),
            'total_cost'  => (clone $query)->where('type', 'in')->sum(DB::raw('qty * unit_cost')),
        ];

        $movs = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        $products  = Product::orderBy('name')->get(['id', 'name', 'sku', 'unit']);
        $locations = Location::orderBy('name')->get(['id', 'name']);
        $users     = User::whereIn('role', ['admin', 'asistente', 'odontologo'])
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.inv.movs.index', array_merge([
            'movs'      => $movs,
            'products'  => $products,
            'locations' => $locations,
            'users'     => $users,
            'stats'     => $stats,
        ], $r->all()));
    }

    public function exportPdf(Request $r)
    {
        $query = $this->buildQuery($r);
        $movs  = $query->orderByDesc('created_at')->limit(500)->get(); // Límite para PDF

        $pdf = \PDF::loadView('admin.inv.movs.pdf', compact('movs', 'r'));
        return $pdf->download('reporte-movimientos-' . date('Y-m-d') . '.pdf');
    }

    public function exportCsv(Request $r)
    {
        $query = $this->buildQuery($r);

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            
            // BOM para Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, ['ID', 'Fecha', 'Producto', 'SKU', 'Ubicación', 'Usuario', 'Tipo', 'Cantidad', 'Costo Unit.', 'Lote', 'Vencimiento', 'Factura', 'Nota']);

            $query->chunk(500, function ($movs) use ($handle) {
                foreach ($movs as $m) {
                    fputcsv($handle, [
                        $m->id,
                        $m->created_at->format('d/m/Y H:i'),
                        $m->product->name ?? 'Eliminado',
                        $m->product->sku ?? '',
                        $m->location->name ?? '—',
                        $m->user->name ?? '—',
                        $m->type,
                        $m->qty,
                        $m->unit_cost,
                        $m->lot,
                        $m->expires_at,
                        $m->purchase_invoice_number,
                        $m->note
                    ]);
                }
            });

            fclose($handle);
        }, 'reporte-movimientos-' . date('Y-m-d') . '.csv');
    }

    private function buildQuery(Request $r)
    {
        $qProd = (int) $r->get('product_id', 0);
        $qLoc  = (int) $r->get('location_id', 0);
        $qUser = (int) $r->get('user_id', 0);
        $type  = $r->get('type', 'all');
        $from  = $r->get('from');
        $to    = $r->get('to');

        return InventoryMovement::with(['product:id,name,sku,unit', 'location:id,name', 'user:id,name'])
            ->when($qProd, fn($q) => $q->where('product_id', $qProd))
            ->when($qLoc,  fn($q) => $q->where('location_id', $qLoc))
            ->when($qUser, fn($q) => $q->where('user_id', $qUser))
            ->when(in_array($type, ['in', 'out', 'adjust', 'transfer']), fn($q) => $q->where('type', $type))
            ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to,   fn($q) => $q->whereDate('created_at', '<=', $to));
    }

    // InventoryMovementController@create
    public function create()
    {
        $movement  = new InventoryMovement(['type' => 'in', 'qty' => 1]);
        $products  = Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'sku', 'unit']);
        $locations = Location::orderBy('name')->get(['id', 'name']);   // 👈 aquí también

        return view('admin.inv.movs.create', compact('movement', 'products', 'locations'));
    }

    // Endpoint AJAX para formulario dinámico (Productos)
    public function productOptions(Request $r)
    {
        $hasStock = $r->boolean('has_stock'); // si es true, filtra stock > 0

        $products = Product::where('is_active', true)->orderBy('name')
            ->when($hasStock, function($q){
                $q->where('stock', '>', 0);
            })
            ->get(['id', 'name', 'stock', 'sku', 'unit']); 

        return response()->json($products);
    }

    // Endpoint AJAX para obtener lotes activos de un producto
    public function lotOptions(Request $r)
    {
        $productId = $r->get('product_id');
        if(!$productId) return response()->json([]);

        // Calcular lotes con saldo positivo
        // Esto asume que el stock se mueve por lotes. 
        // Si la tabla es muy grande, esto debería optimizarse o tener una tabla 'product_lots'.
        // Por ahora, hacemos una consulta agregada.
        
        $lots = InventoryMovement::where('product_id', $productId)
            ->whereNotNull('lot')
            ->where('lot', '<>', '')
            ->selectRaw('lot, DATE_FORMAT(MAX(expires_at), "%Y-%m-%d") as expires_at, SUM(CASE WHEN type IN ("in","adjust","transfer") THEN qty WHEN type="out" THEN -qty ELSE 0 END) as balance')
            ->groupBy('lot')
            ->having('balance', '>', 0)
            ->get();

        return response()->json($lots);
    }


    public function store(Request $r)
    {
        // Unificar inputs lot_in / lot_out en 'lot'
        $r->merge([
            'lot' => ($r->type === 'out') ? $r->lot_out : $r->lot_in
        ]);

        $data = $r->validate([
            'product_id'              => ['required', 'exists:products,id'],
            'location_id'             => ['required', 'exists:locations,id'],
            'type'                    => ['required', Rule::in(InventoryMovement::types())], // in|out|adjust|transfer
            'qty'                     => ['required', 'integer', 'min:1'],
            'unit_cost'               => ['nullable', 'numeric', 'min:0'],
            'purchase_invoice_number' => ['nullable', 'string', 'max:60'],
            'lot'                     => [
                Rule::requiredIf(fn() => in_array($r->type, ['in', 'out'])), 
                'nullable', 
                'string', 
                'max:60'
            ],
            'expires_at'              => [
                Rule::requiredIf(fn() => $r->type === 'in'), 
                'nullable', 
                'date'
            ],
            'note'                    => ['nullable', 'string', 'max:500'],
        ], [
            'lot.required' => 'El lote es obligatorio para este tipo de movimiento.',
            'expires_at.required' => 'La fecha de vencimiento es obligatoria para entradas.',
            'lot.required_if' => 'El lote es obligatorio.',
            'expires_at.required_if' => 'La fecha de vencimiento es obligatoria para entradas.',
        ]);

        $userId = optional($r->user())->id;

        // Validación de vencimiento REMOVIDA para permitir bajas por vencimiento.
        // El usuario sabe lo que hace si selecciona explícitamente un lote vencido para darle salida.
        
        // if ($data['type'] === 'out' && !empty($data['lot'])) {
        //    ...
        // }

        DB::transaction(function () use ($data, $userId) {
            $product = Product::lockForUpdate()->findOrFail($data['product_id']);

            // VALIDACIÓN: Evitar stock negativo GLOBAL
            if ($data['type'] === 'out') {
                if ($product->stock < $data['qty']) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'qty' => "Stock insuficiente. Stock actual: {$product->stock}, intentas sacar: {$data['qty']}",
                    ]);
                }

                // VALIDACIÓN: Evitar stock negativo del LOTE ESPECÍFICO
                if (!empty($data['lot'])) {
                    $lotBalance = InventoryMovement::where('product_id', $product->id)
                        ->where('lot', $data['lot'])
                        ->selectRaw('SUM(CASE WHEN type IN ("in","adjust","transfer") THEN qty WHEN type="out" THEN -qty ELSE 0 END) as balance')
                        ->value('balance');

                    $lotBalance = (int) $lotBalance;

                    if ($lotBalance < $data['qty']) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'lot' => "Stock insuficiente en el lote '{$data['lot']}'. Disponible: {$lotBalance}, Intentas sacar: {$data['qty']}",
                        ]);
                    }
                }
            }

            $unitCost = (float) ($data['unit_cost'] ?? 0);

            // Crear movimiento
            $mov = InventoryMovement::create([
                'product_id'              => $product->id,
                'location_id'             => $data['location_id'],
                'type'                    => $data['type'],
                'qty'                     => (int) $data['qty'],
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
