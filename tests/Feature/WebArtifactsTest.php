<?php

namespace Tests\Feature;

use App\Mail\AppointmentConfirmation;
use App\Models\Appointment;
use App\Models\Attachment;
use App\Models\Chair;
use App\Models\Consent;
use App\Models\Dentist;
use App\Models\Patient;
use App\Models\Permission;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WebArtifactsTest extends TestCase
{
    use RefreshDatabase;

    public function test_consent_keeps_appointment_link_and_generates_a_valid_pdf(): void
    {
        Storage::fake('public');
        $user = $this->userWithPermission('medical_history.manage');
        [$patient, $appointment] = $this->clinicalFixture();

        $this->actingAs($user)->post(route('admin.patients.consents.store', $patient), [
            'title' => 'Consentimiento QA',
            'body' => 'Paciente: {{patient.full_name}} - Odontólogo: {{doctor.name}}',
            'appointment_id' => $appointment->id,
        ])->assertRedirect();

        $consent = Consent::where('patient_id', $patient->id)->firstOrFail();
        self::assertSame($appointment->id, $consent->appointment_id);
        self::assertStringContainsString('Odontólogo QA', $consent->body);

        $pdf = $this->actingAs($user)->get(route('admin.consents.pdf', $consent));
        $pdf->assertOk()->assertHeader('content-type', 'application/pdf');
        self::assertStringStartsWith('%PDF', $pdf->getContent());

        $this->actingAs($user)->post(route('admin.consents.uploadSigned', $consent), [
            'signed_by_name' => 'Paciente QA',
            'signed_by_doc' => 'QA-DOC',
            'scan' => UploadedFile::fake()->create('consentimiento-firmado.pdf', 32, 'application/pdf'),
        ])->assertRedirect();

        $consent->refresh();
        self::assertNotNull($consent->signed_at);
        Storage::disk('public')->assertExists($consent->signature_path);
    }

    public function test_clinical_attachment_is_stored_with_its_metadata(): void
    {
        Storage::fake('public');
        $user = $this->userWithPermission('clinical_notes.manage');
        [, $appointment] = $this->clinicalFixture();
        $file = UploadedFile::fake()->create('adjunto-qa.pdf', 32, 'application/pdf');

        $this->actingAs($user)->post(route('admin.appointments.attachments.store', $appointment), [
            'files' => [$file],
            'notes' => 'Adjunto QA',
            'type' => 'pdf',
        ])->assertRedirect();

        $attachment = Attachment::where('appointment_id', $appointment->id)->firstOrFail();
        self::assertSame('adjunto-qa.pdf', $attachment->original_name);
        self::assertSame('Adjunto QA', $attachment->notes);
        Storage::disk('public')->assertExists($attachment->path);
    }

    public function test_inventory_exports_return_csv_and_pdf_files(): void
    {
        $user = $this->userWithPermission('inventory.manage');

        $csv = $this->actingAs($user)->get(route('admin.inv.movs.export.csv'));
        $csv->assertOk();
        self::assertStringContainsString('text/csv', (string) $csv->headers->get('content-type'));
        self::assertStringContainsString('Producto', $csv->streamedContent());

        $pdf = $this->actingAs($user)->get(route('admin.inv.movs.export.pdf'));
        $pdf->assertOk();
        self::assertStringContainsString('application/pdf', (string) $pdf->headers->get('content-type'));
        self::assertStringStartsWith('%PDF', $pdf->getContent());
    }

    public function test_billing_report_is_a_valid_pdf_download(): void
    {
        $user = $this->userWithPermission('billing.index');

        $pdf = $this->actingAs($user)->get(route('admin.billing.pdf'));

        $pdf->assertOk();
        self::assertStringContainsString('application/pdf', (string) $pdf->headers->get('content-type'));
        self::assertStringStartsWith('%PDF', $pdf->getContent());
    }

    public function test_appointment_confirmation_email_shows_the_patient_name(): void
    {
        [$patient, $appointment] = $this->clinicalFixture();

        $html = (new AppointmentConfirmation($appointment->load(['patient', 'dentist', 'service'])))->render();

        self::assertStringContainsString($patient->full_name, $html);
    }

    private function userWithPermission(string $permissionName): User
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);
        $permission = Permission::create([
            'name' => $permissionName,
            'label' => $permissionName,
        ]);
        $user->permissions()->attach($permission);

        return $user;
    }

    private function clinicalFixture(): array
    {
        $chair = Chair::create(['name' => 'Consultorio Artefactos QA', 'shift' => 'completo']);
        $dentist = Dentist::create([
            'name' => 'Odontólogo QA',
            'ci' => 'QA-ARTIFACT-DENTIST-'.uniqid(),
            'chair_id' => $chair->id,
            'status' => true,
        ]);
        $patient = Patient::create([
            'first_name' => 'Paciente',
            'last_name' => 'Artefactos QA',
            'ci' => 'QA-ARTIFACT-PATIENT-'.uniqid(),
            'is_active' => true,
        ]);
        $specialtyId = DB::table('specialties')->insertGetId([
            'name' => 'Especialidad Artefactos '.uniqid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $service = Service::create([
            'name' => 'Servicio Artefactos QA',
            'duration_min' => 30,
            'price' => 300,
            'active' => true,
            'specialty_id' => $specialtyId,
        ]);
        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'dentist_id' => $dentist->id,
            'service_id' => $service->id,
            'chair_id' => $chair->id,
            'date' => '2026-08-10',
            'start_time' => '15:00:00',
            'end_time' => '15:30:00',
            'status' => 'in_service',
            'is_active' => true,
        ]);

        return [$patient, $appointment];
    }
}
