<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Attachment;
use App\Models\ClinicalNote;
use App\Models\Consent;
use App\Models\Diagnosis;
use App\Models\MedicalHistory;
use App\Models\Odontogram;
use App\Models\Patient;
use App\Models\Treatment;
use App\Models\TreatmentPlan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class MedicalHistoryController extends Controller
{
    public function edit(Patient $patient)
    {
        $history = MedicalHistory::firstOrCreate(
            ['patient_id' => $patient->id],
            [
                'smoker' => false,
                'pregnant' => null,
                'allergies' => null,
                'medications' => null,
                'systemic_diseases' => null,
                'surgical_history' => null,
                'habits' => null,
                'extra' => null,
            ]
        );

        return view('admin.patients.history', compact('patient', 'history'));
    }

    public function update(Request $r, Patient $patient)
    {
        $v = $r->validate([
            'smoker'            => 'nullable|boolean',
            'pregnant'          => 'nullable|boolean',
            'allergies'         => 'nullable|string',
            'medications'       => 'nullable|string',
            'systemic_diseases' => 'nullable|string',
            'surgical_history'  => 'nullable|string',
            'habits'            => 'nullable|string',
            'extra'             => 'nullable|array', // desde el form lo parseamos si mandamos JSON
        ]);

        // Normaliza checkboxes
        $v['smoker']   = $r->boolean('smoker');
        // solo pacientes mujer? dejamos nullable a elección clínica:
        $v['pregnant'] = $r->has('pregnant') ? $r->boolean('pregnant') : null;

        // Si “extra” viene como texto JSON, inténtalo decodificar
        if ($r->filled('extra_json')) {
            $decoded = json_decode($r->input('extra_json'), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $v['extra'] = $decoded;
            }
        }

        $history = MedicalHistory::firstOrCreate(['patient_id' => $patient->id]);
        $history->update($v);

        return redirect()
            ->route('admin.patients.history', $patient)
            ->with('ok', 'Historia clínica actualizada.');
    }

    public function show(Patient $patient)
    {
        $events = collect();

        // Citas
        $appts = Appointment::with(['dentist:id,name', 'service:id,name'])
            ->where('patient_id', $patient->id)
            ->orderBy('date')->orderBy('start_time')->get();

        foreach ($appts as $a) {
            $day  = $a->date instanceof Carbon ? $a->date->copy() : Carbon::parse($a->date);
            $time = strlen($a->start_time) === 5 ? $a->start_time . ':00' : $a->start_time; // H:i:s
            $ts   = $day->setTimeFromTimeString($time);

            $events->push([
                'ts'     => $ts,
                'type'   => 'appointment',
                'title'  => 'Cita: ' . $a->service?->name,
                'meta'   => 'Con ' . $a->dentist?->name . ' · ' . substr($a->start_time, 0, 5) . '–' . substr($a->end_time, 0, 5),
                'status' => $a->status,
            ]);
        }

        // Notas clínicas (SOAP)
        $notes = ClinicalNote::where('patient_id', $patient->id)->orderBy('created_at')->get();
        foreach ($notes as $n) {
            $events->push([
                'ts'    => $n->created_at ?? now(),
                'type'  => 'note',
                'title' => 'Nota clínica (' . $n->type . ')',
                'meta'  => trim(($n->assessment ? 'Dx: ' . $n->assessment . ' · ' : '') . ($n->plan ? 'Plan: ' . $n->plan : '')),
            ]);
        }

        // Diagnósticos
        $dx = Diagnosis::where('patient_id', $patient->id)->orderBy('created_at')->get();
        foreach ($dx as $d) {
            $tooth = $d->tooth_code ? ' (pieza ' . $d->tooth_code . ($d->surface ? ' ' . $d->surface : '') . ')' : '';
            $events->push([
                'ts'     => $d->created_at ?? now(),
                'type'   => 'diagnosis',
                'title'  => 'Diagnóstico: ' . $d->label . $tooth,
                'meta'   => $d->code ? 'CIE: ' . $d->code : null,
                'status' => $d->status,
            ]);
        }

        // Tratamientos (por planes)
        $planIds = TreatmentPlan::where('patient_id', $patient->id)->pluck('id');
        if ($planIds->count()) {
            $treats = Treatment::with('service:id,name')
                ->whereIn('treatment_plan_id', $planIds)
                ->orderBy('created_at')->get();

            foreach ($treats as $t) {
                $tooth = $t->tooth_code ? ' (pieza ' . $t->tooth_code . ($t->surface ? ' ' . $t->surface : '') . ')' : '';
                $events->push([
                    'ts'     => $t->created_at ?? now(),
                    'type'   => 'treatment',
                    'title'  => 'Tratamiento: ' . $t->service?->name . $tooth,
                    'meta'   => 'Estado: ' . $t->status . ' · Bs ' . $t->price,
                    'status' => $t->status,
                ]);
            }
        }

        // ⭐ Odontogramas (compatibles con la vista)
        $odos = Odontogram::with(['teeth.surfaces'])
            ->where('patient_id', $patient->id)
            ->orderBy('date')
            ->orderBy('created_at') // primero cronológico ascendente para poder comparar
            ->get();

        $prev = null;
        foreach ($odos as $o) {
            // timestamp seguro (evita "double time"): prioriza 'date' y si no 'created_at'
            $ts = $o->date ? \Carbon\Carbon::parse($o->date) : ($o->created_at ?? now());

            // ==== DIFF vs anterior ====
            $currTeeth = $o->teeth->mapWithKeys(function ($t) {
                return [
                    (string)$t->tooth_code => [
                        'status'   => $t->status, // sano | ausente | null
                        'surfaces' => $t->surfaces->mapWithKeys(fn($s) => [$s->surface => $s->condition])->toArray(), // O,M,D,B,L => caries/obturado/sellado
                    ]
                ];
            })->toArray();

            $diffText = 'Registro inicial';
            if ($prev) {
                $prevTeeth = $prev->teeth->mapWithKeys(function ($t) {
                    return [
                        (string)$t->tooth_code => [
                            'status'   => $t->status,
                            'surfaces' => $t->surfaces->mapWithKeys(fn($s) => [$s->surface => $s->condition])->toArray(),
                        ]
                    ];
                })->toArray();

                $changedCount = 0;
                $added = ['caries' => 0, 'obturado' => 0, 'sellado' => 0, 'ausente' => 0];
                $removed = ['caries' => 0, 'obturado' => 0, 'sellado' => 0, 'ausente' => 0];

                $allCodes = array_unique(array_merge(array_keys($prevTeeth), array_keys($currTeeth)));
                foreach ($allCodes as $code) {
                    $a = $prevTeeth[$code] ?? ['status' => null, 'surfaces' => []];
                    $b = $currTeeth[$code] ?? ['status' => null, 'surfaces' => []];

                    // cambio de estado de la pieza (sano/ausente/null)
                    if (($a['status'] ?? null) !== ($b['status'] ?? null)) {
                        $changedCount++;
                        if (($b['status'] ?? null) === 'ausente') $added['ausente']++;
                        if (($a['status'] ?? null) === 'ausente' && ($b['status'] ?? null) !== 'ausente') $removed['ausente']++;
                    }

                    // superficies: entradas y salidas
                    foreach (['O', 'M', 'D', 'B', 'L'] as $sf) {
                        $pa = $a['surfaces'][$sf] ?? null; // condition en anterior
                        $pb = $b['surfaces'][$sf] ?? null; // condition en actual
                        if ($pa !== $pb) {
                            $changedCount++;
                            if ($pb)  $added[$pb] = ($added[$pb] ?? 0) + 1;      // apareció una condición
                            if ($pa)  $removed[$pa] = ($removed[$pa] ?? 0) + 1;  // desapareció
                        }
                    }
                }

                // construye texto compacto
                $chunks = [];
                if ($changedCount === 0) {
                    $diffText = 'Sin cambios respecto al anterior';
                } else {
                    if ($added['caries']   || $removed['caries'])   $chunks[] = 'caries +' . $added['caries'] . '/−' . $removed['caries'];
                    if ($added['obturado'] || $removed['obturado']) $chunks[] = 'obturados +' . $added['obturado'] . '/−' . $removed['obturado'];
                    if ($added['sellado']  || $removed['sellado'])  $chunks[] = 'sellados +' . $added['sellado'] . '/−' . $removed['sellado'];
                    if ($added['ausente']  || $removed['ausente'])  $chunks[] = 'ausentes +' . $added['ausente'] . '/−' . $removed['ausente'];
                    $diffText = 'Cambios: ' . $changedCount . ' (' . implode(', ', array_filter($chunks)) . ')';
                }
            }

            $events->push([
                'ts'    => $ts,
                'type'  => 'odontogram',
                'title' => 'Odontograma',
                'meta'  => $diffText,
                'url'   => route('admin.odontograms.show', $o),
            ]);

            $prev = $o;
        }

        // Consentimientos
        $cons = Consent::where('patient_id', $patient->id)->orderBy('created_at')->get();
        foreach ($cons as $c) {
            $events->push([
                'ts'     => $c->signed_at ?? $c->created_at ?? now(),
                'type'   => 'consent',
                'title'  => 'Consentimiento: ' . $c->title,
                'meta'   => $c->signed_at ? 'Firmado' : 'Pendiente de firma',
                'status' => $c->signed_at ? 'signed' : 'pending',
            ]);
        }

        // Adjuntos
        $atts = Attachment::where('patient_id', $patient->id)->orderBy('created_at')->get();
        foreach ($atts as $a) {
            $events->push([
                'ts'    => $a->created_at ?? now(),
                'type'  => 'attachment',
                'title' => 'Adjunto: ' . $a->original_name,
                'meta'  => $a->type ?: 'archivo',
            ]);
        }

        // Pagos (si existe tabla y modelo)
        if (Schema::hasTable('payments') && class_exists(\App\Models\Payment::class)) {
            $paymentsQ = \App\Models\Payment::query();
            $orderCol  = Schema::hasColumn('payments', 'paid_at') ? 'paid_at' : 'created_at';

            if (Schema::hasColumn('payments', 'patient_id')) {
                $paymentsQ->where('patient_id', $patient->id);
            } elseif (Schema::hasColumn('payments', 'appointment_id')) {
                $apptIds = Appointment::where('patient_id', $patient->id)->pluck('id');
                $paymentsQ->whereIn('appointment_id', $apptIds);
            } elseif (Schema::hasColumn('payments', 'treatment_plan_id')) {
                $planIds = TreatmentPlan::where('patient_id', $patient->id)->pluck('id');
                $paymentsQ->whereIn('treatment_plan_id', $planIds);
            } else {
                $paymentsQ = null;
            }

            if ($paymentsQ) {
                $payments = $paymentsQ->orderBy($orderCol)->get();
                foreach ($payments as $p) {
                    $ts     = $p->{$orderCol} ?? $p->created_at ?? now();
                    $amount = $p->amount ?? $p->total ?? $p->value ?? 0;
                    $method = Schema::hasColumn('payments', 'method')
                        ? $p->method
                        : (Schema::hasColumn('payments', 'method_id') ? ('Método #' . $p->method_id) : null);

                    $events->push([
                        'ts'     => $ts,
                        'type'   => 'payment',
                        'title'  => 'Pago',
                        'meta'   => 'Bs ' . number_format((float)$amount, 2) . ($method ? ' · ' . $method : ''),
                        'status' => 'paid',
                    ]);
                }
            }
        }

        $events = $events->sortByDesc('ts')->values();

        return view('admin.patients.record', compact('patient', 'events'));
    }
}
