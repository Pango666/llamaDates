<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Odontogram;
use App\Models\OdontogramSurface;
use App\Models\OdontogramTooth;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class OdontogramController extends Controller
{
    public function open(Request $request, Patient $patient)
    {
        $appointmentId = $request->integer('appointment_id'); // opcional
        $odo = Odontogram::where('patient_id', $patient->id)
            ->orderByDesc('date')->orderByDesc('id')->first();

        if (!$odo) {
            $odo = Odontogram::create([
                'patient_id' => $patient->id,
                'date'       => now()->toDateString(),
                'notes'      => 'Odontograma inicial',
                'created_by' => optional(auth()->user())->id,
            ]);
        }

        return redirect()->route('admin.odontograms.show', [
            'odontogram'    => $odo->id,
            'appointment_id' => $appointmentId ?: null,
        ]);
    }

    public function show(Request $request, Odontogram $odontogram)
    {
        $patient = $odontogram->patient;

        $teeth = OdontogramTooth::with('surfaces')
            ->where('odontogram_id', $odontogram->id)
            ->orderBy('tooth_code')
            ->get();

        $teethByCode = $teeth->keyBy('tooth_code');

        $teethInit = [];
        foreach ($teeth as $t) {
            $surfaces = [];
            foreach ($t->surfaces as $s) {
                $surfaces[] = ['surface' => $s->surface, 'condition' => $s->condition];
            }
            $teethInit[$t->tooth_code] = [
                'status'   => $t->status,
                'notes'    => $t->notes,
                'surfaces' => $surfaces,
            ];
        }

        $returnTo = $request->query('return_to') ?: route('admin.patients.show', $patient);

        return view('admin.odontograms.editor', [
            'patient'     => $patient,
            'odontogram'  => $odontogram,
            'teethByCode' => $teethByCode,
            'teethInit'   => $teethInit,
            'returnTo'    => $returnTo,
        ]);
    }

    public function upsertTeeth(Request $request, Odontogram $odontogram)
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.tooth_code' => ['required', 'string', 'max:3'],
            'items.*.status' => ['nullable', 'in:sano,ausente'],
            'items.*.notes' => ['nullable', 'string'],
            'items.*.surfaces' => ['array'],
            'items.*.surfaces.*.surface' => ['required', 'in:O,M,D,B,L'],
            'items.*.surfaces.*.condition' => ['required', 'in:caries,obturado,sellado'],
            'appointment_id' => ['nullable', 'integer', 'exists:appointments,id'],
        ]);

        DB::transaction(function () use ($data, $odontogram) {
            foreach ($data['items'] as $it) {
                $tooth = OdontogramTooth::updateOrCreate(
                    ['odontogram_id' => $odontogram->id, 'tooth_code' => (string)$it['tooth_code']],
                    ['status' => $it['status'] ?? null, 'notes' => $it['notes'] ?? null]
                );

                $keep = [];
                foreach ($it['surfaces'] ?? [] as $s) {
                    OdontogramSurface::updateOrCreate(
                        ['odontogram_tooth_id' => $tooth->id, 'surface' => $s['surface']],
                        ['condition' => $s['condition']]
                    );
                    $keep[] = $s['surface'];
                }
                if (count($keep)) {
                    OdontogramSurface::where('odontogram_tooth_id', $tooth->id)
                        ->whereNotIn('surface', $keep)->delete();
                } else {
                    OdontogramSurface::where('odontogram_tooth_id', $tooth->id)->delete();
                }
            }
        });

        $redirect = route('admin.odontograms.show', array_filter([
            'odontogram'     => $odontogram->id,
            'appointment_id' => $request->input('appointment_id'),
        ]));

        return response()->json(['ok' => true, 'redirect' => $redirect]);
    }
}
