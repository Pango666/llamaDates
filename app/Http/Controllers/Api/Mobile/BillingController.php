<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Patient;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    /**
     * GET /api/v1/mobile/invoices
     * List invoices for the logged in patient
     */
    public function index(Request $request)
    {
        $user = auth('api')->user();
        $patient = Patient::where('user_id', $user->id)->first();
        
        if (!$patient) return response()->json(['error' => 'Paciente no encontrado'], 404);

        $invoices = Invoice::where('patient_id', $patient->id)
            ->withCount('items')
            ->orderBy('issued_at', 'desc')
            ->paginate(15)
            ->through(function($inv) {
                return [
                    'id'          => $inv->id,
                    'number'      => $inv->number,
                    'date'        => $inv->issued_at ? $inv->issued_at->format('Y-m-d') : null,
                    'total'       => (float) $inv->grand_total,
                    'status'      => $inv->status, // draft, issued, paid, cancelled
                    'balance'     => (float) $inv->balance,
                    'items_count' => $inv->items_count,
                ];
            });

        return response()->json($invoices);
    }

    /**
     * GET /api/v1/mobile/invoices/{id}
     * Invoice details + payments
     */
    public function show($id)
    {
        $user = auth('api')->user();
        $patient = Patient::where('user_id', $user->id)->first();

        $invoice = Invoice::where('id', $id)
            ->where('patient_id', $patient->id)
            ->with(['items', 'payments'])
            ->firstOrFail();

        return response()->json([
            'id'          => $invoice->id,
            'number'      => $invoice->number,
            'status'      => $invoice->status,
            'issued_at'   => $invoice->issued_at ? $invoice->issued_at->format('Y-m-d H:i') : null,
            'notes'       => $invoice->notes,
            'totals'      => [
                'subtotal' => (float)$invoice->subtotal,
                'discount' => (float)$invoice->discount,
                'tax'      => (float)($invoice->grand_total - $invoice->subtotal + $invoice->discount), // approx
                'total'    => (float)$invoice->grand_total,
                'paid'     => (float)$invoice->paid_amount,
                'balance'  => (float)$invoice->balance,
            ],
            'items' => $invoice->items->map(fn($i) => [
                'description' => $i->concept,
                'quantity'    => $i->quantity,
                'price'       => (float)$i->price,
                'total'       => (float)$i->total,
            ]),
            'payments' => $invoice->payments->map(fn($p) => [
                'date'   => $p->paid_at ? $p->paid_at->format('Y-m-d') : $p->created_at->format('Y-m-d'),
                'amount' => (float)$p->amount,
                'method' => $p->method,
                'ref'    => $p->reference,
            ])
        ]);
    }
}
