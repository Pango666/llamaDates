<?php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InventoryMovementController extends Controller
{
    public function index(Request $r)
    {
        $qProd = (int) $r->get('product_id', 0);
        $qLoc  = (int) $r->get('location_id', 0);
        $type  = $r->get('type','all'); // all|in|out
        $from  = $r->get('from');
        $to    = $r->get('to');

        $movs = InventoryMovement::with(['product:id,name,sku,unit','location:id,name'])
            ->when($qProd, fn($qq)=>$qq->where('product_id',$qProd))
            ->when($qLoc,  fn($qq)=>$qq->where('location_id',$qLoc))
            ->when(in_array($type,['in','out']), fn($qq)=>$qq->where('type',$type))
            ->when($from, fn($qq)=>$qq->whereDate('created_at','>=',$from))
            ->when($to,   fn($qq)=>$qq->whereDate('created_at','<=',$to))
            ->orderByDesc('created_at')
            ->paginate(20)->withQueryString();

        $products  = Product::orderBy('name')->get(['id','name','sku','unit']);
        $locations = Location::orderBy('is_main','desc')->orderBy('name')->get(['id','name']);

        return view('admin.inv.movs.index', compact('movs','products','locations','qProd','qLoc','type','from','to'));
    }

    public function create()
    {
        $movement = new InventoryMovement(['type'=>'in','qty'=>1]);
        $products  = Product::orderBy('name')->get(['id','name','sku','unit','cost_avg']);
        $locations = Location::where('active',true)->orderBy('is_main','desc')->orderBy('name')->get(['id','name']);
        return view('admin.inv.movs.create', compact('movement','products','locations'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'product_id' => ['required','exists:products,id'],
            'location_id'=> ['required','exists:locations,id'],
            'type'       => ['required', Rule::in(['in','out'])],
            'qty'        => ['required','numeric','min:0.0001'],
            'unit_cost'  => ['nullable','numeric','min:0'],
            'notes'      => ['nullable','string','max:300'],
        ]);

        $userId = optional($r->user())->id;

        DB::transaction(function() use ($data,$userId){
            $product = Product::lockForUpdate()->findOrFail($data['product_id']);

            // costo a registrar en el movimiento
            $unitCost = (float)($data['unit_cost'] ?? 0);
            if ($data['type'] === 'out' && $unitCost <= 0) {
                // si sale sin especificar, usa costo promedio actual
                $unitCost = (float) $product->cost_avg;
            }

            // crear movimiento
            $mov = InventoryMovement::create([
                'product_id'          => $product->id,
                'location_id'         => $data['location_id'],
                'type'                => $data['type'],
                'qty'                 => (float)$data['qty'],
                'unit_cost_at_issue'  => $unitCost,
                'notes'               => $data['notes'] ?? null,
                'created_by'          => $userId,
            ]);

            // recalcular costo promedio si es ENTRADA
            if ($mov->type === 'in') {
                // stock total antes de la entrada (todas ubicaciones)
                $beforeQty = (float) InventoryMovement::where('product_id',$product->id)
                    ->selectRaw('COALESCE(SUM(CASE WHEN type="in" THEN qty ELSE -qty END),0) as stock')
                    ->value('stock');

                // antes de insertar "mov" ya se insertó; restar su qty para stock previo:
                $beforeQty = $beforeQty - (float)$mov->qty; // stock realmente previo

                $beforeCost = $product->cost_avg;
                $newQty     = $beforeQty + (float)$mov->qty;

                if ($newQty > 0) {
                    $newAvg = (($beforeQty * $beforeCost) + ($mov->qty * $mov->unit_cost_at_issue)) / $newQty;
                    $product->update(['cost_avg' => round($newAvg, 4)]);
                } else {
                    // si era 0 → el nuevo avg es el del movimiento
                    $product->update(['cost_avg' => round((float)$mov->unit_cost_at_issue, 4)]);
                }
            }
        });

        return redirect()->route('admin.inv.movs.index')->with('ok','Movimiento registrado');
    }
}
