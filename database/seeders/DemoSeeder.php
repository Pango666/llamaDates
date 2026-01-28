<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
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

// ROLES / PERMISOS
use App\Models\Role;
use App\Models\Permission;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create();
        $today = Carbon::today();

        $hasUserRoleCol = Schema::hasColumn('users', 'role');

        // ========================================================
        //                       USUARIOS
        // ========================================================
        $adminData = [
            'name'     => 'Admin',
            'password' => Hash::make('password'),
            'status'   => 'active',
        ];
        if ($hasUserRoleCol) {
            $adminData['role'] = 'admin';
        }
        $admin = User::updateOrCreate(
            ['email' => 'admin@demo.test'],
            $adminData
        );

        $asistData = [
            'name'     => 'Asistente',
            'password' => Hash::make('password'),
            'status'   => 'active',
        ];
        if ($hasUserRoleCol) {
            $asistData['role'] = 'asistente';
        }
        $asist = User::updateOrCreate(
            ['email' => 'asist@demo.test'],
            $asistData
        );

        $uJuanData = [
            'name'     => 'Dr. Juan Pérez',
            'password' => Hash::make('password'),
            'status'   => 'active',
        ];
        if ($hasUserRoleCol) {
            $uJuanData['role'] = 'odontologo';
        }
        $uJuan = User::updateOrCreate(
            ['email' => 'dr.juan@demo.test'],
            $uJuanData
        );

        $uAnaData = [
            'name'     => 'Dra. Ana Díaz',
            'password' => Hash::make('password'),
            'status'   => 'active',
        ];
        if ($hasUserRoleCol) {
            $uAnaData['role'] = 'odontologo';
        }
        $uAna = User::updateOrCreate(
            ['email' => 'dra.ana@demo.test'],
            $uAnaData
        );

        // Usuario Cajero de prueba
        $uCajeroData = [
            'name'     => 'María Cajera',
            'password' => Hash::make('password'),
            'status'   => 'active',
        ];
        if ($hasUserRoleCol) {
            $uCajeroData['role'] = 'cajero';
        }
        $uCajero = User::updateOrCreate(
            ['email' => 'cajero@demo.test'],
            $uCajeroData
        );

        // ========================================================
        //                 ROLES / PERMISOS (NUEVO)
        // ========================================================
        if (Schema::hasTable('roles') && Schema::hasTable('permissions')) {
            // ---- Roles base ----
            $roleAdmin = Role::updateOrCreate(
                ['name' => 'admin'],
                ['label' => 'Administrador']
            );
            $roleAsist = Role::updateOrCreate(
                ['name' => 'asistente'],
                ['label' => 'Asistente']
            );
            $roleOdonto = Role::updateOrCreate(
                ['name' => 'odontologo'],
                ['label' => 'Odontólogo']
            );
            $rolePaciente = Role::updateOrCreate(
                ['name' => 'paciente'],
                ['label' => 'Paciente']
            );
            $roleAlmacen = Role::updateOrCreate(
                ['name' => 'almacen'],
                ['label' => 'Almacén / Inventario']
            );
            $roleEnfermera = Role::updateOrCreate(
                ['name' => 'enfermera'],
                ['label' => 'Enfermera']
            );
            $roleCajero = Role::updateOrCreate(
                ['name' => 'cajero'],
                ['label' => 'Cajero']
            );

            // ---- Permisos (incluye macros + por módulo/ruta) ----
            $permissionsData = [

                // DASHBOARD
                ['name' => 'dashboard.view', 'label' => 'Ver dashboard'],

                // AGENDA / REPORTES / PAGOS (extras)
                ['name' => 'agenda.view',          'label' => 'Ver agenda'],
                ['name' => 'reports.view',         'label' => 'Ver reportes'],
                ['name' => 'payments.view_status', 'label' => 'Ver estado de pagos'],

                // ================== CITAS (ADMIN) ==================
                ['name' => 'appointments.manage',       'label' => 'Gestionar citas'],
                ['name' => 'appointments.index',        'label' => 'Ver listado de citas'],
                ['name' => 'appointments.create',       'label' => 'Crear cita'],
                ['name' => 'appointments.store',        'label' => 'Guardar cita'],
                ['name' => 'appointments.show',         'label' => 'Ver detalle de cita'],
                ['name' => 'appointments.update_status','label' => 'Cambiar estado de cita'],
                ['name' => 'appointments.cancel',       'label' => 'Cancelar cita'],
                ['name' => 'appointments.availability', 'label' => 'Consultar disponibilidad de citas'],
                ['name' => 'appointments.slot_chair',   'label' => 'Consultar silla disponible'],
                // suministros por cita (inventario pero ligado a cita)
                ['name' => 'appointments.supplies.store',   'label' => 'Registrar insumos por cita'],
                ['name' => 'appointments.supplies.destroy', 'label' => 'Eliminar insumo de cita'],

                // ================ PACIENTES (ADMIN) =================
                ['name' => 'patients.manage',         'label' => 'Gestionar pacientes'],
                ['name' => 'patients.index',          'label' => 'Ver listado de pacientes'],
                ['name' => 'patients.create',         'label' => 'Crear paciente'],
                ['name' => 'patients.store',          'label' => 'Guardar paciente'],
                ['name' => 'patients.show',           'label' => 'Ver detalle de paciente'],
                ['name' => 'patients.edit',           'label' => 'Editar paciente'],
                ['name' => 'patients.update',         'label' => 'Actualizar paciente'],
                ['name' => 'patients.destroy',        'label' => 'Eliminar paciente'],
                ['name' => 'patients.history.view',   'label' => 'Ver historia clínica completa'],
                ['name' => 'patients.history.update', 'label' => 'Actualizar historia clínica'],
                ['name' => 'patients.find_by_ci',     'label' => 'Buscar paciente por CI'],

                // ============ PAGOS / FACTURACIÓN (ADMIN) ==========
                ['name' => 'billing.manage',             'label' => 'Gestionar pagos y facturación'],
                ['name' => 'billing.index',              'label' => 'Ver lista de pagos'],
                ['name' => 'billing.create',             'label' => 'Crear pago'],
                ['name' => 'billing.store',              'label' => 'Guardar pago'],
                ['name' => 'billing.show',               'label' => 'Ver detalle de pago'],
                ['name' => 'billing.edit',               'label' => 'Editar pago'],
                ['name' => 'billing.update',             'label' => 'Actualizar pago'],
                ['name' => 'billing.issue',              'label' => 'Emitir factura/pago'],
                ['name' => 'billing.cancel',             'label' => 'Cancelar factura/pago'],
                ['name' => 'billing.payments.add',       'label' => 'Agregar abono a factura'],
                ['name' => 'billing.payments.delete',    'label' => 'Eliminar abono de factura'],
                ['name' => 'billing.delete',             'label' => 'Eliminar factura'],

                ['name' => 'invoices.show',              'label' => 'Ver factura'],
                ['name' => 'invoices.view',              'label' => 'Ver vista de factura'],
                ['name' => 'invoices.payments.store',    'label' => 'Registrar pago en factura'],
                ['name' => 'invoices.markPaid',          'label' => 'Marcar factura como pagada'],
                ['name' => 'invoices.download',          'label' => 'Descargar factura'],
                ['name' => 'invoices.regenerate',        'label' => 'Regenerar factura'],
                ['name' => 'invoices.from_appointment.create','label' => 'Crear factura desde cita'],
                ['name' => 'invoices.from_appointment.store', 'label' => 'Guardar factura desde cita'],

                // =================== SERVICIOS =====================
                ['name' => 'services.view',    'label' => 'Ver servicios (solo lectura)'],
                ['name' => 'services.index',   'label' => 'Ver listado de servicios'],
                ['name' => 'services.create',  'label' => 'Crear servicio'],
                ['name' => 'services.store',   'label' => 'Guardar servicio'],
                ['name' => 'services.edit',    'label' => 'Editar servicio'],
                ['name' => 'services.update',  'label' => 'Actualizar servicio'],
                ['name' => 'services.toggle',  'label' => 'Activar/desactivar servicio'],
                ['name' => 'services.destroy', 'label' => 'Eliminar servicio'],

                // =================== HORARIOS ======================
                ['name' => 'schedules.view',          'label' => 'Ver horarios (solo lectura)'],
                ['name' => 'schedules.index',         'label' => 'Ver listado de horarios'],
                ['name' => 'schedules.edit',           'label' => 'Editar horario'],
                ['name' => 'schedules.update',         'label' => 'Actualizar horario'],
                ['name' => 'schedules.chairs.options', 'label' => 'Ver sillas para horario'],

                // ================== SILLONES =======================
                ['name' => 'chairs.view',    'label' => 'Ver consultorios (solo lectura)'],
                ['name' => 'chairs.index',   'label' => 'Ver listado de consultorios'],
                ['name' => 'chairs.create',  'label' => 'Crear consultorio/sillón'],
                ['name' => 'chairs.store',   'label' => 'Guardar consultorio/sillón'],
                ['name' => 'chairs.edit',    'label' => 'Editar consultorio/sillón'],
                ['name' => 'chairs.update',  'label' => 'Actualizar consultorio/sillón'],
                ['name' => 'chairs.destroy', 'label' => 'Eliminar consultorio/sillón'],
                ['name' => 'chairs.usage',   'label' => 'Ver ocupación de consultorios'],

                // ================== USUARIOS / DENTISTAS ===========
                ['name' => 'users.manage',      'label' => 'Gestionar usuarios'],

                // usuarios panel admin
                ['name' => 'users.index',       'label' => 'Ver usuarios'],
                ['name' => 'users.create',      'label' => 'Crear usuario'],
                ['name' => 'users.store',       'label' => 'Guardar usuario'],
                ['name' => 'users.edit',        'label' => 'Editar usuario'],
                ['name' => 'users.update',      'label' => 'Actualizar usuario'],
                ['name' => 'users.destroy',     'label' => 'Eliminar usuario'],

                // odontólogos
                ['name' => 'dentists.index',    'label' => 'Ver odontólogos'],
                ['name' => 'dentists.create',   'label' => 'Crear odontólogo'],
                ['name' => 'dentists.store',    'label' => 'Guardar odontólogo'],
                ['name' => 'dentists.show',     'label' => 'Ver odontólogo'],
                ['name' => 'dentists.edit',     'label' => 'Editar odontólogo'],
                ['name' => 'dentists.update',   'label' => 'Actualizar odontólogo'],
                ['name' => 'dentists.destroy',  'label' => 'Eliminar odontólogo'],

                // ================= HISTORIA / CONSENTIMIENTOS ======
                ['name' => 'medical_history.manage',   'label' => 'Gestionar historias clínicas'],

                // plantillas de consentimiento
                ['name' => 'consent_templates.index',   'label' => 'Ver plantillas de consentimiento'],
                ['name' => 'consent_templates.create',  'label' => 'Crear plantilla de consentimiento'],
                ['name' => 'consent_templates.store',   'label' => 'Guardar plantilla de consentimiento'],
                ['name' => 'consent_templates.edit',    'label' => 'Editar plantilla de consentimiento'],
                ['name' => 'consent_templates.update',  'label' => 'Actualizar plantilla de consentimiento'],
                ['name' => 'consent_templates.destroy', 'label' => 'Eliminar plantilla de consentimiento'],

                // consentimientos por paciente
                ['name' => 'patient_consents.index',  'label' => 'Ver consentimientos del paciente'],
                ['name' => 'patient_consents.create', 'label' => 'Crear consentimiento del paciente'],
                ['name' => 'patient_consents.store',  'label' => 'Guardar consentimiento del paciente'],

                // operaciones sobre un consentimiento
                ['name' => 'consents.show',          'label' => 'Ver consentimiento'],
                ['name' => 'consents.edit',          'label' => 'Editar consentimiento'],
                ['name' => 'consents.update',        'label' => 'Actualizar consentimiento'],
                ['name' => 'consents.destroy',       'label' => 'Eliminar consentimiento'],
                ['name' => 'consents.print',         'label' => 'Imprimir consentimiento'],
                ['name' => 'consents.pdf',           'label' => 'Descargar PDF de consentimiento'],
                ['name' => 'consents.upload_signed', 'label' => 'Subir consentimiento firmado'],

                // =========== NOTAS CLÍNICAS / DIAGNÓSTICOS / ADJUNTOS
                ['name' => 'clinical_notes.manage',   'label' => 'Gestionar notas clínicas'],
                ['name' => 'clinical_notes.store',    'label' => 'Registrar nota clínica'],
                ['name' => 'clinical_notes.destroy',  'label' => 'Eliminar nota clínica'],

                ['name' => 'diagnoses.store',         'label' => 'Registrar diagnóstico'],
                ['name' => 'diagnoses.destroy',       'label' => 'Eliminar diagnóstico'],

                ['name' => 'attachments.store',       'label' => 'Subir adjunto clínico'],
                ['name' => 'attachments.destroy',     'label' => 'Eliminar adjunto clínico'],

                // ================= ODONTOGRAMAS ====================
                ['name' => 'odontograms.manage',        'label' => 'Gestionar odontogramas'],
                ['name' => 'odontograms.open',          'label' => 'Abrir odontograma de paciente'],
                ['name' => 'odontograms.show',          'label' => 'Ver odontograma'],
                ['name' => 'odontograms.teeth.upsert',  'label' => 'Registrar/editar piezas en odontograma'],

                // ============ PLANES DE TRATAMIENTO =================
                ['name' => 'treatment_plans.manage',     'label' => 'Gestionar planes de tratamiento'],

                ['name' => 'patient_plans.index',        'label' => 'Ver planes de paciente'],
                ['name' => 'patient_plans.create',       'label' => 'Crear plan para paciente'],
                ['name' => 'patient_plans.store',        'label' => 'Guardar plan para paciente'],

                ['name' => 'plans.show',                 'label' => 'Ver plan de tratamiento'],
                ['name' => 'plans.edit',                 'label' => 'Editar plan de tratamiento'],
                ['name' => 'plans.update',               'label' => 'Actualizar plan de tratamiento'],
                ['name' => 'plans.destroy',              'label' => 'Eliminar plan de tratamiento'],
                ['name' => 'plans.approve',              'label' => 'Aprobar plan de tratamiento'],
                ['name' => 'plans.start',                'label' => 'Iniciar plan de tratamiento'],
                ['name' => 'plans.recalc',               'label' => 'Recalcular plan de tratamiento'],
                ['name' => 'plans.print',                'label' => 'Imprimir plan de tratamiento'],
                ['name' => 'plans.pdf',                  'label' => 'Descargar PDF de plan'],

                ['name' => 'plans.treatments.store',     'label' => 'Agregar tratamiento a plan'],
                ['name' => 'plans.treatments.edit',      'label' => 'Editar tratamiento de plan'],
                ['name' => 'plans.treatments.update',    'label' => 'Actualizar tratamiento de plan'],
                ['name' => 'plans.treatments.destroy',   'label' => 'Eliminar tratamiento de plan'],

                ['name' => 'plans.invoice.create',       'label' => 'Crear factura desde plan'],
                ['name' => 'plans.invoice.store',        'label' => 'Guardar factura desde plan'],

                // ================= INVENTARIO ======================
                ['name' => 'inventory.manage',  'label' => 'Gestionar inventario'],

                // productos
                ['name' => 'inv.products.index',   'label' => 'Ver productos'],
                ['name' => 'inv.products.create',  'label' => 'Crear producto'],
                ['name' => 'inv.products.store',   'label' => 'Guardar producto'],
                ['name' => 'inv.products.edit',    'label' => 'Editar producto'],
                ['name' => 'inv.products.update',  'label' => 'Actualizar producto'],
                ['name' => 'inv.products.destroy', 'label' => 'Eliminar producto'],

                // categorías
                ['name' => 'inv.product_categories.index',   'label' => 'Ver categorías de producto'],
                ['name' => 'inv.product_categories.create',  'label' => 'Crear categoría de producto'],
                ['name' => 'inv.product_categories.store',   'label' => 'Guardar categoría de producto'],
                ['name' => 'inv.product_categories.edit',    'label' => 'Editar categoría de producto'],
                ['name' => 'inv.product_categories.update',  'label' => 'Actualizar categoría de producto'],
                ['name' => 'inv.product_categories.destroy', 'label' => 'Eliminar categoría de producto'],

                // proveedores
                ['name' => 'inv.suppliers.index',   'label' => 'Ver proveedores'],
                ['name' => 'inv.suppliers.create',  'label' => 'Crear proveedor'],
                ['name' => 'inv.suppliers.store',   'label' => 'Guardar proveedor'],
                ['name' => 'inv.suppliers.edit',    'label' => 'Editar proveedor'],
                ['name' => 'inv.suppliers.update',  'label' => 'Actualizar proveedor'],
                ['name' => 'inv.suppliers.destroy', 'label' => 'Eliminar proveedor'],

                // unidades de medida
                ['name' => 'inv.measurement_units.index',   'label' => 'Ver unidades de medida'],
                ['name' => 'inv.measurement_units.create',  'label' => 'Crear unidad de medida'],
                ['name' => 'inv.measurement_units.store',   'label' => 'Guardar unidad de medida'],
                ['name' => 'inv.measurement_units.edit',    'label' => 'Editar unidad de medida'],
                ['name' => 'inv.measurement_units.update',  'label' => 'Actualizar unidad de medida'],
                ['name' => 'inv.measurement_units.destroy', 'label' => 'Eliminar unidad de medida'],

                // unidades de presentación
                ['name' => 'inv.presentation_units.index',   'label' => 'Ver unidades de presentación'],
                ['name' => 'inv.presentation_units.create',  'label' => 'Crear unidad de presentación'],
                ['name' => 'inv.presentation_units.store',   'label' => 'Guardar unidad de presentación'],
                ['name' => 'inv.presentation_units.edit',    'label' => 'Editar unidad de presentación'],
                ['name' => 'inv.presentation_units.update',  'label' => 'Actualizar unidad de presentación'],
                ['name' => 'inv.presentation_units.destroy', 'label' => 'Eliminar unidad de presentación'],

                // movimientos
                ['name' => 'inv.movs.index',   'label' => 'Ver movimientos de inventario'],
                ['name' => 'inv.movs.create',  'label' => 'Crear movimiento de inventario'],
                ['name' => 'inv.movs.store',   'label' => 'Registrar movimiento de inventario'],

                // ================= ROLES / PERMISOS =================
                ['name' => 'roles.manage',          'label' => 'Gestionar roles'],
                ['name' => 'roles.index',           'label' => 'Ver roles'],
                ['name' => 'roles.create',          'label' => 'Crear rol'],
                ['name' => 'roles.store',           'label' => 'Guardar rol'],
                ['name' => 'roles.edit',            'label' => 'Editar rol'],
                ['name' => 'roles.update',          'label' => 'Actualizar rol'],
                ['name' => 'roles.destroy',         'label' => 'Eliminar rol'],
                ['name' => 'roles.perms.edit',      'label' => 'Editar permisos de rol'],
                ['name' => 'roles.perms.update',    'label' => 'Actualizar permisos de rol'],

                ['name' => 'permissions.manage',    'label' => 'Gestionar permisos'],
                ['name' => 'permissions.index',     'label' => 'Ver permisos'],
                ['name' => 'permissions.create',    'label' => 'Crear permiso'],
                ['name' => 'permissions.store',     'label' => 'Guardar permiso'],
                ['name' => 'permissions.edit',      'label' => 'Editar permiso'],
                ['name' => 'permissions.update',    'label' => 'Actualizar permiso'],
                ['name' => 'permissions.destroy',   'label' => 'Eliminar permiso'],

                // ================= APP PACIENTE =====================
                // acciones globales de paciente
                ['name' => 'appointments.request',      'label' => 'Solicitar/reprogramar cita'],
                ['name' => 'patient.dashboard.view',    'label' => 'Ver dashboard de paciente'],
                ['name' => 'patient.profile.view',      'label' => 'Ver perfil de paciente'],
                ['name' => 'patient.profile.update',    'label' => 'Actualizar perfil de paciente'],
                ['name' => 'patient.profile.password',  'label' => 'Cambiar contraseña de paciente'],
                ['name' => 'patient.odontogram.view',   'label' => 'Ver odontograma del paciente'],

                // citas del lado paciente
                ['name' => 'patient.appointments.index',       'label' => 'Ver citas (paciente)'],
                ['name' => 'patient.appointments.create',      'label' => 'Reservar cita (paciente)'],
                ['name' => 'patient.appointments.store',       'label' => 'Guardar cita (paciente)'],
                ['name' => 'patient.appointments.cancel',      'label' => 'Cancelar cita (paciente)'],
                ['name' => 'patient.appointments.availability','label' => 'Ver disponibilidad (paciente)'],
                ['name' => 'patient.appointments.slot_chair',  'label' => 'Ver silla disponible (paciente)'],

                // facturas del lado paciente
                ['name' => 'patient.invoices.index',    'label' => 'Ver facturas (paciente)'],
                ['name' => 'patient.invoices.show',     'label' => 'Ver detalle de factura (paciente)'],
            ];

            $permIdsByName = [];
            foreach ($permissionsData as $pd) {
                $p = Permission::updateOrCreate(
                    ['name' => $pd['name']],
                    ['label' => $pd['label']]
                );
                $permIdsByName[$p->name] = $p->id;
            }

            // ---- Asignar permisos a roles (por nombre) ----
            $allPermNames = array_keys($permIdsByName);

            $mapRolePermNames = [
                // ============================================================
                // ADMIN: Tiene TODO (configurar + operar)
                // ============================================================
                'admin' => $allPermNames,

                // ============================================================
                // ASISTENTE: Operar citas, pacientes, pagos operativos
                // VER config (consultorios, horarios, servicios) pero NO EDITAR
                // ============================================================
                'asistente' => [
                    'dashboard.view',
                    'agenda.view',

                    // --- CITAS: CRUD completo operativo ---
                    'appointments.index',
                    'appointments.create',
                    'appointments.store',
                    'appointments.show',
                    'appointments.update_status',  // confirmar, llegó, no-show, reagendar
                    'appointments.cancel',
                    'appointments.availability',
                    'appointments.slot_chair',

                    // --- PACIENTES: crear, ver (NO editar, NO eliminar, NO historia clínica) ---
                    'patients.index',
                    'patients.create',
                    'patients.store',
                    'patients.show',
                    'patients.find_by_ci',

                    // --- PAGOS: operativo (registrar, ver estado, NO arqueos/cierres) ---
                    'billing.index',
                    'billing.create',
                    'billing.store',
                    'billing.show',
                    'billing.payments.add',
                    'invoices.show',
                    'invoices.view',
                    'invoices.payments.store',
                    'invoices.download',
                    'invoices.from_appointment.create',
                    'invoices.from_appointment.store',
                    'payments.view_status',

                    // --- INVENTARIO: operativo (ver stock, registrar consumo) ---
                    'inv.products.index',
                    'inv.movs.index',
                    'inv.movs.create',
                    'inv.movs.store',

                    // --- VER CONFIG (sin editar) ---
                    'services.view',        // ver servicios y precios
                    'chairs.view',          // ver consultorios
                    'schedules.view',       // ver horarios
                ],

                // ============================================================
                // ODONTÓLOGO: Clínico total, agenda propia, ver pagos (lectura)
                // ============================================================
                'odontologo' => [
                    'dashboard.view',
                    'agenda.view',

                    // --- CITAS: ver su agenda, confirmar/atender ---
                    'appointments.index',
                    'appointments.show',
                    'appointments.update_status',

                    // --- PACIENTES: ver, historia clínica completa ---
                    'patients.index',
                    'patients.show',
                    'patients.find_by_ci',
                    'patients.history.view',
                    'patients.history.update',

                    // --- HISTORIA CLÍNICA: completo ---
                    'medical_history.manage',
                    'clinical_notes.manage',
                    'clinical_notes.store',
                    'clinical_notes.destroy',
                    'diagnoses.store',
                    'diagnoses.destroy',
                    'attachments.store',
                    'attachments.destroy',

                    // --- ODONTOGRAMA: completo ---
                    'odontograms.manage',
                    'odontograms.open',
                    'odontograms.show',
                    'odontograms.teeth.upsert',

                    // --- PLANES DE TRATAMIENTO: completo ---
                    'treatment_plans.manage',
                    'patient_plans.index',
                    'patient_plans.create',
                    'patient_plans.store',
                    'plans.show',
                    'plans.edit',
                    'plans.update',
                    'plans.approve',
                    'plans.start',
                    'plans.recalc',
                    'plans.print',
                    'plans.pdf',
                    'plans.treatments.store',
                    'plans.treatments.edit',
                    'plans.treatments.update',
                    'plans.treatments.destroy',

                    // --- CONSENTIMIENTOS ---
                    'consent_templates.index',
                    'patient_consents.index',
                    'patient_consents.create',
                    'patient_consents.store',
                    'consents.show',
                    'consents.print',
                    'consents.pdf',

                    // --- PAGOS: solo lectura ---
                    'payments.view_status',
                    'billing.show',
                    'invoices.show',
                ],

                // ============================================================
                // CAJERA: Caja total, cobros, arqueos, agendamiento presencial
                // VER config pero NO EDITAR
                // ============================================================
                'cajero' => [
                    'dashboard.view',
                    'agenda.view',
                    'payments.view_status',

                    // --- CITAS: crear para pacientes presenciales ---
                    'appointments.index',
                    'appointments.create',
                    'appointments.store',
                    'appointments.show',
                    'appointments.availability',
                    'appointments.slot_chair',

                    // --- PACIENTES: registrar datos mínimos ---
                    'patients.index',
                    'patients.create',
                    'patients.store',
                    'patients.show',
                    'patients.find_by_ci',

                    // --- COBROS Y CAJA: completo ---
                    'billing.manage',
                    'billing.index',
                    'billing.create',
                    'billing.store',
                    'billing.show',
                    'billing.edit',
                    'billing.update',
                    'billing.issue',
                    'billing.cancel',
                    'billing.payments.add',
                    'billing.payments.delete',
                    'invoices.show',
                    'invoices.view',
                    'invoices.payments.store',
                    'invoices.markPaid',
                    'invoices.download',
                    'invoices.regenerate',
                    'invoices.from_appointment.create',
                    'invoices.from_appointment.store',
                    'plans.invoice.create',
                    'plans.invoice.store',

                    // --- VER CONFIG (sin editar) ---
                    'services.view',
                    'chairs.view',
                    'schedules.view',
                ],

                // ============================================================
                // ENFERMERA: Apoyo clínico, ver citas/pacientes, notas, consentimientos
                // ============================================================
                'enfermera' => [
                    'dashboard.view',
                    'agenda.view',

                    // --- CITAS: ver ---
                    'appointments.index',
                    'appointments.show',

                    // --- PACIENTES: ver + historia ---
                    'patients.index',
                    'patients.show',
                    'patients.find_by_ci',
                    'patients.history.view',

                    // --- NOTAS CLÍNICAS: apoyo ---
                    'clinical_notes.store',
                    'attachments.store',

                    // --- CONSENTIMIENTOS ---
                    'patient_consents.index',
                    'patient_consents.create',
                    'patient_consents.store',
                    'consents.show',
                    'consents.print',
                    'consents.pdf',

                    // --- VER CONFIG ---
                    'services.view',
                ],

                // ============================================================
                // PACIENTE: Solo su portal
                // ============================================================
                'paciente' => [
                    'appointments.request',
                    'appointments.availability',
                    'payments.view_status',

                    'patient.dashboard.view',
                    'patient.profile.view',
                    'patient.profile.update',
                    'patient.profile.password',
                    'patient.odontogram.view',

                    'patient.appointments.index',
                    'patient.appointments.create',
                    'patient.appointments.store',
                    'patient.appointments.cancel',
                    'patient.appointments.availability',
                    'patient.appointments.slot_chair',

                    'patient.invoices.index',
                    'patient.invoices.show',
                ],

                // ============================================================
                // ALMACÉN: Inventario completo
                // ============================================================
                'almacen' => [
                    'dashboard.view',
                    'inventory.manage',

                    'inv.products.index',
                    'inv.products.create',
                    'inv.products.store',
                    'inv.products.edit',
                    'inv.products.update',
                    'inv.products.destroy',

                    'inv.product_categories.index',
                    'inv.product_categories.create',
                    'inv.product_categories.store',
                    'inv.product_categories.edit',
                    'inv.product_categories.update',
                    'inv.product_categories.destroy',

                    'inv.suppliers.index',
                    'inv.suppliers.create',
                    'inv.suppliers.store',
                    'inv.suppliers.edit',
                    'inv.suppliers.update',
                    'inv.suppliers.destroy',

                    'inv.measurement_units.index',
                    'inv.measurement_units.create',
                    'inv.measurement_units.store',
                    'inv.measurement_units.edit',
                    'inv.measurement_units.update',
                    'inv.measurement_units.destroy',

                    'inv.presentation_units.index',
                    'inv.presentation_units.create',
                    'inv.presentation_units.store',
                    'inv.presentation_units.edit',
                    'inv.presentation_units.update',
                    'inv.presentation_units.destroy',

                    'inv.movs.index',
                    'inv.movs.create',
                    'inv.movs.store',
                ],
            ];

            // convierto nombres -> ids y guardo en tablas pivot
            foreach ($mapRolePermNames as $roleName => $permNames) {
                $role = Role::where('name', $roleName)->first();
                if (!$role) {
                    continue;
                }

                $permIds = [];
                foreach ($permNames as $pname) {
                    $pid = $permIdsByName[$pname] ?? null;
                    if ($pid) {
                        $permIds[] = $pid;
                    }
                }
                $permIds = array_unique($permIds);

                // limpiamos y reinsertamos (es demo)
                \DB::table('permission_role')->where('role_id', $role->id)->delete();

                foreach ($permIds as $pid) {
                    \DB::table('permission_role')->updateOrInsert(
                        ['role_id' => $role->id, 'permission_id' => $pid],
                        ['created_at' => now(), 'updated_at' => now()]
                    );
                }
            }

            // ---- Asignar roles por defecto a usuarios demo ----
            $mapUserRoles = [
                $admin->id => ['admin'],
                $asist->id => ['asistente'],
                $uJuan->id => ['odontologo'],
                $uAna->id  => ['odontologo'],
                $uCajero->id => ['cajero'],
            ];

            foreach ($mapUserRoles as $userId => $roleNames) {
                foreach ($roleNames as $rn) {
                    $role = Role::where('name', $rn)->first();
                    if (!$role) {
                        continue;
                    }
                    \DB::table('role_user')->updateOrInsert(
                        ['user_id' => $userId, 'role_id' => $role->id],
                        ['created_at' => now(), 'updated_at' => now()]
                    );
                }
            }

            // Ejemplo: dar un permiso directo al asistente (además de su rol)
            $extraPerm = $permIdsByName['reports.view'] ?? null;
            if ($extraPerm) {
                \DB::table('permission_user')->updateOrInsert(
                    ['user_id' => $asist->id, 'permission_id' => $extraPerm],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }

        // ========================================================
        //                   SILLONES / ODONTÓLOGOS
        // ========================================================
        $chair1 = Chair::firstOrCreate(['name' => 'Sillón 1'], ['shift' => 'completo']);
        $chair2 = Chair::firstOrCreate(['name' => 'Sillón 2'], ['shift' => 'completo']);
        $chair3 = Chair::firstOrCreate(['name' => 'Sillón 3'], ['shift' => 'completo']);

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

        // ========================================================
        //                       SERVICIOS
        // ========================================================
        $servicesData = [
            ['name' => 'Consulta',            'duration_min' => 30, 'price' => 100, 'active' => true],
            ['name' => 'Limpieza',            'duration_min' => 45, 'price' => 250, 'active' => true],
            ['name' => 'Endodoncia',          'duration_min' => 60, 'price' => 600, 'active' => true],
            ['name' => 'Control Ortodoncia',  'duration_min' => 20, 'price' => 60,  'active' => true],
            ['name' => 'Blanqueamiento',      'duration_min' => 90, 'price' => 350, 'active' => true],
        ];
        $services = collect();
        foreach ($servicesData as $sd) {
            $services->push(Service::updateOrCreate(['name' => $sd['name']], $sd));
        }

        // ========================================================
        //                        HORARIOS
        // ========================================================
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

        // ========================================================
        //                      PACIENTES
        // ========================================================
        $patients = collect();
        $patients->push(Patient::updateOrCreate([
            'email' => 'maria@demo.test'
        ], [
            'first_name' => 'María',
            'last_name'  => 'Gómez',
            'phone'      => '70000000',
            'birthdate'  => Carbon::parse('1995-05-10'),
            'address'    => 'Av. Siempre Viva 123'
        ]));

        for ($i = 0; $i < 9; $i++) {
            $patients->push(Patient::create([
                'first_name' => $faker->firstName,
                'last_name'  => $faker->lastName,
                'email'      => $faker->unique()->safeEmail(),
                'phone'      => $faker->numerify('7#######'),
                'birthdate'  => $faker->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
                'address'    => $faker->address()
            ]));
        }

        // Vincular/crear usuarios para pacientes sin user_id
        $rolePacienteId = null;
        if (Schema::hasTable('roles')) {
            $rolePacienteId = Role::where('name', 'paciente')->value('id');
        }

        foreach ($patients as $p) {
            if (!$p->user_id) {
                $email = $p->email ?: "pac{$p->id}@demo.test";

                $userData = [
                    'name'     => trim(($p->first_name ?? '') . ' ' . ($p->last_name ?? '')) ?: "Paciente {$p->id}",
                    'password' => Hash::make('password'),
                    'status'   => 'active',
                ];
                if ($hasUserRoleCol) {
                    $userData['role'] = 'paciente';
                }

                $user = User::firstOrCreate(
                    ['email' => $email],
                    $userData
                );

                $p->user_id = $user->id;
                $p->save();

                // Rol paciente por defecto
                if ($rolePacienteId) {
                    \DB::table('role_user')->updateOrInsert(
                        ['user_id' => $user->id, 'role_id' => $rolePacienteId],
                        ['created_at' => now(), 'updated_at' => now()]
                    );
                }
            }
        }

        // ========================================================
        //               HISTORIA CLÍNICA POR PACIENTE
        // ========================================================
        foreach ($patients as $p) {
            MedicalHistory::firstOrCreate(
                ['patient_id' => $p->id],
                [
                    'smoker'            => $faker->boolean(20),
                    'pregnant'          => null,
                    'allergies'         => $faker->boolean(30) ? 'Penicilina' : null,
                    'medications'       => $faker->boolean(40) ? 'Ibuprofeno ocasional' : null,
                    'systemic_diseases' => $faker->boolean(20) ? 'Hipertensión controlada' : null,
                    'surgical_history'  => $faker->boolean(20) ? 'Apendicectomía 2010' : null,
                    'habits'            => $faker->boolean(50) ? 'Café diario' : null,
                    'extra'             => ['bp' => $faker->numberBetween(110, 140) . '/' . $faker->numberBetween(70, 90)],
                ]
            );
        }

        // ========================================================
        //                 CITAS PRÓXIMAS (7 DÍAS)
        // ========================================================
        $makeSlots = function (int $dentistId, Carbon $date, int $durationMin) {
            $scheds = Schedule::where('dentist_id', $dentistId)
                ->where('day_of_week', $date->dayOfWeek)
                ->get();
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

        $dentists     = collect([$dentJuan, $dentAna]);
        $appointments = collect();

        for ($d = 0; $d < 7; $d++) {
            $date = $today->copy()->addDays($d + 1); // desde mañana
            foreach ($dentists as $dentist) {
                $svc   = $services->random();
                $slots = $makeSlots($dentist->id, $date, $svc->duration_min);
                shuffle($slots);
                $slots = array_slice($slots, 0, 3); // 3 citas/día por odontólogo

                foreach ($slots as $st) {
                    $pat   = $patients->random();
                    $start = Carbon::parse("{$date->toDateString()} {$st}");
                    $end   = $start->copy()->addMinutes($svc->duration_min);

                    $exists = Appointment::where('dentist_id', $dentist->id)
                        ->whereDate('date', $date)
                        ->whereTime('start_time', $st)
                        ->exists();
                    if ($exists) {
                        continue;
                    }

                    $appointments->push(
                        Appointment::create([
                            'patient_id' => $pat->id,
                            'dentist_id' => $dentist->id,
                            'service_id' => $svc->id,
                            'chair_id'   => $dentist->chair_id,
                            'date'       => $date->toDateString(),
                            'start_time' => $st,
                            'end_time'   => $end->format('H:i:s'),
                            'status'     => $faker->randomElement(['reserved', 'confirmed']),
                            'notes'      => $faker->boolean(30) ? 'Paciente prefiere anestesia tópica' : null,
                        ])
                    );
                }
            }
        }

        // ========================================================
        //        PLANES DE TRATAMIENTO + TRATAMIENTOS
        // ========================================================
        foreach ($patients as $p) {
            if ($faker->boolean(70)) {
                $plan = TreatmentPlan::create([
                    'patient_id'     => $p->id,
                    'title'          => 'Plan inicial',
                    'estimate_total' => 0,
                    'status'         => $faker->randomElement(['draft', 'approved', 'in_progress']),
                    'approved_at'    => $faker->boolean(40) ? now() : null,
                    'approved_by'    => $faker->boolean(40) ? $admin->id : null,
                ]);

                $itemsCount = $faker->numberBetween(1, 3);
                $sum        = 0;
                for ($i = 0; $i < $itemsCount; $i++) {
                    $svc = $services->random();
                    $sum += $svc->price;
                    Treatment::create([
                        'treatment_plan_id' => $plan->id,
                        'service_id'        => $svc->id,
                        'tooth_code'        => $faker->boolean(60) ? (string)$faker->randomElement([
                            11, 12, 13, 14, 15, 16,
                            21, 22, 23, 24, 25, 26,
                            31, 32, 33, 34, 35, 36,
                            41, 42, 43, 44, 45, 46,
                        ]) : null,
                        'surface'       => $faker->boolean(40) ? $faker->randomElement(['O', 'M', 'D', 'B', 'L', 'I']) : null,
                        'price'         => $svc->price,
                        'status'        => $faker->randomElement(['planned', 'in_progress']),
                        'appointment_id'=> optional($appointments->random())->id,
                        'notes'         => $faker->boolean(30) ? 'Requiere control en 7 días' : null,
                    ]);
                }
                $plan->update(['estimate_total' => $sum]);
            }
        }

        // ========================================================
        //           DIAGNÓSTICOS + NOTAS CLÍNICAS
        // ========================================================
        foreach ($patients as $p) {
            if ($faker->boolean(60)) {
                Diagnosis::create([
                    'patient_id'  => $p->id,
                    'code'        => $faker->boolean(50) ? 'K02.1' : null,
                    'label'       => 'Caries dental',
                    'tooth_code'  => $faker->randomElement([16, 26, 36, 46, 11, 21, 31, 41]),
                    'surface'     => $faker->randomElement(['O', 'M', 'D', 'B', 'L', 'I']),
                    'status'      => $faker->randomElement(['active', 'resolved']),
                    'notes'       => $faker->boolean(30) ? 'Lesión cavitada' : null,
                ]);
            }

            if ($faker->boolean(50)) {
                $appt = $appointments->random();
                ClinicalNote::create([
                    'patient_id'     => $p->id,
                    'appointment_id' => $appt?->id,
                    'type'           => 'SOAP',
                    'subjective'     => 'Dolor intermitente zona molar sup. derecha',
                    'objective'      => 'Percusión levemente positiva',
                    'assessment'     => 'Caries profunda',
                    'plan'           => 'Endodoncia + Obturación',
                    'vitals'         => ['bp' => '120/80', 'temp' => '36.7'],
                    'author_id'      => $uJuan->id,
                ]);
            }
        }

        // ========================================================
        //                     ODONTOGRAMAS
        // ========================================================
        foreach ($patients as $p) {
            if ($faker->boolean(65)) {
                $odo = Odontogram::create([
                    'patient_id' => $p->id,
                    'date'       => $today->toDateString(),
                    'notes'      => 'Odontograma inicial',
                    'created_by' => $uJuan->id,
                ]);

                foreach ([16, 26, 36, 46] as $tooth) {
                    $t = OdontogramTooth::create([
                        'odontogram_id' => $odo->id,
                        'tooth_code'    => (string)$tooth,
                        'status'        => $faker->randomElement(['sano', 'caries', 'obturado']),
                        'notes'         => null,
                    ]);
                    OdontogramSurface::create([
                        'odontogram_tooth_id' => $t->id,
                        'surface'             => 'O',
                        'condition'           => $faker->randomElement(['caries', 'fisura', 'restauración']),
                        'notes'               => null,
                    ]);
                }
            }
        }

        // ========================================================
        //                       ADJUNTOS
        // ========================================================
        Storage::disk('public')->makeDirectory('attachments');
        foreach ($patients as $p) {
            if ($faker->boolean(50)) {
                $filename = 'attachments/sample_' . $p->id . '.txt';
                Storage::disk('public')->put($filename, "Archivo de prueba del paciente #{$p->id}");
                Attachment::create([
                    'patient_id'    => $p->id,
                    'type'          => 'txt',
                    'path'          => $filename,
                    'original_name' => basename($filename),
                    'notes'         => 'Documento generado por el seeder',
                ]);
            }
        }

        // ========================================================
        //              SEMILLAS OPCIONALES ADICIONALES
        // ========================================================
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
        if (Schema::hasTable('products')
            && Schema::hasTable('product_categories')
            && Schema::hasTable('product_presentation_units')
            && Schema::hasTable('measurement_units')
            && Schema::hasTable('suppliers')
            && Schema::hasTable('locations')
        ) {
            // ------ Unidades de medida ------
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

            // ------ Categorías ------
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

            // ------ Proveedores ------
            $supLabDemo = Supplier::updateOrCreate(
                ['name' => 'Laboratorio Dental Demo'],
                [
                    'contact' => 'Contacto Lab',
                    'phone'   => '70000001',
                    'tax_id'  => '12345601',
                ]
            );
            $supFarmacia = Supplier::updateOrCreate(
                ['name' => 'Farmacia Central Demo'],
                [
                    'contact' => 'Encargado Compras',
                    'phone'   => '70000002',
                    'tax_id'  => '12345602',
                ]
            );

            // ------ Ubicaciones ------
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
            Product::updateOrCreate(
                ['sku' => 'MED-0001'],
                [
                    'barcode'               => '1000000000001',
                    'name'                  => 'Lidocaína 2% carpule',
                    'product_category_id'   => $catAnestesico->id,
                    'presentation_unit_id'  => $presCarpule->id,
                    'presentation_detail'   => 'Caja x 50 carpules',
                    'concentration_value'   => 2.000,
                    'concentration_unit_id' => $muPercent->id,
                    'unit'                  => 'carpule',
                    'brand'                 => 'LabDemo',
                    'supplier_id'           => $supLabDemo->id,
                    'stock'                 => 100,
                    'min_stock'             => 20,
                    'is_active'             => true,
                ]
            );

            Product::updateOrCreate(
                ['sku' => 'MED-0002'],
                [
                    'barcode'               => '1000000000002',
                    'name'                  => 'Ibuprofeno 400 mg',
                    'product_category_id'   => $catAnalgesico->id,
                    'presentation_unit_id'  => $presTableta->id,
                    'presentation_detail'   => 'Caja x 20 tabletas',
                    'concentration_value'   => 400.000,
                    'concentration_unit_id' => $muMg->id,
                    'unit'                  => 'tableta',
                    'brand'                 => 'DolorLess',
                    'supplier_id'           => $supFarmacia->id,
                    'stock'                 => 200,
                    'min_stock'             => 40,
                    'is_active'             => true,
                ]
            );

            Product::updateOrCreate(
                ['sku' => 'MED-0003'],
                [
                    'barcode'               => '1000000000003',
                    'name'                  => 'Amoxicilina 500 mg',
                    'product_category_id'   => $catAntibiotico->id,
                    'presentation_unit_id'  => $presCapsula->id,
                    'presentation_detail'   => 'Caja x 12 cápsulas',
                    'concentration_value'   => 500.000,
                    'concentration_unit_id' => $muMg->id,
                    'unit'                  => 'cápsula',
                    'brand'                 => 'AntibioX',
                    'supplier_id'           => $supFarmacia->id,
                    'stock'                 => 120,
                    'min_stock'             => 24,
                    'is_active'             => true,
                ]
            );

            Product::updateOrCreate(
                ['sku' => 'MED-0004'],
                [
                    'barcode'               => '1000000000004',
                    'name'                  => 'Clorhexidina 0.12% enjuague bucal',
                    'product_category_id'   => $catDesinfectante->id,
                    'presentation_unit_id'  => $presFrasco->id,
                    'presentation_detail'   => 'Frasco 250 ml',
                    'concentration_value'   => 0.12,
                    'concentration_unit_id' => $muPercent->id,
                    'unit'                  => 'frasco',
                    'brand'                 => 'ChxDent',
                    'supplier_id'           => $supLabDemo->id,
                    'stock'                 => 50,
                    'min_stock'             => 10,
                    'is_active'             => true,
                ]
            );

            Product::updateOrCreate(
                ['sku' => 'MED-0005'],
                [
                    'barcode'               => '1000000000005',
                    'name'                  => 'Guantes de examen (talla M)',
                    'product_category_id'   => $catMaterial->id,
                    'presentation_unit_id'  => $presCaja->id,
                    'presentation_detail'   => 'Caja x 100 unidades',
                    'concentration_value'   => null,
                    'concentration_unit_id' => null,
                    'unit'                  => 'caja',
                    'brand'                 => 'SafeHands',
                    'supplier_id'           => $supLabDemo->id,
                    'stock'                 => 30,
                    'min_stock'             => 5,
                    'is_active'             => true,
                ]
            );
        }
    }
}
