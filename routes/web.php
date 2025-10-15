<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\ChairController;
use App\Http\Controllers\ClinicalNoteController;
use App\Http\Controllers\ConsentController;
use App\Http\Controllers\ConsentTemplateController;
use App\Http\Controllers\DentistController;
use App\Http\Controllers\DiagnosisController;
use App\Http\Controllers\MedicalHistoryController;
use App\Http\Controllers\OdontogramController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\TreatmentController;
use App\Http\Controllers\TreatmentPlanController;
use App\Models\MedicalHistory;

// INVENTARIO (nuevos)
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\InventoryMovementController;
use App\Http\Controllers\AppointmentSupplyController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Invitados (login / reset)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.post')
        ->middleware('throttle:6,1');

    Route::get('/password/forgot',        [AuthController::class, 'showForgot'])->name('password.request');
    Route::post('/password/email',        [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/password/reset/{token}', [AuthController::class, 'showReset'])->name('password.reset');
    Route::post('/password/reset',        [AuthController::class, 'resetPassword'])->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Autenticados (logout + redirecciones base)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Home => dashboard admin (por ahora unificamos todo en el panel de admin)
    Route::get('/', fn() => redirect()->route('admin.dashboard'))->name('home');
    Route::get('/dashboard', fn() => redirect()->route('admin.dashboard'))->name('dashboard');
});

