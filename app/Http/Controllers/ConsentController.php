<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Consent;
use App\Models\ConsentTemplate;
use App\Models\Patient;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ConsentController extends Controller
{
    public function index(Patient $patient)
    {
        $consents = Consent::where('patient_id', $patient->id)->latest()->paginate(12);
        return view('admin.consents.index', compact('patient', 'consents'));
    }

    public function create(Request $request, Patient $patient)
    {
        $templates = ConsentTemplate::orderBy('name')->get();
        $appointment = null;
        if ($request->filled('appointment_id')) {
            $appointment = Appointment::with('dentist')->find($request->appointment_id);
        }
        return view('admin.consents.create', compact('patient', 'templates', 'appointment'));
    }

    public function store(Request $request, Patient $patient)
    {
        $data = $request->validate([
            'title'         => ['required', 'string', 'max:160'],
            'template_id'   => ['nullable', 'exists:consent_templates,id'],
            'body'          => ['nullable', 'string'],
            'to_pdf'        => ['nullable', 'boolean'],
            'appointment_id' => ['nullable', 'exists:appointments,id'], // <-- NUEVO
        ]);

        $appointment = null;
        if (!empty($data['appointment_id'])) {
            $appointment = Appointment::with('dentist')->find($data['appointment_id']);
        }

        // Render del cuerpo (si no mandaron body en crudo)
        $body = $data['body'] ?? '';
        if ($body === '' && !empty($data['template_id'])) {
            $tpl  = ConsentTemplate::find($data['template_id']);
            $body = $this->renderTemplate($tpl->body, $patient, $appointment); // <-- pasa la cita
        } elseif ($body !== '') {
            // Si escribieron el body manual, igual permitimos variables:
            $body = $this->renderTemplate($body, $patient, $appointment);
        }

        $consent = Consent::create([
            'patient_id'      => $patient->id,
            'title'           => $data['title'],
            'body'            => $body,
            'signed_at'       => null,
            'signed_by_name'  => null,
            'signed_by_doc'   => null,
            'signature_path'  => null,
            // si tienes columna appointment_id en consents, guarda; si no, omítelo
            // 'appointment_id'  => $appointment?->id,
        ]);

        return ($request->boolean('to_pdf'))
            ? redirect()->route('admin.consents.pdf', $consent)
            : redirect()->route('admin.consents.show', $consent)->with('ok', 'Consentimiento creado.');
    }


    public function show(Consent $consent)
    {
        $patient = $consent->patient;
        return view('admin.consents.show', compact('consent', 'patient'));
    }

    public function edit(Consent $consent)
    {
        $patient   = $consent->patient;
        $templates = ConsentTemplate::orderBy('name')->get();
        return view('admin.consents.edit', compact('consent', 'patient', 'templates'));
    }

    public function update(Request $request, Consent $consent)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'body'  => ['required', 'string'],
        ]);
        $consent->update($data);
        return redirect()->route('admin.consents.show', $consent)->with('ok', 'Actualizado.');
    }

    public function destroy(Consent $consent)
    {
        if ($consent->file_path) Storage::disk('public')->delete($consent->file_path);
        if ($consent->signature_path) Storage::disk('public')->delete($consent->signature_path);
        $patient = $consent->patient;
        $consent->delete();
        return redirect()->route('admin.patients.consents.index', $patient)->with('ok', 'Eliminado.');
    }

    public function print(Consent $consent)
    {
        $patient = $consent->patient;
        return view('admin.consents.print', compact('consent', 'patient'));
    }

    public function pdf(Consent $consent)
    {
        $consent->load(['patient', 'appointment.dentist']);
        // Asegura que el body esté renderizado con el doctor correcto incluso si se guardó sin cita
        $html = $this->renderTemplate($consent->body, $consent->patient, $consent->appointment);

        if (!class_exists(Pdf::class)) {
            // fallback a vista imprimible si no tienes dompdf instalado
            return view('admin.consents.print', ['consent' => $consent, 'html' => nl2br(e($html))]);
        }

        $pdf = Pdf::loadView('admin.consents.print', [
            'consent' => $consent,
            'html'    => $html, // en tu blade usa {!! $html !!} para respetar saltos/formatos
        ])->setPaper('a4');

        return $pdf->download('consentimiento_' . $consent->id . '.pdf');
    }

    public function uploadScan(Request $request, Consent $consent)
    {
        $data = $request->validate([
            'signed_by_name' => ['nullable', 'string', 'max:120'],
            'signed_by_doc'  => ['nullable', 'string', 'max:60'],
            'scan'           => ['required', 'file', 'mimes:pdf,jpg,jpeg,png'],
        ]);

        $path = $request->file('scan')->store("consents/{$consent->id}", 'public');

        $consent->update([
            'signed_at'      => now(),
            'signed_by_name' => $data['signed_by_name'] ?? $consent->signed_by_name,
            'signed_by_doc'  => $data['signed_by_doc'] ?? $consent->signed_by_doc,
            'file_path'      => $path,
        ]);

        return back()->with('ok', 'Escaneo guardado y marcado como firmado.');
    }

    public function uploadSigned(Request $request, Consent $consent)
    {
        $data = $request->validate([
            'signed_by_name' => ['required', 'string', 'max:120'],
            'signed_by_doc'  => ['nullable', 'string', 'max:60'],
            'scan'           => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $file = $request->file('scan');
        $path = $file->store("consents/signed", 'public');

        $consent->update([
            'signed_by_name' => $data['signed_by_name'],
            'signed_by_doc'  => $data['signed_by_doc'] ?? null,
            'signature_path' => $path,
            'signed_at'      => now(),
        ]);

        return back()->with('ok', 'Firmado/escaneado adjuntado.');
    }

    private function renderTemplate(string $tpl, Patient $patient, ?\App\Models\Appointment $appointment = null): string
    {
        $doctorName = null;

        if ($appointment && $appointment->relationLoaded('dentist')) {
            $doctorName = $appointment->dentist?->name;
        }
        // Fallback: último odontólogo que atendió al paciente (si quieres)
        if (!$doctorName) {
            $lastAppt = Appointment::with('dentist')
                ->where('patient_id', $patient->id)
                ->whereNotNull('dentist_id')
                ->latest('date')->latest('start_time')
                ->first();
            $doctorName = $lastAppt?->dentist?->name;
        }
        // Último fallback: no usar Admin; deja vacío o usa un guion
        $doctorName = $doctorName ?: '';

        $map = [
            '{{patient.full_name}}' => trim($patient->first_name . ' ' . $patient->last_name),
            '{{patient.ci}}'        => (string)($patient->ci ?? ''),
            '{{doctor.name}}'       => $doctorName,
            '{{today}}'             => now()->format('Y-m-d'),
        ];

        // Reemplazo simple y seguro
        return strtr($tpl, $map);
    }
}
