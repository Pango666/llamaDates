<?php

namespace Tests\Feature;

use App\Mail\AppointmentConfirmation;
use App\Models\Appointment;
use App\Models\Chair;
use App\Models\Dentist;
use App\Models\Patient;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BotApiTest extends TestCase
{
    use RefreshDatabase;

    private const BOT_KEY = 'kapso-test-secret';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.ceot_bot.key' => self::BOT_KEY]);
        Carbon::setTestNow('2026-08-04 10:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_bot_routes_require_the_integration_key(): void
    {
        $this->getJson('/api/bot/services')->assertUnauthorized();

        $this->withHeader('X-CEOT-Bot-Key', 'wrong-key')
            ->getJson('/api/bot/services')
            ->assertUnauthorized();

        $this->botGet('/api/bot/services')->assertOk();
    }

    public function test_dentists_include_the_specialty_required_by_the_chatbot(): void
    {
        [, $dentist] = $this->dentalFixture();

        $this->botGet('/api/bot/dentists')
            ->assertOk()
            ->assertJsonPath('0.id', $dentist->id)
            ->assertJsonPath('0.specialty', 'Odontologia general');
    }

    public function test_patient_can_be_identified_or_registered_with_a_whatsapp_number(): void
    {
        $patient = $this->patient('QA-BOT-IDENTITY', '71234567');

        $this->botPost('/api/bot/check-patient', [
            'identifier' => '+591 71234567',
        ])->assertOk()
            ->assertJsonPath('exists', true)
            ->assertJsonPath('patient.id', $patient->id)
            ->assertJsonMissingPath('patient.ci');

        $this->botPost('/api/bot/register', [
            'first_name' => 'Nuevo',
            'last_name' => 'Paciente',
            'ci' => 'QA-BOT-NEW',
            'phone' => '+591 76543210',
        ])->assertCreated();

        $this->assertDatabaseHas('patients', [
            'ci' => 'QA-BOT-NEW',
            'phone' => '76543210',
        ]);
    }

    public function test_booking_is_reserved_idempotent_and_only_the_owner_can_confirm_it(): void
    {
        Mail::fake();
        [$patient, $dentist, $service] = $this->appointmentFixture();
        $otherPatient = $this->patient('QA-BOT-OTHER', '70000002');

        $payload = [
            'patient_identifier' => $patient->ci,
            'dentist_id' => $dentist->id,
            'service_id' => $service->id,
            'date' => '2026-08-10',
            'time' => '11:00',
        ];

        $created = $this->botPost('/api/bot/book', $payload)
            ->assertCreated()
            ->assertJsonPath('appointment.status', 'reserved');

        $appointmentId = $created->json('appointment.id');

        $this->botPost('/api/bot/book', $payload)
            ->assertOk()
            ->assertJsonPath('appointment.id', $appointmentId);

        $this->assertDatabaseCount('appointments', 1);

        $this->botPost("/api/bot/appointments/{$appointmentId}/confirm", [
            'patient_identifier' => $otherPatient->ci,
        ])->assertNotFound();

        $this->botPost("/api/bot/appointments/{$appointmentId}/confirm", [
            'patient_identifier' => $patient->ci,
        ])->assertOk()
            ->assertJsonPath('appointment.status', 'confirmed');

        $this->botPost("/api/bot/appointments/{$appointmentId}/confirm", [
            'patient_identifier' => $patient->ci,
        ])->assertOk();

        $this->assertDatabaseHas('appointments', [
            'id' => $appointmentId,
            'status' => 'confirmed',
            'is_active' => true,
        ]);
        Mail::assertSent(AppointmentConfirmation::class, 1);
    }

    public function test_booking_rejects_breaks_and_patient_schedule_conflicts(): void
    {
        [$patient, $dentist, $service, $chair] = $this->appointmentFixture();

        $this->botPost('/api/bot/book', [
            'patient_identifier' => $patient->ci,
            'dentist_id' => $dentist->id,
            'service_id' => $service->id,
            'date' => '2026-08-10',
            'time' => '12:00',
        ])->assertUnprocessable();

        $otherDentist = Dentist::create([
            'name' => 'Odontologo alterno',
            'ci' => 'QA-BOT-DENTIST-2',
            'specialty' => 'Endodoncia',
            'chair_id' => $chair->id,
            'status' => true,
        ]);

        $otherChair = Chair::create(['name' => 'Consultorio alterno', 'shift' => 'completo']);
        DB::table('schedules')->insert([
            'dentist_id' => $otherDentist->id,
            'day_of_week' => 1,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'chair_id' => $otherChair->id,
            'breaks' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->appointment($patient, $otherDentist, $service, $otherChair, 'confirmed', '15:00:00');

        $this->botPost('/api/bot/book', [
            'patient_identifier' => $patient->ci,
            'dentist_id' => $dentist->id,
            'service_id' => $service->id,
            'date' => '2026-08-10',
            'time' => '15:00',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('time');
    }

    public function test_patient_can_cancel_an_owned_future_appointment(): void
    {
        [$patient, $dentist, $service, $chair] = $this->appointmentFixture();
        $appointment = $this->appointment($patient, $dentist, $service, $chair, 'confirmed');

        $this->botPost("/api/bot/appointments/{$appointment->id}/cancel", [
            'patient_identifier' => $patient->phone,
            'reason' => 'No podre asistir',
        ])->assertOk()
            ->assertJsonPath('appointment.status', 'canceled');

        $this->botPost("/api/bot/appointments/{$appointment->id}/cancel", [
            'patient_identifier' => $patient->phone,
        ])->assertOk();

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'canceled',
            'is_active' => false,
            'canceled_reason' => 'No podre asistir',
        ]);
    }

    public function test_patient_can_optionally_include_canceled_and_past_appointments(): void
    {
        [$patient, $dentist, $service, $chair] = $this->appointmentFixture();
        $future = $this->appointment($patient, $dentist, $service, $chair, 'confirmed');
        $canceled = $this->appointment($patient, $dentist, $service, $chair, 'canceled', '14:00:00');
        $past = $this->appointment($patient, $dentist, $service, $chair, 'attended', '09:00:00');
        $past->update(['date' => '2026-08-03']);

        $this->botPost('/api/bot/my-appointments', [
            'patient_identifier' => $patient->ci,
        ])->assertOk()
            ->assertJsonCount(1, 'appointments')
            ->assertJsonPath('appointments.0.id', $future->id);

        $this->botPost('/api/bot/my-appointments', [
            'patient_identifier' => $patient->ci,
            'include_history' => true,
        ])->assertOk()
            ->assertJsonCount(3, 'appointments')
            ->assertJsonFragment(['id' => $canceled->id, 'status' => 'canceled'])
            ->assertJsonFragment(['id' => $past->id, 'status' => 'attended']);
    }

    public function test_reschedule_rechecks_availability_and_returns_to_reserved(): void
    {
        [$patient, $dentist, $service, $chair] = $this->appointmentFixture();
        $appointment = $this->appointment($patient, $dentist, $service, $chair, 'confirmed', '11:00:00');
        $otherPatient = $this->patient('QA-BOT-CONFLICT', '70000003');
        $this->appointment($otherPatient, $dentist, $service, $chair, 'reserved', '14:00:00');

        $this->botPost("/api/bot/appointments/{$appointment->id}/reschedule", [
            'patient_identifier' => $patient->ci,
            'date' => '2026-08-10',
            'time' => '14:00',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('time');

        $this->botPost("/api/bot/appointments/{$appointment->id}/reschedule", [
            'patient_identifier' => $patient->ci,
            'date' => '2026-08-10',
            'time' => '15:00',
        ])->assertOk()
            ->assertJsonPath('appointment.time', '15:00')
            ->assertJsonPath('appointment.status', 'reserved');

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'date' => '2026-08-10 00:00:00',
            'start_time' => '15:00:00',
            'status' => 'reserved',
        ]);
    }

    private function botGet(string $uri)
    {
        return $this->withHeader('X-CEOT-Bot-Key', self::BOT_KEY)->getJson($uri);
    }

    private function botPost(string $uri, array $data)
    {
        return $this->withHeader('X-CEOT-Bot-Key', self::BOT_KEY)->postJson($uri, $data);
    }

    private function appointmentFixture(): array
    {
        [$chair, $dentist, $service] = $this->dentalFixture();
        $patient = $this->patient('QA-BOT-PATIENT', '70000001');

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

        return [$patient, $dentist, $service, $chair];
    }

    private function dentalFixture(): array
    {
        $chair = Chair::create(['name' => 'Consultorio Bot', 'shift' => 'completo']);
        $dentist = Dentist::create([
            'name' => 'Odontologo Bot',
            'ci' => 'QA-BOT-DENTIST',
            'specialty' => 'Odontologia general',
            'chair_id' => $chair->id,
            'status' => true,
        ]);
        $specialtyId = DB::table('specialties')->insertGetId([
            'name' => 'Odontologia general',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $service = Service::create([
            'name' => 'Consulta',
            'duration_min' => 30,
            'price' => 100,
            'active' => true,
            'specialty_id' => $specialtyId,
        ]);

        return [$chair, $dentist, $service];
    }

    private function patient(string $ci, string $phone): Patient
    {
        return Patient::create([
            'first_name' => 'Paciente',
            'last_name' => $ci,
            'ci' => $ci,
            'phone' => $phone,
            'email' => strtolower($ci).'@example.test',
            'is_active' => true,
        ]);
    }

    private function appointment(
        Patient $patient,
        Dentist $dentist,
        Service $service,
        Chair $chair,
        string $status,
        string $startTime = '11:00:00'
    ): Appointment {
        return Appointment::create([
            'patient_id' => $patient->id,
            'dentist_id' => $dentist->id,
            'service_id' => $service->id,
            'chair_id' => $chair->id,
            'date' => '2026-08-10',
            'start_time' => $startTime,
            'end_time' => Carbon::parse($startTime)->addMinutes(30)->format('H:i:s'),
            'status' => $status,
            'is_active' => $status !== 'canceled',
        ]);
    }
}
