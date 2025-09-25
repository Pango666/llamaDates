<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Diagnosis;
use Illuminate\Http\Request;

class DiagnosisController extends Controller
{
    public function store(Request $request, Appointment $appointment)
    {
        $data = $request->validate([
            'label'      => ['required','string','max:160'],
            'code'       => ['nullable','string','max:20'],
            'tooth_code' => ['nullable','string','max:3'],
            'surface'    => ['nullable','in:O,M,D,B,L,I'],
            'status'     => ['required','in:active,resolved'],
            'notes'      => ['nullable','string'],
            'clinical_note_id' => ['nullable','integer'],
        ]);

        Diagnosis::create([
            'patient_id'       => $appointment->patient_id,
            'appointment_id'   => $appointment->id,
            'clinical_note_id' => $data['clinical_note_id'] ?? null,
            'label'      => $data['label'],
            'code'       => $data['code'] ?? null,
            'tooth_code' => $data['tooth_code'] ?? null,
            'surface'    => $data['surface'] ?? null,
            'status'     => $data['status'],
            'notes'      => $data['notes'] ?? null,
        ]);

        return back()->with('ok','Diagnóstico agregado.');
    }

    public function destroy(Diagnosis $diagnosis)
    {
        $apt = $diagnosis->appointment_id;
        $diagnosis->delete();
        return redirect()->route('admin.appointments.show',$apt)->with('ok','Diagnóstico eliminado.');
    }
}
