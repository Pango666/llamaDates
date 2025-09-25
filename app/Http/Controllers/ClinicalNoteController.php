<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\ClinicalNote;
use Illuminate\Http\Request;

class ClinicalNoteController extends Controller
{
    public function index(Appointment $appointment)
    {
        $notes = ClinicalNote::where('appointment_id',$appointment->id)
            ->orderBy('created_at','desc')->get();

        return view('admin.notes.index', compact('appointment','notes'));
    }

    // crear rápido desde la cita (form inline en show)
    public function store(Request $request, Appointment $appointment)
    {
        $data = $request->validate([
            'type'       => ['required','in:SOAP,FREE'],
            'subjective' => ['nullable','string'],
            'objective'  => ['nullable','string'],
            'assessment' => ['nullable','string'],
            'plan'       => ['nullable','string'],
            'vitals'     => ['nullable','array'], // {bp, temp, hr, spo2...}
        ]);

        $note = ClinicalNote::create([
            'patient_id'     => $appointment->patient_id,
            'appointment_id' => $appointment->id,
            'type'       => $data['type'],
            'subjective' => $data['subjective'] ?? null,
            'objective'  => $data['objective'] ?? null,
            'assessment' => $data['assessment'] ?? null,
            'plan'       => $data['plan'] ?? null,
            'vitals'     => $data['vitals'] ?? null,
            'author_id'  => optional(auth()->user())->id,
        ]);

        return back()->with('ok','Nota clínica registrada.');
    }

    public function destroy(ClinicalNote $note)
    {
        $apt = $note->appointment_id;
        $note->delete();
        return redirect()->route('admin.appointments.show',$apt)->with('ok','Nota eliminada.');
    }
}
