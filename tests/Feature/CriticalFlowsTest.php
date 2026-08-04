<?php

namespace Tests\Feature;

use App\Models\Chair;
use App\Models\Appointment;
use App\Models\Dentist;
use App\Models\InventoryMovement;
use App\Models\Location;
use App\Models\Patient;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\Schedule;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CriticalFlowsTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_schedule_with_breaks_is_saved_as_json(): void
    {
        $user = $this->userWithPermission('schedules.index');
        $chair = Chair::create(['name' => 'Consultorio QA', 'shift' => 'completo']);
        $dentist = Dentist::create([
            'name' => 'Odontólogo QA',
            'ci' => 'QA-SCHEDULE',
            'chair_id' => $chair->id,
            'status' => true,
        ]);

        $response = $this->actingAs($user)->post(route('admin.schedules.update', $dentist), [
            'schedule' => [
                1 => [[
                    'start_time' => '09:00',
                    'end_time' => '17:00',
                    'chair_id' => $chair->id,
                    'breaks' => '12:00-13:00',
                ]],
            ],
        ]);

        $response->assertRedirect(route('admin.schedules.edit', $dentist));
        $schedule = Schedule::where('dentist_id', $dentist->id)->firstOrFail();

        self::assertSame([['start' => '12:00', 'end' => '13:00']], $schedule->breaks);
    }

    public function test_receipt_uses_service_name_when_description_is_blank(): void
    {
        Carbon::setTestNow('2026-08-04 10:00:00');
        $user = $this->userWithPermission('billing.index');
        $chair = Chair::create(['name' => 'Consultorio QA', 'shift' => 'completo']);
        $dentist = Dentist::create([
            'name' => 'Odontólogo QA',
            'ci' => 'QA-BILLING-DENTIST',
            'chair_id' => $chair->id,
            'status' => true,
        ]);
        $patient = Patient::create([
            'first_name' => 'Paciente',
            'last_name' => 'QA',
            'ci' => 'QA-BILLING-PATIENT',
            'is_active' => true,
        ]);
        $specialtyId = DB::table('specialties')->insertGetId([
            'name' => 'Especialidad QA',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $service = Service::create([
            'name' => 'Servicio QA',
            'duration_min' => 30,
            'price' => 850,
            'active' => true,
            'specialty_id' => $specialtyId,
        ]);
        Schedule::create([
            'dentist_id' => $dentist->id,
            'day_of_week' => 1,
            'start_time' => '09:00',
            'end_time' => '17:00',
            'chair_id' => $chair->id,
            'breaks' => [['start' => '12:00', 'end' => '13:00']],
        ]);

        $response = $this->actingAs($user)->post(route('admin.billing.store'), [
            'patient_id' => $patient->id,
            'discount' => 0,
            'tax_percent' => 0,
            'items' => [[
                'service_id' => $service->id,
                'description' => '',
                'quantity' => 1,
                'unit_price' => 850,
                'dentist_id' => $dentist->id,
                'date' => '2026-08-10',
                'start_time' => '09:00',
            ]],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('invoice_items', [
            'service_id' => $service->id,
            'description' => 'Servicio QA',
            'unit_price' => 850,
        ]);
    }

    public function test_creating_a_dentist_user_assigns_the_dentist_role(): void
    {
        $user = $this->userWithPermission('users.manage');
        $role = Role::create(['name' => 'odontologo', 'label' => 'Odontólogo']);

        $response = $this->actingAs($user)->post(route('admin.dentists.store'), [
            'name' => 'Odontólogo Nuevo QA',
            'ci' => 'QA-NEW-DENTIST',
            'specialty' => 'Odontología general',
            'create_user' => 1,
            'new_user_name' => 'Odontólogo Nuevo QA',
            'new_user_email' => 'new.dentist.qa@example.test',
            'new_user_password' => 'password',
        ]);

        $response->assertRedirect();
        $dentistUser = User::where('email', 'new.dentist.qa@example.test')->firstOrFail();

        self::assertTrue($dentistUser->roles()->whereKey($role->id)->exists());
    }

    public function test_staff_can_reprogram_an_appointment(): void
    {
        Carbon::setTestNow('2026-08-04 10:00:00');
        $user = $this->userWithPermission('appointments.index', 'appointments.update_status');
        $chair = Chair::create(['name' => 'Consultorio QA', 'shift' => 'completo']);
        $dentist = Dentist::create([
            'name' => 'Odontólogo QA',
            'ci' => 'QA-REPROGRAM-DENTIST',
            'chair_id' => $chair->id,
            'status' => true,
        ]);
        $patient = Patient::create([
            'first_name' => 'Paciente',
            'last_name' => 'Reprogramación',
            'ci' => 'QA-REPROGRAM-PATIENT',
            'is_active' => true,
        ]);
        $specialtyId = DB::table('specialties')->insertGetId([
            'name' => 'Especialidad Reprogramación',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $service = Service::create([
            'name' => 'Servicio Reprogramación',
            'duration_min' => 30,
            'price' => 200,
            'active' => true,
            'specialty_id' => $specialtyId,
        ]);
        Schedule::create([
            'dentist_id' => $dentist->id,
            'day_of_week' => 1,
            'start_time' => '09:00',
            'end_time' => '17:00',
            'chair_id' => $chair->id,
        ]);
        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'dentist_id' => $dentist->id,
            'service_id' => $service->id,
            'chair_id' => $chair->id,
            'date' => '2026-08-10',
            'start_time' => '09:00:00',
            'end_time' => '09:30:00',
            'status' => 'reserved',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->put(route('admin.appointments.update', $appointment), [
            'patient_id' => $patient->id,
            'dentist_id' => $dentist->id,
            'service_id' => $service->id,
            'date' => '2026-08-10',
            'start_time' => '09:30',
            'notes' => 'Reprogramada en prueba automática',
        ]);

        $response->assertRedirect(route('admin.appointments.show', $appointment));
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'start_time' => '09:30:00',
            'end_time' => '10:00:00',
        ]);
    }

    public function test_inventory_movements_preserve_the_product_opening_stock(): void
    {
        $user = $this->userWithPermission('inventory.manage');
        $location = Location::create(['name' => 'Depósito QA', 'is_active' => true]);
        $product = Product::create([
            'name' => 'Producto QA',
            'sku' => 'QA-STOCK',
            'unit' => 'unidad',
            'stock' => 30,
            'min_stock' => 0,
            'is_active' => true,
        ]);

        $entry = $this->actingAs($user)->post(route('admin.inv.movs.store'), [
            'product_id' => $product->id,
            'location_id' => $location->id,
            'type' => 'in',
            'qty' => 10,
            'unit_cost' => 25.50,
            'lot_in' => 'QA-LOT',
            'expires_at' => '2027-08-04',
        ]);

        $entry->assertRedirect(route('admin.inv.movs.index'));
        self::assertSame(40, $product->fresh()->stock);

        $exit = $this->actingAs($user)->post(route('admin.inv.movs.store'), [
            'product_id' => $product->id,
            'location_id' => $location->id,
            'type' => 'out',
            'qty' => 4,
            'lot_out' => 'QA-LOT',
        ]);

        $exit->assertRedirect(route('admin.inv.movs.index'));
        self::assertSame(36, $product->fresh()->stock);
        self::assertSame(2, InventoryMovement::where('product_id', $product->id)->count());
    }

    public function test_canceled_appointment_cannot_be_billed(): void
    {
        $user = $this->userWithPermission('billing.index');
        $chair = Chair::create(['name' => 'Consultorio Facturación QA', 'shift' => 'completo']);
        $dentist = Dentist::create([
            'name' => 'Odontólogo Facturación QA',
            'ci' => 'QA-CANCELED-BILLING-DENTIST',
            'chair_id' => $chair->id,
            'status' => true,
        ]);
        $patient = Patient::create([
            'first_name' => 'Paciente',
            'last_name' => 'Cancelado QA',
            'ci' => 'QA-CANCELED-BILLING-PATIENT',
            'is_active' => true,
        ]);
        $specialtyId = DB::table('specialties')->insertGetId([
            'name' => 'Especialidad Facturación QA',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $service = Service::create([
            'name' => 'Servicio Facturación QA',
            'duration_min' => 30,
            'price' => 200,
            'active' => true,
            'specialty_id' => $specialtyId,
        ]);
        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'dentist_id' => $dentist->id,
            'service_id' => $service->id,
            'chair_id' => $chair->id,
            'date' => '2026-08-10',
            'start_time' => '11:00:00',
            'end_time' => '11:30:00',
            'status' => 'canceled',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('admin.invoices.createFromAppointment', $appointment))
            ->assertRedirect(route('admin.appointments.show', $appointment))
            ->assertSessionHas('error');

        $this->actingAs($user)
            ->post(route('admin.invoices.storeFromAppointment', $appointment))
            ->assertRedirect(route('admin.appointments.show', $appointment))
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('invoices', ['appointment_id' => $appointment->id]);
    }

    public function test_appointment_status_cards_do_not_break_the_daily_chart_query(): void
    {
        Carbon::setTestNow('2026-08-04 10:00:00');
        $user = $this->userWithPermission('appointments.index');

        foreach (['reserved', 'confirmed', 'in_service', 'done', 'no_show', 'canceled'] as $status) {
            DB::flushQueryLog();
            DB::enableQueryLog();

            $this->actingAs($user)
                ->get(route('admin.appointments.index', [
                    'date' => '2026-08-04',
                    'status' => $status,
                ]))
                ->assertOk();

            $dailyQuery = collect(DB::getQueryLog())
                ->pluck('query')
                ->first(fn (string $query) => str_contains(
                    strtolower($query),
                    'select date, count(*) as count'
                ));

            self::assertNotNull($dailyQuery, "No se ejecutó la consulta diaria para el estado {$status}.");
            self::assertStringNotContainsString('start_time', strtolower($dailyQuery));

            DB::disableQueryLog();
        }
    }

    private function userWithPermission(string ...$permissionNames): User
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);
        foreach ($permissionNames as $permissionName) {
            $permission = Permission::create([
                'name' => $permissionName,
                'label' => $permissionName,
            ]);
            $user->permissions()->attach($permission);
        }

        return $user;
    }
}
