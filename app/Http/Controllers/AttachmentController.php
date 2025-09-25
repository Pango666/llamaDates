<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    public function store(Request $request, Appointment $appointment)
    {
        $request->validate([
            'files.*' => ['required','file','max:10240'], // 10MB c/u
            'notes'   => ['nullable','string'],
            'clinical_note_id' => ['nullable','integer'],
            'type'    => ['nullable','string','max:40'],
        ]);

        if (!$request->hasFile('files')) {
            return back()->with('warn','No subiste archivos.');
        }

        foreach ($request->file('files') as $f) {
            $path = $f->store('attachments','public');
            Attachment::create([
                'patient_id'       => $appointment->patient_id,
                'appointment_id'   => $appointment->id,
                'clinical_note_id' => $request->input('clinical_note_id'),
                'type'             => $request->input('type') ?: $f->getClientOriginalExtension(),
                'path'             => $path,
                'original_name'    => $f->getClientOriginalName(),
                'notes'            => $request->input('notes'),
            ]);
        }

        return back()->with('ok','Adjunto(s) cargado(s).');
    }

    public function destroy(Attachment $attachment)
    {
        $apt = $attachment->appointment_id;
        Storage::disk('public')->delete($attachment->path);
        $attachment->delete();
        return redirect()->route('admin.appointments.show',$apt)->with('ok','Adjunto eliminado.');
    }
}
