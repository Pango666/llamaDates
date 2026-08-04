<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Chair;
use App\Models\Dentist;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Odontogram;
use App\Models\OdontogramSurface;
use App\Models\OdontogramTooth;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MobileApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['jwt.secret' => str_repeat('a', 64)]);
        Carbon::setTestNow('2026-08-04 10:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_mobile_login_only_accepts_linked_patient_accounts(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin-mobile-qa@example.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->postJson('/api/v1/mobile/login', [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertForbidden();

        $this->withToken(auth('api')->login($admin))
            ->getJson('/api/v1/mobile/me')
            ->assertForbidden();

        [$patientUser, $patient] = $this->patientAccount('patient-mobile-qa@example.test', 'QA-MOBILE-LOGIN');
        $token = $this->tokenFor($patientUser);

        $this->withToken($token)
            ->getJson('/api/v1/mobile/me')
            ->assertOk()
            ->assertJsonPath('patient.id', $patient->id);
    }

    public function test_mobile_history_never_leaks_other_patients_appointments(): void
    {
        [$patientUser, $patient] = $this->patientAccount('history-a@example.test', 'QA-MOBILE-HISTORY-A');
        [, $otherPatient] = $this->patientAccount('history-b@example.test', 'QA-MOBILE-HISTORY-B');
        [$chair, $dentist, $service] = $this->dentalFixture('HISTORY');

        $ownAppointment = $this->appointment($patient, $dentist, $service, $chair, 'canceled');
        $this->appointment($otherPatient, $dentist, $service, $chair, 'canceled', '10:00:00');

        $this->withToken($this->tokenFor($patientUser))
            ->getJson('/api/v1/mobile/appointments?status=history')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $ownAppointment->id)
            ->assertJsonPath('data.0.patient_id', $patient->id);
    }

    public function test_mobile_invoice_and_odontogram_contracts_use_real_fields(): void
    {
        [$patientUser, $patient] = $this->patientAccount('clinical-mobile@example.test', 'QA-MOBILE-CLINICAL');

        $invoice = Invoice::create([
            'number' => 'QA-MOB-0001',
            'patient_id' => $patient->id,
            'status' => 'issued',
            'discount' => 0,
            'tax_percent' => 0,
            'issued_at' => now(),
            'created_by' => $patientUser->id,
        ]);
        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'description' => 'Consulta móvil QA',
            'quantity' => 1,
            'unit_price' => 250,
            'total' => 250,
        ]);

        $odontogram = Odontogram::create([
            'patient_id' => $patient->id,
            'date' => now()->toDateString(),
            'created_by' => $patientUser->id,
        ]);
        $tooth = OdontogramTooth::create([
            'odontogram_id' => $odontogram->id,
            'tooth_code' => '26',
        ]);
        OdontogramSurface::create([
            'odontogram_tooth_id' => $tooth->id,
            'surface' => 'O',
            'condition' => 'sellado',
        ]);

        $token = $this->tokenFor($patientUser);

        $this->withToken($token)
            ->getJson('/api/v1/mobile/invoices/'.$invoice->id)
            ->assertOk()
            ->assertJsonPath('items.0.description', 'Consulta móvil QA')
            ->assertJsonPath('items.0.price', 250);

        $this->withToken($token)
            ->getJson('/api/v1/mobile/odontogram')
            ->assertOk()
            ->assertJsonPath('teeth.0.code', '26')
            ->assertJsonPath('teeth.0.surfaces.0.surface', 'O')
            ->assertJsonPath('teeth.0.surfaces.0.condition', 'sellado');
    }

    public function test_mobile_booking_respects_schedule_and_appointment_show_has_chair(): void
    {
        Mail::fake();
        [$patientUser] = $this->patientAccount('booking-mobile@example.test', 'QA-MOBILE-BOOKING');
        [$chair, $dentist, $service] = $this->dentalFixture('BOOKING');

        DB::table('schedules')->insert([
            'dentist_id' => $dentist->id,
            'day_of_week' => 1,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'chair_id' => $chair->id,
            'breaks' => json_encode([['start' => '12:00', 'end' => '13:00']]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $token = $this->tokenFor($patientUser);
        $payload = [
            'dentist_id' => $dentist->id,
            'service_id' => $service->id,
            'date' => '2026-08-10',
        ];

        $this->withToken($token)
            ->postJson('/api/v1/mobile/appointments', $payload + ['time' => '18:00'])
            ->assertUnprocessable();

        $created = $this->withToken($token)
            ->postJson('/api/v1/mobile/appointments', $payload + ['time' => '11:00'])
            ->assertCreated();

        $appointmentId = $created->json('appointment.id');

        $this->withToken($token)
            ->getJson('/api/v1/mobile/appointments/'.$appointmentId)
            ->assertOk()
            ->assertJsonPath('chair.id', $chair->id);
    }

    private function patientAccount(string $email, string $ci): array
    {
        $user = User::factory()->create([
            'name' => 'Paciente móvil QA',
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => 'paciente',
            'status' => 'active',
        ]);
        $patient = Patient::create([
            'user_id' => $user->id,
            'first_name' => 'Paciente',
            'last_name' => 'Móvil QA',
            'ci' => $ci,
            'email' => $email,
            'is_active' => true,
        ]);

        return [$user, $patient];
    }

    private function tokenFor(User $user): string
    {
        return (string) $this->postJson('/api/v1/mobile/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertOk()->json('access_token');
    }

    private function dentalFixture(string $suffix): array
    {
        $chair = Chair::create(['name' => 'Consultorio '.$suffix, 'shift' => 'completo']);
        $dentist = Dentist::create([
            'name' => 'Odontólogo '.$suffix,
            'ci' => 'QA-MOBILE-DENTIST-'.$suffix,
            'chair_id' => $chair->id,
            'status' => true,
        ]);
        $specialtyId = DB::table('specialties')->insertGetId([
            'name' => 'Especialidad '.$suffix,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $service = Service::create([
            'name' => 'Servicio '.$suffix,
            'duration_min' => 30,
            'price' => 250,
            'active' => true,
            'specialty_id' => $specialtyId,
        ]);

        return [$chair, $dentist, $service];
    }

    private function appointment(
        Patient $patient,
        Dentist $dentist,
        Service $service,
        Chair $chair,
        string $status,
        string $startTime = '09:00:00'
    ): Appointment {
        $endTime = Carbon::parse($startTime)->addMinutes(30)->format('H:i:s');

        return Appointment::create([
            'patient_id' => $patient->id,
            'dentist_id' => $dentist->id,
            'service_id' => $service->id,
            'chair_id' => $chair->id,
            'date' => '2026-08-10',
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => $status,
            'is_active' => $status !== 'canceled',
        ]);
    }
}