/*
|--------------------------------------------------------------------------
| ADMINISTRADOR
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->group(function () {
    // Dashboard (usa AppointmentController)
    Route::get('/admin', [AppointmentController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/dashboard/data', [AppointmentController::class, 'dashboardData'])->name('admin.dashboard.data');

    // MODULO DE CITAS Y RESERVAS
    Route::get('/admin/citas', [AppointmentController::class, 'adminIndex'])->name('admin.appointments.index');
    Route::get('/admin/citas/nueva', [AppointmentController::class, 'createForm'])->name('admin.appointments.create');
    Route::post('/admin/citas', [AppointmentController::class, 'store'])->name('admin.appointments.store');
    Route::get('/admin/citas/disponibilidad', [AppointmentController::class, 'availability'])->name('admin.appointments.availability');
    Route::get('/admin/citas/slot-chair', [AppointmentController::class, 'slotChair'])->name('admin.appointments.slotChair');
    Route::get('/admin/citas/{appointment}', [AppointmentController::class, 'show'])->name('admin.appointments.show');
    Route::post('/admin/citas/{appointment}/estado', [AppointmentController::class, 'updateStatus'])->name('admin.appointments.status');
    Route::post('/admin/citas/{appointment}/cancelar', [AppointmentController::class, 'cancel'])->name('admin.appointments.cancel');

    // MODULO DE PACIENTES
    Route::get('/admin/pacientes',                 [PatientController::class, 'index'])->name('admin.patients.index');
    Route::get('/admin/pacientes/nuevo',           [PatientController::class, 'create'])->name('admin.patients.create');
    Route::post('/admin/pacientes',                [PatientController::class, 'store'])->name('admin.patients.store');
    Route::get('/admin/pacientes/{patient}',       [PatientController::class, 'show'])->name('admin.patients.show');
    Route::get('/admin/pacientes/{patient}/editar', [PatientController::class, 'edit'])->name('admin.patients.edit');
    Route::put('/admin/pacientes/{patient}',       [PatientController::class, 'update'])->name('admin.patients.update');
    Route::delete('/admin/pacientes/{patient}',    [PatientController::class, 'destroy'])->name('admin.patients.destroy');

    // MODULO DE ODONTOLOGOS
    Route::get('/admin/odontologos',                    [DentistController::class, 'index'])->name('admin.dentists');
    Route::get('/admin/odontologos/nuevo',              [DentistController::class, 'create'])->name('admin.dentists.create');
    Route::post('/admin/odontologos',                   [DentistController::class, 'store'])->name('admin.dentists.store');
    Route::get('/admin/odontologos/{dentist}',          [DentistController::class, 'show'])->name('admin.dentists.show');
    Route::get('/admin/odontologos/{dentist}/editar',   [DentistController::class, 'edit'])->name('admin.dentists.edit');
    Route::put('/admin/odontologos/{dentist}',          [DentistController::class, 'update'])->name('admin.dentists.update');
    Route::delete('/admin/odontologos/{dentist}',       [DentistController::class, 'destroy'])->name('admin.dentists.destroy');

    // MODULO DE SERVICIOS
    Route::get('/admin/servicios',                    [ServiceController::class, 'index'])->name('admin.services');
    Route::get('/admin/servicios/nuevo',              [ServiceController::class, 'create'])->name('admin.services.create');
    Route::post('/admin/servicios',                   [ServiceController::class, 'store'])->name('admin.services.store');
    Route::get('/admin/servicios/{service}/editar',   [ServiceController::class, 'edit'])->name('admin.services.edit');
    Route::put('/admin/servicios/{service}',          [ServiceController::class, 'update'])->name('admin.services.update');
    Route::post('/admin/servicios/{service}/toggle',  [ServiceController::class, 'toggle'])->name('admin.services.toggle');
    Route::delete('/admin/servicios/{service}',       [ServiceController::class, 'destroy'])->name('admin.services.destroy');

    // MODULO DE HORARIOS
    Route::get('/admin/horarios',                   [ScheduleController::class, 'index'])->name('admin.schedules');
    Route::get('/admin/horarios/{dentist}',         [ScheduleController::class, 'edit'])->name('admin.schedules.edit');
    Route::post('/admin/horarios/{dentist}',        [ScheduleController::class, 'update'])->name('admin.schedules.update');

    // MODULO DE PAGOS Y FACTURACION
    Route::get('/admin/pagos',                            [BillingController::class, 'index'])->name('admin.billing');
    Route::get('/admin/pagos/nueva',                      [BillingController::class, 'create'])->name('admin.billing.create');
    Route::post('/admin/pagos',                           [BillingController::class, 'store'])->name('admin.billing.store');
    Route::get('/admin/pagos/{invoice}',                  [BillingController::class, 'show'])->name('admin.billing.show');
    Route::get('/admin/pagos/{invoice}/editar',           [BillingController::class, 'edit'])->name('admin.billing.edit');
    Route::put('/admin/pagos/{invoice}',                  [BillingController::class, 'update'])->name('admin.billing.update');
    Route::post('/admin/pagos/{invoice}/emitir',          [BillingController::class, 'issue'])->name('admin.billing.issue');
    Route::post('/admin/pagos/{invoice}/cancelar',        [BillingController::class, 'cancel'])->name('admin.billing.cancel');
    Route::post('/admin/pagos/{invoice}/pagos',           [BillingController::class, 'addPayment'])->name('admin.billing.payments.add');
    Route::delete('/admin/pagos/{invoice}/pagos/{payment}', [BillingController::class, 'deletePayment'])->name('admin.billing.payments.delete');
    Route::delete('/admin/pagos/{invoice}',               [BillingController::class, 'destroy'])->name('admin.billing.delete');

    // MODULO DE HISTORIAS CLINICAS
    Route::get('/admin/pacientes/{patient}/historia-completa', [MedicalHistoryController::class, 'show'])->name('admin.patients.record');
    Route::put('/admin/pacientes/{patient}/historia',          [MedicalHistoryController::class, 'update'])->name('admin.patients.history.update');

    // MODULO DE ODONTOGRAMAS
    Route::get('/admin/patients/{patient}/odontograms',  [OdontogramController::class, 'open'])->name('admin.odontograms.open');
    Route::get('/admin/odontograms/{odontogram}',        [OdontogramController::class, 'show'])->name('admin.odontograms.show');
    Route::post('/admin/odontograms/{odontogram}/teeth', [OdontogramController::class, 'upsertTeeth'])->name('admin.odontograms.teeth.upsert');

    Route::get('patients/{patient}/plans',            [TreatmentPlanController::class, 'index'])->name('admin.patients.plans.index');
    Route::get('patients/{patient}/plans/create',     [TreatmentPlanController::class, 'create'])->name('admin.patients.plans.create');
    Route::post('patients/{patient}/plans',           [TreatmentPlanController::class, 'store'])->name('admin.patients.plans.store');

    Route::get('patients/{patient}/plans',        [TreatmentPlanController::class, 'index'])->name('admin.patients.plans.index');
    Route::get('patients/{patient}/plans/create', [TreatmentPlanController::class, 'create'])->name('admin.patients.plans.create');
    Route::post('patients/{patient}/plans',       [TreatmentPlanController::class, 'store'])->name('admin.patients.plans.store');

    // Plan
    Route::get('plans/{plan}',        [TreatmentPlanController::class, 'show'])->name('admin.plans.show');
    Route::get('plans/{plan}/edit',   [TreatmentPlanController::class, 'edit'])->name('admin.plans.edit');
    Route::put('plans/{plan}',        [TreatmentPlanController::class, 'update'])->name('admin.plans.update');
    Route::delete('plans/{plan}',     [TreatmentPlanController::class, 'destroy'])->name('admin.plans.destroy');
    Route::post('plans/{plan}/approve', [TreatmentPlanController::class, 'approve'])->name('admin.plans.approve');
    Route::post('plans/{plan}/start',  [TreatmentPlanController::class, 'start'])->name('admin.plans.start');
    Route::post('plans/{plan}/recalc', [TreatmentPlanController::class, 'recalc'])->name('admin.plans.recalc');

    // Impresión/PDF del plan
    Route::get('plans/{plan}/print', [TreatmentPlanController::class, 'print'])->name('admin.plans.print');
    Route::get('plans/{plan}/pdf',   [TreatmentPlanController::class, 'pdf'])->name('admin.plans.pdf');

    // Ítems del plan
    Route::post('plans/{plan}/treatments',  [TreatmentController::class, 'store'])->name('admin.plans.treatments.store');
    Route::get('plans/{plan}/treatments/{treatment}/edit', [TreatmentController::class, 'edit'])->name('admin.plans.treatments.edit');
    Route::put('treatments/{treatment}',    [TreatmentController::class, 'update'])->name('admin.plans.treatments.update');
    Route::delete('treatments/{treatment}', [TreatmentController::class, 'destroy'])->name('admin.plans.treatments.destroy');

    // Agendar cita desde un tratamiento
    Route::get('treatments/{treatment}/schedule', [TreatmentController::class, 'schedule'])->name('admin.treatments.schedule');

    // Facturar plan
    Route::get('plans/{plan}/invoice',  [BillingController::class, 'createFromPlan'])->name('admin.plans.invoice.create');
    Route::post('plans/{plan}/invoice', [BillingController::class, 'storeFromPlan'])->name('admin.plans.invoice.store');

    // Atajos/descargas factura (alias que ya usas)
    Route::get('/admin/invoices/{invoice}',               [BillingController::class, 'show'])->name('admin.invoices.show');
    Route::get('/admin/invoices/{invoice}/view',          [BillingController::class, 'view'])->name('admin.invoices.view');
    Route::post('/admin/invoices/{invoice}/payments',     [BillingController::class, 'storePayment'])->name('admin.invoices.payments.store');
    Route::post('/admin/invoices/{invoice}/mark-paid',    [BillingController::class, 'markPaid'])->name('admin.invoices.markPaid');
    Route::get('/admin/invoices/{invoice}/download',      [BillingController::class, 'download'])->name('admin.invoices.download');
    Route::post('/admin/invoices/{invoice}/regenerate',   [BillingController::class, 'regenerate'])->name('admin.invoices.regenerate');

    Route::get('invoices/from-appointment/{appointment}',  [BillingController::class, 'createFromAppointment'])->name('admin.invoices.createFromAppointment');
    Route::post('invoices/from-appointment/{appointment}', [BillingController::class, 'storeFromAppointment'])->name('admin.invoices.storeFromAppointment');

    // MODULO DE CONSENTIMIENTOS
    // Plantillas
    Route::get('/admin/consents/templates',                 [ConsentTemplateController::class, 'index'])->name('admin.consents.templates');
    Route::get('/admin/consents/templates/create',          [ConsentTemplateController::class, 'create'])->name('admin.consents.templates.create');
    Route::post('/admin/consents/templates',                [ConsentTemplateController::class, 'store'])->name('admin.consents.templates.store');
    Route::get('/admin/consents/templates/{template}/edit', [ConsentTemplateController::class, 'edit'])->name('admin.consents.templates.edit');
    Route::put('/admin/consents/templates/{template}',      [ConsentTemplateController::class, 'update'])->name('admin.consents.templates.update');
    Route::delete('/admin/consents/templates/{template}',   [ConsentTemplateController::class, 'destroy'])->name('admin.consents.templates.destroy');

    // Consentimientos por paciente
    Route::prefix('/admin/patients/{patient}')->group(function () {
        Route::get('consents',         [ConsentController::class, 'index'])->name('admin.patients.consents.index');
        Route::get('consents/create',  [ConsentController::class, 'create'])->name('admin.patients.consents.create');
        Route::post('consents',        [ConsentController::class, 'store'])->name('admin.patients.consents.store');
    });

    // Operaciones de un consentimiento
    Route::get('/admin/consents/{consent}',        [ConsentController::class, 'show'])->name('admin.consents.show');
    Route::get('/admin/consents/{consent}/edit',   [ConsentController::class, 'edit'])->name('admin.consents.edit');
    Route::put('/admin/consents/{consent}',        [ConsentController::class, 'update'])->name('admin.consents.update');
    Route::delete('/admin/consents/{consent}',     [ConsentController::class, 'destroy'])->name('admin.consents.destroy');

    // PDF / imprimir
    Route::get('/admin/consents/{consent}/print',  [ConsentController::class, 'print'])->name('admin.consents.print');
    Route::get('/admin/consents/{consent}/pdf',    [ConsentController::class, 'pdf'])->name('admin.consents.pdf');

    // Subir escaneo firmado
    Route::post('consents/{consent}/upload-signed',  [ConsentController::class, 'uploadSigned'])->name('admin.consents.uploadSigned');

    // MODULO DE NOTAS CLINICAS
    Route::post('appointments/{appointment}/notes', [ClinicalNoteController::class, 'store'])->name('admin.appointments.notes.store');
    Route::delete('notes/{note}', [ClinicalNoteController::class, 'destroy'])->name('admin.notes.destroy');

    // MODULO DE DIAGNOSTICOS
    Route::post('appointments/{appointment}/diagnoses', [DiagnosisController::class, 'store'])->name('admin.appointments.diagnoses.store');
    Route::delete('diagnoses/{diagnosis}', [DiagnosisController::class, 'destroy'])->name('admin.diagnoses.destroy');

    // MODULO DE ADJUNTOS
    Route::post('appointments/{appointment}/attachments', [AttachmentController::class, 'store'])->name('admin.appointments.attachments.store');
    Route::delete('attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('admin.attachments.destroy');

    // MODULO DE SILLAS
    Route::get('/admin/sillas',            [ChairController::class, 'index'])->name('admin.chairs.index');
    Route::get('/admin/sillas/crear',      [ChairController::class, 'create'])->name('admin.chairs.create');
    Route::post('/admin/sillas',           [ChairController::class, 'store'])->name('admin.chairs.store');
    Route::get('/admin/sillas/{chair}/editar', [ChairController::class, 'edit'])->name('admin.chairs.edit');
    Route::put('/admin/sillas/{chair}',    [ChairController::class, 'update'])->name('admin.chairs.update');
    Route::delete('/admin/sillas/{chair}', [ChairController::class, 'destroy'])->name('admin.chairs.destroy');
    Route::get('/admin/sillas/ocupacion', [ChairController::class, 'usageByWeekday'])->name('admin.chairs.usage');

    // ajax
    Route::get('/admin/horarios/{dentist}/chairs/options', [ScheduleController::class, 'chairOptions'])->name('admin.schedules.chairs.options');

    /* ========================= * USUARIOS / ROLES / PERMISOS * ========================= */
    Route::get('/users', [UserController::class, 'index'])->name('admin.users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('admin.users.create');
    Route::post('/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
    Route::get('/roles', [RoleController::class, 'index'])->name('admin.roles.index');
    Route::get('/roles/create', [RoleController::class, 'create'])->name('admin.roles.create');
    Route::post('/roles', [RoleController::class, 'store'])->name('admin.roles.store');
    Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('admin.roles.edit');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->name('admin.roles.update');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('admin.roles.destroy');
    // Permisos de un rol 
    Route::get('/roles/{role}/perms', [RoleController::class, 'editPerms'])->name('admin.roles.perms');
    Route::post('/roles/{role}/perms', [RoleController::class, 'updatePerms'])->name('admin.roles.perms.update');
    Route::get('/permissions', [PermissionController::class, 'index'])->name('admin.permissions.index');
    Route::get('/permissions/create', [PermissionController::class, 'create'])->name('admin.permissions.create');
    Route::post('/permissions', [PermissionController::class, 'store'])->name('admin.permissions.store');
    Route::get('/permissions/{permission}/edit', [PermissionController::class, 'edit'])->name('admin.permissions.edit');
    Route::put('/permissions/{permission}', [PermissionController::class, 'update'])->name('admin.permissions.update');
    Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy'])->name('admin.permissions.destroy');
    // Alias de compatibilidad legacy 
    Route::get('/appointments', fn() => redirect()->route('admin.appointments.index'))->name('appointments.legacy');

    /* =========================
     *  INVENTARIO (NUEVO) — nombres admin.inv.*
     * ========================= */
    // Productos
    Route::get('/admin/inv/productos',                   [ProductController::class, 'index'])->name('admin.inv.products.index');
    Route::get('/admin/inv/productos/crear',             [ProductController::class, 'create'])->name('admin.inv.products.create');
    Route::post('/admin/inv/productos',                  [ProductController::class, 'store'])->name('admin.inv.products.store');
    Route::get('/admin/inv/productos/{product}/editar',  [ProductController::class, 'edit'])->name('admin.inv.products.edit');
    Route::put('/admin/inv/productos/{product}',         [ProductController::class, 'update'])->name('admin.inv.products.update');
    Route::delete('/admin/inv/productos/{product}',      [ProductController::class, 'destroy'])->name('admin.inv.products.destroy');

    // Proveedores
    Route::resource('/admin/inv/proveedores', SupplierController::class)->except(['show'])->names([
        'index'   => 'admin.inv.suppliers.index',
        'create'  => 'admin.inv.suppliers.create',
        'store'   => 'admin.inv.suppliers.store',
        'edit'    => 'admin.inv.suppliers.edit',
        'update'  => 'admin.inv.suppliers.update',
        'destroy' => 'admin.inv.suppliers.destroy',
    ]);

    // Ubicaciones
    Route::resource('/admin/inv/ubicaciones', LocationController::class)->except(['show'])->names([
        'index'   => 'admin.inv.locations.index',
        'create'  => 'admin.inv.locations.create',
        'store'   => 'admin.inv.locations.store',
        'edit'    => 'admin.inv.locations.edit',
        'update'  => 'admin.inv.locations.update',
        'destroy' => 'admin.inv.locations.destroy',
    ]);

    // Movimientos (Kardex)
    Route::get('/admin/inv/movimientos',            [InventoryMovementController::class, 'index'])->name('admin.inv.movs.index');
    Route::get('/admin/inv/movimientos/nuevo',      [InventoryMovementController::class, 'create'])->name('admin.inv.movs.create');
    Route::post('/admin/inv/movimientos',           [InventoryMovementController::class, 'store'])->name('admin.inv.movs.store');

    // Suministros por cita (descarga de stock + item en factura si se incluye precio)
    Route::post('/appointments/{appointment}/supplies', [AppointmentSupplyController::class, 'store'])
        ->name('appointments.supplies.store');

    Route::delete('/appointments/{appointment}/supplies/{supply}', [AppointmentSupplyController::class, 'destroy'])
        ->name('appointments.supplies.destroy');
});



