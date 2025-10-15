<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AppointmentSupply;
use App\Models\InventoryMovement;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Location;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AppointmentSupplyController extends Controller
{
    public function store(Request $r)
    {
        $data = $r->validate([
            'patient_id' => 'required|exists:patients,id',
            'dentist_id' => 'required|exists:dentists,id',
            'service_id' => 'required|exists:services,id',
            'date'       => 'required',       // puede venir como Y-m-d o con tiempo
            'start_time' => 'required',       // H:i u H:i:s
            'notes'      => 'nullable|string',
        ]);

        $svc = \App\Models\Service::findOrFail($data['service_id']);

        // --- NORMALIZA FECHA y HORA (robusto contra "YYYY-mm-dd 00:00:00") ---
        $day = \Carbon\Carbon::parse($data['date'])->toDateString();           // "2025-10-13"
        $startStr = strlen($data['start_time']) === 5
            ? $data['start_time'] . ':00'
            : $data['start_time'];                                             // "HH:ii:ss"

        // Combina sin ambigüedades
        $start = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', "$day $startStr");
        $end   = (clone $start)->addMinutes($svc->duration_min);

        if ($start->isPast()) {
            return back()->withErrors(['start_time' => 'No se puede reservar en el pasado'])->withInput();
        }

        // Conflicto con otras citas activas
        $conflict = \App\Models\Appointment::where('dentist_id', $data['dentist_id'])
            ->whereDate('date', $day)->where('is_active', true)
            ->where('start_time', '<', $end->format('H:i:s'))
            ->where('end_time',   '>', $start->format('H:i:s'))
            ->exists();

        if ($conflict) {
            return back()->withErrors(['start_time' => 'Horario no disponible'])->withInput();
        }

        // Determinar silla
        $dow = $start->dayOfWeek; // 0..6
        $block = \App\Models\Schedule::where('dentist_id', $data['dentist_id'])
            ->where('day_of_week', $dow)
            ->where('start_time', '<=', $start->format('H:i:s'))
            ->where('end_time',   '>=', $end->format('H:i:s'))
            ->orderBy('start_time', 'desc')
            ->first();

        if (!$block) {
            return back()->withErrors(['start_time' => 'El horario seleccionado no pertenece al turno del odontólogo.'])->withInput();
        }

        $chairId = $block->chair_id ?? \App\Models\Dentist::whereKey($data['dentist_id'])->value('chair_id');
        if (!$chairId) {
            return back()->withErrors(['start_time' => 'No hay silla asignada para ese turno.'])->withInput();
        }

        \App\Models\Appointment::create([
            'patient_id' => $data['patient_id'],
            'dentist_id' => $data['dentist_id'],
            'service_id' => $data['service_id'],
            'chair_id'   => $chairId,
            'date'       => $day,                           // <-- solo fecha
            'start_time' => $start->format('H:i:s'),
            'end_time'   => $end->format('H:i:s'),
            'status'     => 'reserved',
            'is_active'  => true,
            'notes'      => $data['notes'] ?? null,
        ]);

        return redirect()->route('admin.appointments.index')->with('ok', 'Cita creada');
    }

    /**
     * Eliminar un suministro de la cita:
     * - genera movimiento inverso (entrada/ajuste) para devolver stock
     * - elimina el registro appointment_supplies
     * (no toca factura automáticamente)
     */
    public function destroy(Appointment $appointment, AppointmentSupply $supply)
    {
        abort_if($supply->appointment_id !== $appointment->id, 404);

        // 1) Regla de edición
        if ($appointment->status !== 'in_service') {
            return back()->withErrors('Solo puedes editar suministros con la cita "En atención".');
        }

        // 2) Evitar descuadre si ya hay factura (no cancelada)
        $hasInvoice = $appointment->invoice()
            ->whereNotIn('status', ['canceled'])
            ->exists();
        if ($hasInvoice) {
            return back()->withErrors('No puedes eliminar suministros: la cita ya tiene factura emitida.');
        }

        DB::transaction(function () use ($supply) {
            // 3) Revertir stock (ajuste positivo)
            InventoryMovement::create([
                'product_id'   => $supply->product_id,
                'location_id'  => $supply->location_id,
                'type'         => 'adjust',
                'qty'          => abs($supply->qty), // vuelve al stock
                'unit_cost'    => $supply->unit_cost_at_issue, // referencia
                'lot'          => $supply->lot,
                'expires_at'   => null,
                'appointment_id' => $supply->appointment_id,
                'user_id'      => auth()->id(),
                'note'         => 'Reversión por eliminación de suministro en cita',
            ]);

            // 4) Borrar el “vinculo” de la cita
            $supply->delete();
        });

        return back()->with('ok', 'Suministro eliminado y stock revertido.');
    }
}
