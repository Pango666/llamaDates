<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;

// Models
use App\Models\User;
use App\Models\Chair;
use App\Models\Dentist;
use App\Models\Service;
use App\Models\Patient;
use App\Models\Schedule;
use App\Models\Appointment;
use App\Models\MedicalHistory;
use App\Models\TreatmentPlan;
use App\Models\Treatment;
use App\Models\Diagnosis;
use App\Models\ClinicalNote;
use App\Models\Attachment;
use App\Models\Consent;
use App\Models\Odontogram;
use App\Models\OdontogramTooth;
use App\Models\OdontogramSurface;

// INVENTARIO
use App\Models\Product;
use App\Models\Supplier;
use App\Models\ProductCategory;
use App\Models\ProductPresentationUnit;
use App\Models\MeasurementUnit;
use App\Models\Location;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create();
        $today = Carbon::today();

        // ---------- Usuarios ----------
        $admin = User::updateOrCreate(
            ['email' => 'admin@demo.test'],
            ['name' => 'Admin', 'password' => Hash::make('password'), 'role' => 'admin', 'status' => 'active']
        );

        $asist = User::updateOrCreate(
            ['email' => 'asist@demo.test'],
            ['name' => 'Asistente', 'password' => Hash::make('password'), 'role' => 'asistente', 'status' => 'active']
        );

        $uJuan = User::updateOrCreate(
            ['email' => 'dr.juan@demo.test'],
            ['name' => 'Dr. Juan Pérez', 'password' => Hash::make('password'), 'role' => 'odontologo', 'status' => 'active']
        );
        $uAna = User::updateOrCreate(
            ['email' => 'dra.ana@demo.test'],
            ['name' => 'Dra. Ana Díaz', 'password' => Hash::make('password'), 'role' => 'odontologo', 'status' => 'active']
        );

        // ---------- Sillones ----------
        $chair1 = Chair::firstOrCreate(['name' => 'Sillón 1'], ['shift' => 'completo']);
        $chair2 = Chair::firstOrCreate(['name' => 'Sillón 2'], ['shift' => 'completo']);
        $chair3 = Chair::firstOrCreate(['name' => 'Sillón 3'], ['shift' => 'completo']);

        // ---------- Odontólogos ----------
        $dentJuan = Dentist::updateOrCreate(
            ['name' => 'Dr. Juan Pérez'],
            array_filter([
                'user_id'   => $uJuan->id,
                'chair_id'  => $chair1->id,
                'specialty' => Schema::hasColumn('dentists', 'specialty') ? 'Odontología General' : null,
            ], fn($v) => !is_null($v))
        );

        $dentAna = Dentist::updateOrCreate(
            ['name' => 'Dra. Ana Díaz'],
            array_filter([
                'user_id'   => $uAna->id,
                'chair_id'  => $chair2->id,
                'specialty' => Schema::hasColumn('dentists', 'specialty') ? 'Endodoncia' : null,
            ], fn($v) => !is_null($v))
        );

        // ---------- Servicios ----------
        $servicesData = [
            ['name' => 'Consulta',            'duration_min' => 30, 'price' => 100, 'active' => true],
            ['name' => 'Limpieza',            'duration_min' => 45, 'price' => 250, 'active' => true],
            ['name' => 'Endodoncia',          'duration_min' => 60, 'price' => 600, 'active' => true],
            ['name' => 'Control Ortodoncia',  'duration_min' => 20, 'price' => 60, 'active' => true],
            ['name' => 'Blanqueamiento',      'duration_min' => 90, 'price' => 350, 'active' => true],
        ];
        $services = collect();
        foreach ($servicesData as $sd) {
            $services->push(Service::updateOrCreate(['name' => $sd['name']], $sd));
        }

        // ---------- Horarios (L–V: 09–13 / 14:30–18) ----------
        foreach ([$dentJuan, $dentAna] as $dentist) {
            foreach ([1, 2, 3, 4, 5] as $dow) { // 1=Lunes ... 5=Viernes
                Schedule::firstOrCreate([
                    'dentist_id' => $dentist->id,
                    'day_of_week' => $dow,
                    'start_time' => '09:00',
                    'end_time' => '13:00'
                ], ['breaks' => []]);

                Schedule::firstOrCreate([
                    'dentist_id' => $dentist->id,
                    'day_of_week' => $dow,
                    'start_time' => '14:30',
                    'end_time' => '18:00'
                ], ['breaks' => []]);
            }
        }

        // ---------- Pacientes ----------
        $patients = collect();
        $patients->push(Patient::updateOrCreate([
            'email' => 'maria@demo.test'
        ], [
            'first_name' => 'María',
            'last_name' => 'Gómez',
            'phone' => '70000000',
            'birthdate' => Carbon::parse('1995-05-10'),
            'address' => 'Av. Siempre Viva 123'
        ]));

        for ($i = 0; $i < 9; $i++) {
            $patients->push(Patient::create([
                'first_name' => $faker->firstName,
                'last_name' => $faker->lastName,
                'email' => $faker->unique()->safeEmail(),
                'phone' => $faker->numerify('7#######'),
                'birthdate' => $faker->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
                'address' => $faker->address()
            ]));
        }

        // Vincular/crear usuarios para pacientes sin user_id
        foreach ($patients as $p) {
            if (!$p->user_id) {
                $email = $p->email ?: "pac{$p->id}@demo.test";
                $user  = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name'     => trim(($p->first_name ?? '') . ' ' . ($p->last_name ?? '')) ?: "Paciente {$p->id}",
                        'password' => Hash::make('password'),
                        'role'     => 'paciente',
                        'status'   => 'active',
                    ]
                );
                $p->user_id = $user->id;
                $p->save();
            }
        }

        // ---------- Historia Clínica por paciente ----------
        foreach ($patients as $p) {
            MedicalHistory::firstOrCreate(
                ['patient_id' => $p->id],
                [
                    'smoker' => $faker->boolean(20),
                    'pregnant' => null,
                    'allergies' => $faker->boolean(30) ? 'Penicilina' : null,
                    'medications' => $faker->boolean(40) ? 'Ibuprofeno ocasional' : null,
                    'systemic_diseases' => $faker->boolean(20) ? 'Hipertensión controlada' : null,
                    'surgical_history' => $faker->boolean(20) ? 'Apendicectomía 2010' : null,
                    'habits' => $faker->boolean(50) ? 'Café diario' : null,
                    'extra' => ['bp' => $faker->numberBetween(110, 140) . '/' . $faker->numberBetween(70, 90)],
                ]
            );
        }

        // ---------- Citas próximas (siguientes 7 días) ----------
        $makeSlots = function (int $dentistId, Carbon $date, int $durationMin) {
            $scheds = Schedule::where('dentist_id', $dentistId)->where('day_of_week', $date->dayOfWeek)->get();
            $slots = [];
            foreach ($scheds as $s) {
                $cur = Carbon::parse($date->toDateString() . ' ' . $s->start_time);
                $end = Carbon::parse($date->toDateString() . ' ' . $s->end_time);
                while ($cur->copy()->addMinutes($durationMin)->lte($end)) {
                    $slots[] = $cur->format('H:i:s');
                    $cur->addMinutes($durationMin);
                }
            }
            return $slots;
        };

        $dentists = collect([$dentJuan, $dentAna]);
        $appointments = collect();

        for ($d = 0; $d < 7; $d++) {
            $date = $today->copy()->addDays($d + 1); // desde mañana
            foreach ($dentists as $dentist) {
                $svc = $services->random();
                $slots = $makeSlots($dentist->id, $date, $svc->duration_min);
                shuffle($slots);
                $slots = array_slice($slots, 0, 3); // 3 citas/día por odontólogo

                foreach ($slots as $st) {
                    $pat = $patients->random();
                    $start = Carbon::parse("{$date->toDateString()} {$st}");
                    $end   = $start->copy()->addMinutes($svc->duration_min);

                    // Evitar choques
                    $exists = Appointment::where('dentist_id', $dentist->id)
                        ->whereDate('date', $date)
                        ->whereTime('start_time', $st)->exists();
                    if ($exists) continue;

                    $appointments->push(
                        Appointment::create([
                            'patient_id' => $pat->id,
                            'dentist_id' => $dentist->id,
                            'service_id' => $svc->id,
                            'chair_id' => $dentist->chair_id,
                            'date' => $date->toDateString(),
                            'start_time' => $st,
                            'end_time' => $end->format('H:i:s'),
                            'status' => $faker->randomElement(['reserved', 'confirmed']),
                            'notes' => $faker->boolean(30) ? 'Paciente prefiere anestesia tópica' : null,
                        ])
                    );
                }
            }
        }

        // ---------- Planes de tratamiento + tratamientos ----------
        foreach ($patients as $p) {
            if ($faker->boolean(70)) {
                $plan = TreatmentPlan::create([
                    'patient_id' => $p->id,
                    'title' => 'Plan inicial',
                    'estimate_total' => 0,
                    'status' => $faker->randomElement(['draft', 'approved', 'in_progress']),
                    'approved_at' => $faker->boolean(40) ? now() : null,
                    'approved_by' => $faker->boolean(40) ? $admin->id : null,
                ]);

                $itemsCount = $faker->numberBetween(1, 3);
                $sum = 0;
                for ($i = 0; $i < $itemsCount; $i++) {
                    $svc = $services->random();
                    $sum += $svc->price;
                    Treatment::create([
                        'treatment_plan_id' => $plan->id,
                        'service_id' => $svc->id,
                        'tooth_code' => $faker->boolean(60) ? (string)$faker->randomElement([11, 12, 13, 14, 15, 16, 21, 22, 23, 24, 25, 26, 31, 32, 33, 34, 35, 36, 41, 42, 43, 44, 45, 46]) : null,
                        'surface' => $faker->boolean(40) ? $faker->randomElement(['O', 'M', 'D', 'B', 'L', 'I']) : null,
                        'price' => $svc->price,
                        'status' => $faker->randomElement(['planned', 'in_progress']),
                        'appointment_id' => optional($appointments->random())->id,
                        'notes' => $faker->boolean(30) ? 'Requiere control en 7 días' : null,
                    ]);
                }
                $plan->update(['estimate_total' => $sum]);
            }
        }

        // ---------- Diagnósticos + Notas clínicas ----------
        foreach ($patients as $p) {
            if ($faker->boolean(60)) {
                Diagnosis::create([
                    'patient_id' => $p->id,
                    'code' => $faker->boolean(50) ? 'K02.1' : null,
                    'label' => 'Caries dental',
                    'tooth_code' => $faker->randomElement([16, 26, 36, 46, 11, 21, 31, 41]),
                    'surface' => $faker->randomElement(['O', 'M', 'D', 'B', 'L', 'I']),
                    'status' => $faker->randomElement(['active', 'resolved']),
                    'notes' => $faker->boolean(30) ? 'Lesión cavitada' : null,
                ]);
            }

            if ($faker->boolean(50)) {
                $appt = $appointments->random();
                ClinicalNote::create([
                    'patient_id' => $p->id,
                    'appointment_id' => $appt?->id,
                    'type' => 'SOAP',
                    'subjective' => 'Dolor intermitente zona molar sup. derecha',
                    'objective' => 'Percusión levemente positiva',
                    'assessment' => 'Caries profunda',
                    'plan' => 'Endodoncia + Obturación',
                    'vitals' => ['bp' => '120/80', 'temp' => '36.7'],
                    'author_id' => $uJuan->id,
                ]);
            }
        }

        // ---------- Odontogramas ----------
        foreach ($patients as $p) {
            if ($faker->boolean(65)) {
                $odo = Odontogram::create([
                    'patient_id' => $p->id,
                    'date' => $today->toDateString(),
                    'notes' => 'Odontograma inicial',
                    'created_by' => $uJuan->id,
                ]);

                foreach ([16, 26, 36, 46] as $tooth) {
                    $t = OdontogramTooth::create([
                        'odontogram_id' => $odo->id,
                        'tooth_code' => (string)$tooth,
                        'status' => $faker->randomElement(['sano', 'caries', 'obturado']),
                        'notes' => null,
                    ]);
                    OdontogramSurface::create([
                        'odontogram_tooth_id' => $t->id,
                        'surface' => 'O',
                        'condition' => $faker->randomElement(['caries', 'fisura', 'restauración']),
                        'notes' => null,
                    ]);
                }
            }
        }

        // ---------- Adjuntos ----------
        Storage::disk('public')->makeDirectory('attachments');
        foreach ($patients as $p) {
            if ($faker->boolean(50)) {
                $filename = 'attachments/sample_' . $p->id . '.txt';
                Storage::disk('public')->put($filename, "Archivo de prueba del paciente #{$p->id}");
                Attachment::create([
                    'patient_id' => $p->id,
                    'type' => 'txt',
                    'path' => $filename,
                    'original_name' => basename($filename),
                    'notes' => 'Documento generado por el seeder',
                ]);
            }
        }

        // ---------- Consentimientos (opcional) ----------
        // ... (dejado comentado como en tu versión original)

        // ---------- Semillas opcionales (solo si existen tablas) ----------
        if (Schema::hasTable('specialties')) {
            $specs = ['Odontología General', 'Endodoncia', 'Ortodoncia', 'Periodoncia'];
            foreach ($specs as $s) {
                \DB::table('specialties')->updateOrInsert(
                    ['name' => $s],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }

        if (Schema::hasTable('payment_methods')) {
            $methods = ['Efectivo', 'Tarjeta', 'Transferencia'];
            foreach ($methods as $m) {
                \DB::table('payment_methods')->updateOrInsert(
                    ['name' => $m],
                    ['active' => true, 'created_at' => now(), 'updated_at' => now()]
                );
            }
        }

        // =====================================================================
        //                       INVENTARIO / PRODUCTOS
        // =====================================================================

        // Solo sembrar si las tablas existen (por si corres el seeder en etapas)
        if (Schema::hasTable('products')
            && Schema::hasTable('product_categories')
            && Schema::hasTable('product_presentation_units')
            && Schema::hasTable('measurement_units')
            && Schema::hasTable('suppliers')
            && Schema::hasTable('locations')
        ) {
            // ------ Unidades de medida (concentración) ------
            $muMg = MeasurementUnit::updateOrCreate(
                ['symbol' => 'mg'],
                ['name' => 'Miligramo', 'is_active' => true]
            );
            $muG = MeasurementUnit::updateOrCreate(
                ['symbol' => 'g'],
                ['name' => 'Gramo', 'is_active' => true]
            );
            $muMl = MeasurementUnit::updateOrCreate(
                ['symbol' => 'ml'],
                ['name' => 'Mililitro', 'is_active' => true]
            );
            $muPercent = MeasurementUnit::updateOrCreate(
                ['symbol' => '%'],
                ['name' => 'Porcentaje', 'is_active' => true]
            );
            $muMgMl = MeasurementUnit::updateOrCreate(
                ['symbol' => 'mg/ml'],
                ['name' => 'Miligramo por mililitro', 'is_active' => true]
            );
            $muUI = MeasurementUnit::updateOrCreate(
                ['symbol' => 'UI'],
                ['name' => 'Unidad internacional', 'is_active' => true]
            );

            // ------ Unidades de presentación ------
            $presAmpolla = ProductPresentationUnit::updateOrCreate(
                ['name' => 'Ampolla'],
                ['short_name' => 'amp', 'is_active' => true]
            );
            $presTableta = ProductPresentationUnit::updateOrCreate(
                ['name' => 'Tableta'],
                ['short_name' => 'tab', 'is_active' => true]
            );
            $presCapsula = ProductPresentationUnit::updateOrCreate(
                ['name' => 'Cápsula'],
                ['short_name' => 'cap', 'is_active' => true]
            );
            $presFrasco = ProductPresentationUnit::updateOrCreate(
                ['name' => 'Frasco'],
                ['short_name' => 'frasco', 'is_active' => true]
            );
            $presCarpule = ProductPresentationUnit::updateOrCreate(
                ['name' => 'Carpule'],
                ['short_name' => 'carp', 'is_active' => true]
            );
            $presTubo = ProductPresentationUnit::updateOrCreate(
                ['name' => 'Tubo'],
                ['short_name' => 'tubo', 'is_active' => true]
            );
            $presCaja = ProductPresentationUnit::updateOrCreate(
                ['name' => 'Caja'],
                ['short_name' => 'caja', 'is_active' => true]
            );

            // ------ Categorías de productos (odontología) ------
            $catAnalgesico = ProductCategory::updateOrCreate(
                ['name' => 'Analgésico'],
                ['code' => 'ANALG', 'is_active' => true]
            );
            $catAntibiotico = ProductCategory::updateOrCreate(
                ['name' => 'Antibiótico'],
                ['code' => 'ATB', 'is_active' => true]
            );
            $catAnestesico = ProductCategory::updateOrCreate(
                ['name' => 'Anestésico local'],
                ['code' => 'ANEST', 'is_active' => true]
            );
            $catMaterial = ProductCategory::updateOrCreate(
                ['name' => 'Material odontológico'],
                ['code' => 'MAT_OD', 'is_active' => true]
            );
            $catDesinfectante = ProductCategory::updateOrCreate(
                ['name' => 'Desinfectante'],
                ['code' => 'DESINF', 'is_active' => true]
            );
            $catOtros = ProductCategory::updateOrCreate(
                ['name' => 'Otros'],
                ['code' => 'OTR', 'is_active' => true]
            );

            // ------ Proveedores / Laboratorios ------
            $supLabDemo = Supplier::updateOrCreate(
                ['name' => 'Laboratorio Dental Demo'],
                [
                    'contact' => 'Contacto Lab',
                    'phone' => '70000001',
                    'tax_id' => '12345601',
                ]
            );
            $supFarmacia = Supplier::updateOrCreate(
                ['name' => 'Farmacia Central Demo'],
                [
                    'contact' => 'Encargado Compras',
                    'phone' => '70000002',
                    'tax_id' => '12345602',
                ]
            );

            // ------ Ubicaciones (inventario) ------
            $locDeposito = Location::updateOrCreate(
                ['name' => 'Depósito principal'],
                ['is_active' => true]
            );
            $locConsultorio1 = Location::updateOrCreate(
                ['name' => 'Consultorio 1'],
                ['is_active' => true]
            );
            $locConsultorio2 = Location::updateOrCreate(
                ['name' => 'Consultorio 2'],
                ['is_active' => true]
            );

            // ------ Productos demo ------
            // 1) Anestésico local – Lidocaína 2% en carpules
            Product::updateOrCreate(
                ['sku' => 'MED-0001'],
                [
                    'barcode'             => '1000000000001',
                    'name'                => 'Lidocaína 2% carpule',
                    'product_category_id' => $catAnestesico->id,
                    'presentation_unit_id'=> $presCarpule->id,
                    'presentation_detail' => 'Caja x 50 carpules',
                    'concentration_value' => 2.000,
                    'concentration_unit_id' => $muPercent->id,
                    'unit'                => 'carpule',
                    'brand'               => 'LabDemo',
                    'supplier_id'         => $supLabDemo->id,
                    'stock'               => 100,
                    'min_stock'           => 20,
                    'is_active'           => true,
                ]
            );

            // 2) Analgésico – Ibuprofeno 400 mg tabletas
            Product::updateOrCreate(
                ['sku' => 'MED-0002'],
                [
                    'barcode'             => '1000000000002',
                    'name'                => 'Ibuprofeno 400 mg',
                    'product_category_id' => $catAnalgesico->id,
                    'presentation_unit_id'=> $presTableta->id,
                    'presentation_detail' => 'Caja x 20 tabletas',
                    'concentration_value' => 400.000,
                    'concentration_unit_id' => $muMg->id,
                    'unit'                => 'tableta',
                    'brand'               => 'DolorLess',
                    'supplier_id'         => $supFarmacia->id,
                    'stock'               => 200,
                    'min_stock'           => 40,
                    'is_active'           => true,
                ]
            );

            // 3) Antibiótico – Amoxicilina 500 mg cápsulas
            Product::updateOrCreate(
                ['sku' => 'MED-0003'],
                [
                    'barcode'             => '1000000000003',
                    'name'                => 'Amoxicilina 500 mg',
                    'product_category_id' => $catAntibiotico->id,
                    'presentation_unit_id'=> $presCapsula->id,
                    'presentation_detail' => 'Caja x 12 cápsulas',
                    'concentration_value' => 500.000,
                    'concentration_unit_id' => $muMg->id,
                    'unit'                => 'cápsula',
                    'brand'               => 'AntibioX',
                    'supplier_id'         => $supFarmacia->id,
                    'stock'               => 120,
                    'min_stock'           => 24,
                    'is_active'           => true,
                ]
            );

            // 4) Enjuague bucal Clorhexidina 0.12% – frasco
            Product::updateOrCreate(
                ['sku' => 'MED-0004'],
                [
                    'barcode'             => '1000000000004',
                    'name'                => 'Clorhexidina 0.12% enjuague bucal',
                    'product_category_id' => $catDesinfectante->id,
                    'presentation_unit_id'=> $presFrasco->id,
                    'presentation_detail' => 'Frasco 250 ml',
                    'concentration_value' => 0.12,
                    'concentration_unit_id' => $muPercent->id,
                    'unit'                => 'frasco',
                    'brand'               => 'ChxDent',
                    'supplier_id'         => $supLabDemo->id,
                    'stock'               => 50,
                    'min_stock'           => 10,
                    'is_active'           => true,
                ]
            );

            // 5) Material odontológico – Guantes de examen caja x 100
            Product::updateOrCreate(
                ['sku' => 'MED-0005'],
                [
                    'barcode'             => '1000000000005',
                    'name'                => 'Guantes de examen (talla M)',
                    'product_category_id' => $catMaterial->id,
                    'presentation_unit_id'=> $presCaja->id,
                    'presentation_detail' => 'Caja x 100 unidades',
                    'concentration_value' => null,
                    'concentration_unit_id' => null,
                    'unit'                => 'caja',
                    'brand'               => 'SafeHands',
                    'supplier_id'         => $supLabDemo->id,
                    'stock'               => 30,
                    'min_stock'           => 5,
                    'is_active'           => true,
                ]
            );
        }
    }
}