Route::prefix('app')->name('app.')->middleware(['auth', 'role:paciente'])->group(function () {
    Route::get('/', [PatientController::class, 'dashboard'])->name('dashboard');
    Route::get('/perfil',                  [PatientController::class, 'profile'])->name('profile');
    Route::post('/perfil',                 [PatientController::class, 'updateProfile'])->name('profile.update');
    Route::post('/perfil/password',        [PatientController::class, 'updatePassword'])->name('profile.password');

    Route::get('/odontograma',      [PatientController::class, 'odontogram'])->name('odontogram');
    // Citas
    Route::get('/citas', [PatientController::class, 'appointmentsIndex'])->name('appointments.index');
    Route::get('/citas/nueva', [PatientController::class, 'appointmentsCreate'])->name('appointments.create');
    Route::post('/citas', [PatientController::class, 'appointmentsStore'])->name('appointments.store');
    Route::post('/citas/{appointment}/cancelar', [PatientController::class, 'appointmentsCancel'])->name('appointments.cancel');

    // ➜ NUEVO: disponibilidad para el selector del paciente
    Route::get('/citas/disponibilidad', [PatientController::class, 'availability'])
        ->name('appointments.availability');
    Route::get('/citas/slot-chair', [PatientController::class, 'slotChair'])
        ->name('appointments.slotChair');

    // Facturas
    Route::get('/facturas', [PatientController::class, 'invoicesIndex'])->name('invoices.index');
    Route::get('/facturas/{invoice}', [PatientController::class, 'invoicesShow'])->name('invoices.show');
});
