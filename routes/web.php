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

// INVENTARIO (nuevos)
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\InventoryMovementController;
use App\Http\Controllers\AppointmentSupplyController;
use App\Http\Controllers\MeasurementUnitController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductPresentationUnitController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EmailLogController;
use App\Http\Controllers\BackupController;

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
| Autenticados (logout + redirects base)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Redirección inteligente según rol/permisos
    Route::get('/', function () {
        $u = auth()->user();

        if (!$u) {
            return redirect()->route('login');
        }

        // Paciente => app.*
        if ($u->hasRole('paciente')) {
            return redirect()->route('app.dashboard');
        }

        // Si puede ver el dashboard => dashboard admin
        if ($u->can('dashboard.view')) {
            return redirect()->route('admin.dashboard');
        }

        // Cajeros / pagos
        if ($u->can('billing.manage') || $u->can('payments.view_status')) {
            return redirect()->route('admin.billing');
        }

        // Gente de agenda / citas
        if ($u->can('appointments.manage') || $u->can('agenda.view')) {
            return redirect()->route('admin.appointments.index');
        }

        // Fallback
        return redirect()->route('admin.dashboard');
    })->name('home');

    Route::get('/dashboard', function () {
        $u = auth()->user();

        if (!$u) {
            return redirect()->route('login');
        }

        if (method_exists($u, 'hasRole') && $u->hasRole('paciente')) {
            return redirect()->route('app.dashboard');
        }

        if ($u->can('dashboard.view')) {
            return redirect()->route('admin.dashboard');
        }

        if ($u->can('billing.manage') || $u->can('payments.view_status')) {
            return redirect()->route('admin.billing');
        }

        if ($u->can('appointments.manage') || $u->can('agenda.view')) {
            return redirect()->route('admin.appointments.index');
        }

        return redirect()->route('admin.dashboard');
    })->name('dashboard');

    /*
    | PERFIL DE USUARIO
    */
    Route::get('/admin/perfil', [\App\Http\Controllers\ProfileController::class, 'show'])->name('admin.profile');
    Route::post('/admin/perfil/update', [\App\Http\Controllers\ProfileController::class, 'update'])->name('admin.profile.update');
    Route::post('/admin/perfil/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('admin.profile.password');

});

/*
|--------------------------------------------------------------------------
| ADMIN (panel principal)
|   Cualquier usuario autenticado.
|   El acceso real lo controlan los "permission:*".
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    /*
    | CONFIRMACION POR EMAIL (Public Signed Route)
    */
    Route::get('/citas/confirmar/{appointment}', [AppointmentController::class, 'confirmByEmail'])
        ->name('appointments.confirm_email')
        ->middleware('signed');

    /*
    | DASHBOARD
    */
    Route::get('/admin', [AppointmentController::class, 'dashboard'])
        ->name('admin.dashboard')
        ->middleware('permission:dashboard.view');

    Route::get('/admin/dashboard/data', [AppointmentController::class, 'dashboardData'])
        ->name('admin.dashboard.data')
        ->middleware('permission:dashboard.view');

    /*
    | MODULO DE CITAS Y RESERVAS
    | Acceso para quienes puedan ver/gestionar citas
    */
    Route::middleware('permission:appointments.index')->group(function () {
        Route::get('/admin/citas', [AppointmentController::class, 'adminIndex'])->name('admin.appointments.index');
        Route::get('/admin/citas/pdf', [AppointmentController::class, 'pdf'])->name('admin.appointments.pdf');
        Route::get('/admin/citas/nueva', [AppointmentController::class, 'createForm'])->name('admin.appointments.create');
        Route::post('/admin/citas', [AppointmentController::class, 'store'])->name('admin.appointments.store');
        Route::get('/admin/citas/disponibilidad', [AppointmentController::class, 'availability'])->name('admin.appointments.availability');
        Route::get('/admin/citas/slot-chair', [AppointmentController::class, 'slotChair'])->name('admin.appointments.slotChair');
        Route::get('/admin/citas/{appointment}', [AppointmentController::class, 'show'])->name('admin.appointments.show');
        Route::post('/admin/citas/{appointment}/estado', [AppointmentController::class, 'updateStatus'])->name('admin.appointments.status');
        Route::post('/admin/citas/{appointment}/cancelar', [AppointmentController::class, 'cancel'])->name('admin.appointments.cancel');
    });

    /*
    | MODULO DE HORARIOS
    | Ver: schedules.view | Editar: schedules.index
    */
    Route::middleware('permission:schedules.view')->group(function () {
        Route::get('/admin/horarios',                   [ScheduleController::class, 'index'])->name('admin.schedules');
        Route::get('/admin/horarios/{dentist}/chairs/options', [ScheduleController::class, 'chairOptions'])->name('admin.schedules.chairs.options');
    });
    Route::middleware('permission:schedules.index')->group(function () {
        Route::get('/admin/horarios/{dentist}',         [ScheduleController::class, 'edit'])->name('admin.schedules.edit');
        Route::post('/admin/horarios/{dentist}',        [ScheduleController::class, 'update'])->name('admin.schedules.update');
    });

    /*
    | MODULO DE CONSULTORIOS/SILLAS
    | Ver: chairs.view | Editar: chairs.index
    */
    Route::middleware('permission:chairs.view')->group(function () {
        Route::get('/admin/sillas',                  [ChairController::class, 'index'])->name('admin.chairs.index');
        Route::get('/admin/sillas/ocupacion',        [ChairController::class, 'usageByWeekday'])->name('admin.chairs.usage');
    });
    Route::middleware('permission:chairs.index')->group(function () {
        Route::get('/admin/sillas/crear',            [ChairController::class, 'create'])->name('admin.chairs.create');
        Route::post('/admin/sillas',                 [ChairController::class, 'store'])->name('admin.chairs.store');
        Route::get('/admin/sillas/{chair}/editar',   [ChairController::class, 'edit'])->name('admin.chairs.edit');
        Route::put('/admin/sillas/{chair}',          [ChairController::class, 'update'])->name('admin.chairs.update');
        Route::delete('/admin/sillas/{chair}',       [ChairController::class, 'destroy'])->name('admin.chairs.destroy');
    });

    /*
    | MODULO DE PACIENTES
    */
    Route::middleware('permission:patients.index')->group(function () {
        Route::get('/admin/pacientes',                 [PatientController::class, 'index'])->name('admin.patients.index');
        Route::get('/admin/pacientes/nuevo',           [PatientController::class, 'create'])->name('admin.patients.create');
        Route::post('/admin/pacientes',                [PatientController::class, 'store'])->name('admin.patients.store');
        Route::get('/admin/pacientes/{patient}',       [PatientController::class, 'show'])->name('admin.patients.show');
        Route::get('/admin/pacientes/{patient}/editar', [PatientController::class, 'edit'])->name('admin.patients.edit');
        Route::put('/admin/pacientes/{patient}',       [PatientController::class, 'update'])->name('admin.patients.update');
        Route::delete('/admin/pacientes/{patient}',    [PatientController::class, 'destroy'])->name('admin.patients.destroy');
        Route::post('/admin/pacientes/{patient}/toggle', [PatientController::class, 'toggle'])->name('admin.patients.toggle');

        Route::get('/admin/pacientes/{patient}/historia-completa', [MedicalHistoryController::class, 'show'])->name('admin.patients.record');
        Route::put('/admin/pacientes/{patient}/historia',          [MedicalHistoryController::class, 'update'])->name('admin.patients.history.update');

        Route::get('/admin/patients/by-ci/{ci}', [PatientController::class, 'findByCI'])
            ->name('admin.patients.by_ci');
    });

    /*
    | MODULO DE ODONTOLOGOS
    */
    Route::middleware('permission:users.manage')->group(function () {
        Route::get('/admin/odontologos',                    [DentistController::class, 'index'])->name('admin.dentists');
        Route::get('/admin/odontologos/nuevo',              [DentistController::class, 'create'])->name('admin.dentists.create');
        Route::post('/admin/odontologos',                   [DentistController::class, 'store'])->name('admin.dentists.store');
        Route::get('/admin/odontologos/{dentist}',          [DentistController::class, 'show'])->name('admin.dentists.show');
        Route::get('/admin/odontologos/{dentist}/editar',   [DentistController::class, 'edit'])->name('admin.dentists.edit');
        Route::put('/admin/odontologos/{dentist}',          [DentistController::class, 'update'])->name('admin.dentists.update');
        Route::delete('/admin/odontologos/{dentist}',       [DentistController::class, 'destroy'])->name('admin.dentists.destroy');
        Route::post('/admin/odontologos/{dentist}/toggle',  [DentistController::class, 'toggle'])->name('admin.dentists.toggle');
    });

    /*
    | MODULO DE ODONTOGRAMAS
    | - Admin: ver y editar odontogramas de pacientes
    | - Ruta corta /odontograma para usar en las citas:
    |   /odontograma?patient=5&appointment_id=3
    */
    Route::middleware('permission:odontograms.manage')->group(function () {
        Route::get('/admin/patients/{patient}/odontograms',  [OdontogramController::class, 'open'])->name('admin.odontograms.open');
        Route::get('/admin/odontograms/{odontogram}',        [OdontogramController::class, 'show'])->name('admin.odontograms.show');
        Route::post('/admin/odontograms/{odontogram}/teeth', [OdontogramController::class, 'upsertTeeth'])->name('admin.odontograms.teeth.upsert');
    });

    // Ruta usada desde las citas (no la toquemos, solo exige auth)
    Route::get('/odontograma', [PatientController::class, 'odontogram'])->name('odontogram');

    /*
    | MODULO DE SERVICIOS
    | Ver: services.view | Editar: services.index
    */
    Route::middleware(['permission:services.view', 'restrict.dentist'])->group(function () {
        Route::get('/admin/servicios',                    [ServiceController::class, 'index'])->name('admin.services');
    });
    Route::middleware(['permission:services.index', 'restrict.dentist'])->group(function () {
        Route::get('/admin/servicios/nuevo',              [ServiceController::class, 'create'])->name('admin.services.create');
        Route::post('/admin/servicios',                   [ServiceController::class, 'store'])->name('admin.services.store');
        Route::get('/admin/servicios/{service}/editar',   [ServiceController::class, 'edit'])->name('admin.services.edit');
        Route::put('/admin/servicios/{service}',          [ServiceController::class, 'update'])->name('admin.services.update');
        Route::post('/admin/servicios/{service}/toggle',  [ServiceController::class, 'toggle'])->name('admin.services.toggle');
        Route::delete('/admin/servicios/{service}',       [ServiceController::class, 'destroy'])->name('admin.services.destroy');
    });

    /*
    | MODULO DE PAGOS Y FACTURACION
    */
    Route::middleware('permission:billing.index')->group(function () {
        Route::get('/admin/pagos',                            [BillingController::class, 'index'])->name('admin.billing');
        Route::get('/admin/pagos/reporte-pdf',                [BillingController::class, 'pdfExport'])->name('admin.billing.pdf'); // <-- Nuevo Reporte
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

        // Atajos facturas
        Route::get('/admin/invoices/{invoice}',               [BillingController::class, 'show'])->name('admin.invoices.show');
        Route::get('/admin/invoices/{invoice}/view',          [BillingController::class, 'view'])->name('admin.invoices.view');
        Route::post('/admin/invoices/{invoice}/payments',     [BillingController::class, 'storePayment'])->name('admin.invoices.payments.store');
        Route::post('/admin/invoices/{invoice}/mark-paid',    [BillingController::class, 'markPaid'])->name('admin.invoices.markPaid');
        Route::get('/admin/invoices/{invoice}/download',      [BillingController::class, 'download'])->name('admin.invoices.download');
        Route::post('/admin/invoices/{invoice}/regenerate',   [BillingController::class, 'regenerate'])->name('admin.invoices.regenerate');

        Route::get('invoices/from-appointment/{appointment}',  [BillingController::class, 'createFromAppointment'])->name('admin.invoices.createFromAppointment');
        Route::post('invoices/from-appointment/{appointment}', [BillingController::class, 'storeFromAppointment'])->name('admin.invoices.storeFromAppointment');

        // Facturar plan
        Route::get('plans/{plan}/invoice',  [BillingController::class, 'createFromPlan'])->name('admin.plans.invoice.create');
        Route::post('plans/{plan}/invoice', [BillingController::class, 'storeFromPlan'])->name('admin.plans.invoice.store');
    });

    /*
    | MODULO DE CONSENTIMIENTOS
    */
    Route::middleware('permission:medical_history.manage')->group(function () {
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
    });

    /*
    | MODULO DE NOTAS / DIAGNOSTICOS / ADJUNTOS
    */
    Route::middleware('permission:clinical_notes.manage')->group(function () {
        Route::post('appointments/{appointment}/notes', [ClinicalNoteController::class, 'store'])->name('admin.appointments.notes.store');
        Route::delete('notes/{note}', [ClinicalNoteController::class, 'destroy'])->name('admin.notes.destroy');

        Route::post('appointments/{appointment}/diagnoses', [DiagnosisController::class, 'store'])->name('admin.appointments.diagnoses.store');
        Route::delete('diagnoses/{diagnosis}', [DiagnosisController::class, 'destroy'])->name('admin.diagnoses.destroy');

        Route::post('appointments/{appointment}/attachments', [AttachmentController::class, 'store'])->name('admin.appointments.attachments.store');
        Route::delete('attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('admin.attachments.destroy');
    });

    /*
    | MODULO DE PLANES DE TRATAMIENTO
    */
    Route::middleware('permission:treatment_plans.manage')->group(function () {
        Route::get('patients/{patient}/plans',            [TreatmentPlanController::class, 'index'])->name('admin.patients.plans.index');
        Route::get('patients/{patient}/plans/create',     [TreatmentPlanController::class, 'create'])->name('admin.patients.plans.create');
        Route::post('patients/{patient}/plans',           [TreatmentPlanController::class, 'store'])->name('admin.patients.plans.store');

        // Agendar cita desde un tratamiento
        Route::get('treatments/{treatment}/schedule', [TreatmentController::class, 'schedule'])->name('admin.treatments.schedule');

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
    });

    // Alias legacy
    Route::get('/appointments', fn() => redirect()->route('admin.appointments.index'))->name('appointments.legacy');

    /*
    | USUARIOS / ROLES / PERMISOS
    */
    Route::middleware('permission:users.manage')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('admin.users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('admin.users.create');
        Route::post('/users', [UserController::class, 'store'])->name('admin.users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
    });

    /*
    | MODULO DE ROLES
    | Ver: roles.view | Editar: roles.index
    */
    Route::middleware('permission:roles.view')->group(function () {
        Route::get('/admin/roles', [RoleController::class, 'index'])->name('admin.roles.index');
    });
    Route::middleware('permission:roles.index')->group(function () {
        Route::get('/admin/roles/crear', [RoleController::class, 'create'])->name('admin.roles.create');
        Route::post('/admin/roles', [RoleController::class, 'store'])->name('admin.roles.store');
        Route::get('/admin/roles/{role}/editar', [RoleController::class, 'edit'])->name('admin.roles.edit');
        Route::put('/admin/roles/{role}', [RoleController::class, 'update'])->name('admin.roles.update');
        Route::delete('/admin/roles/{role}', [RoleController::class, 'destroy'])->name('admin.roles.destroy');
        Route::get('/admin/roles/{role}/permisos', [RoleController::class, 'editPerms'])->name('admin.roles.perms');
        Route::put('/admin/roles/{role}/permisos', [RoleController::class, 'updatePerms'])->name('admin.roles.update.perms');
        Route::post('/admin/roles/{role}/toggle', [RoleController::class, 'toggle'])->name('admin.roles.toggle');
    });

    /*
    | MODULO DE PERMISOS
    */
    Route::middleware('permission:permissions.manage')->group(function () {
        Route::get('/permissions', [PermissionController::class, 'index'])->name('admin.permissions.index');
        Route::get('/permissions/create', [PermissionController::class, 'create'])->name('admin.permissions.create');
        Route::post('/permissions', [PermissionController::class, 'store'])->name('admin.permissions.store');
        Route::get('/permissions/{permission}/edit', [PermissionController::class, 'edit'])->name('admin.permissions.edit');
        Route::put('/permissions/{permission}', [PermissionController::class, 'update'])->name('admin.permissions.update');
        Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy'])->name('admin.permissions.destroy');
    });

    /*
    | INVENTARIO (admin.inv.*)
    */
    Route::middleware('permission:inventory.manage')->group(function () {
        // Productos
        Route::get('inv/products',                 [ProductController::class, 'index'])->name('admin.inv.products.index');
        Route::get('inv/products/create',          [ProductController::class, 'create'])->name('admin.inv.products.create');
        Route::post('inv/products',                [ProductController::class, 'store'])->name('admin.inv.products.store');
        Route::get('inv/products/{product}/edit',  [ProductController::class, 'edit'])->name('admin.inv.products.edit');
        Route::put('inv/products/{product}',       [ProductController::class, 'update'])->name('admin.inv.products.update');
        Route::delete('inv/products/{product}',    [ProductController::class, 'destroy'])->name('admin.inv.products.destroy');
        Route::post('inv/products/{product}/toggle', [ProductController::class, 'toggle'])->name('admin.inv.products.toggle');
        Route::post('inv/products/{product}/update-batch', [ProductController::class, 'updateBatch'])->name('admin.inv.products.update_batch');

        // Categorías
        Route::get('inv/product-categories',                        [ProductCategoryController::class, 'index'])->name('admin.inv.product_categories.index');
        Route::get('inv/product-categories/create',                 [ProductCategoryController::class, 'create'])->name('admin.inv.product_categories.create');
        Route::post('inv/product-categories',                       [ProductCategoryController::class, 'store'])->name('admin.inv.product_categories.store');
        Route::get('inv/product-categories/{productCategory}/edit', [ProductCategoryController::class, 'edit'])->name('admin.inv.product_categories.edit');
        Route::put('inv/product-categories/{productCategory}',      [ProductCategoryController::class, 'update'])->name('admin.inv.product_categories.update');
        Route::delete('inv/product-categories/{productCategory}',   [ProductCategoryController::class, 'destroy'])->name('admin.inv.product_categories.destroy');

        // Proveedores
        Route::get('inv/suppliers',               [SupplierController::class, 'index'])->name('admin.inv.suppliers.index');
        Route::get('inv/suppliers/create',        [SupplierController::class, 'create'])->name('admin.inv.suppliers.create');
        Route::post('inv/suppliers',              [SupplierController::class, 'store'])->name('admin.inv.suppliers.store');
        Route::get('inv/suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('admin.inv.suppliers.edit');
        Route::put('inv/suppliers/{supplier}',    [SupplierController::class, 'update'])->name('admin.inv.suppliers.update');
        Route::post('inv/suppliers/{supplier}/toggle', [SupplierController::class, 'toggle'])->name('admin.inv.suppliers.toggle');
        Route::delete('inv/suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('admin.inv.suppliers.destroy');

        // Unidades de medida
        Route::get('inv/measurement-units',                           [MeasurementUnitController::class, 'index'])->name('admin.inv.measurement_units.index');
        Route::get('inv/measurement-units/create',                    [MeasurementUnitController::class, 'create'])->name('admin.inv.measurement_units.create');
        Route::post('inv/measurement-units',                          [MeasurementUnitController::class, 'store'])->name('admin.inv.measurement_units.store');
        Route::get('inv/measurement-units/{measurementUnit}/edit',    [MeasurementUnitController::class, 'edit'])->name('admin.inv.measurement_units.edit');
        Route::put('inv/measurement-units/{measurementUnit}',         [MeasurementUnitController::class, 'update'])->name('admin.inv.measurement_units.update');
        Route::delete('inv/measurement-units/{measurementUnit}',      [MeasurementUnitController::class, 'destroy'])->name('admin.inv.measurement_units.destroy');
        Route::post('inv/measurement-units/{measurementUnit}/toggle', [MeasurementUnitController::class, 'toggle'])->name('admin.inv.measurement_units.toggle');

        // Unidades de presentación
        Route::get('inv/presentation-units',                          [ProductPresentationUnitController::class, 'index'])->name('admin.inv.presentation_units.index');
        Route::get('inv/presentation-units/create',                   [ProductPresentationUnitController::class, 'create'])->name('admin.inv.presentation_units.create');
        Route::post('inv/presentation-units',                         [ProductPresentationUnitController::class, 'store'])->name('admin.inv.presentation_units.store');
        Route::get('inv/presentation-units/{presentationUnit}/edit',  [ProductPresentationUnitController::class, 'edit'])->name('admin.inv.presentation_units.edit');
        Route::put('inv/presentation-units/{presentationUnit}',       [ProductPresentationUnitController::class, 'update'])->name('admin.inv.presentation_units.update');
        Route::delete('inv/presentation-units/{presentationUnit}',    [ProductPresentationUnitController::class, 'destroy'])->name('admin.inv.presentation_units.destroy');
        Route::post('inv/presentation-units/{presentationUnit}/toggle', [ProductPresentationUnitController::class, 'toggle'])->name('admin.inv.presentation_units.toggle');

        // Movimientos de inventario
        Route::get('inv/movements/export/pdf', [InventoryMovementController::class, 'exportPdf'])->name('admin.inv.movs.export.pdf');
        Route::get('inv/movements/export/csv', [InventoryMovementController::class, 'exportCsv'])->name('admin.inv.movs.export.csv');
        Route::get('inv/movements',            [InventoryMovementController::class, 'index'])->name('admin.inv.movs.index');
        Route::post('inv/products/{product}/batch', [ProductController::class, 'updateBatch'])->name('admin.inv.products.update_batch');
        Route::get('inv/movements/create',    [InventoryMovementController::class, 'create'])->name('admin.inv.movs.create');
        Route::get('inv/movements/products-options', [InventoryMovementController::class, 'productOptions'])->name('admin.inv.movs.products_options');
        Route::get('inv/movements/lots-options', [InventoryMovementController::class, 'lotOptions'])->name('admin.inv.movs.lots_options');
        Route::post('inv/movements',          [InventoryMovementController::class, 'store'])->name('admin.inv.movs.store');

        // Logs de Correos
        // Notifications
        Route::get('notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('admin.notifications.index');
        Route::get('notifications/test', [\App\Http\Controllers\NotificationController::class, 'test'])->name('admin.notifications.test');
        Route::get('notifications/users-search', [\App\Http\Controllers\NotificationController::class, 'searchUsers'])->name('admin.notifications.users_search');
        Route::post('notifications/test', [\App\Http\Controllers\NotificationController::class, 'sendTest'])->name('admin.notifications.sendTest');
        Route::get('/admin/inv/movimientos',       [InventoryMovementController::class, 'index'])->name('admin.inv.movs.index');
        Route::get('/admin/inv/movimientos/nuevo', [InventoryMovementController::class, 'create'])->name('admin.inv.movs.create');
        Route::post('/admin/inv/movimientos',      [InventoryMovementController::class, 'store'])->name('admin.inv.movs.store');

        // Suministros por cita
        Route::post('/appointments/{appointment}/supplies', [AppointmentSupplyController::class, 'store'])
            ->name('appointments.supplies.store');
        Route::delete('/appointments/{appointment}/supplies/{supply}', [AppointmentSupplyController::class, 'destroy'])
            ->name('appointments.supplies.destroy');
    });

    /*
    | MODULO DE RESPALDOS Y MANTENIMIENTO
    */
    Route::middleware('permission:users.manage')->group(function () {
        Route::get('/admin/backups', [BackupController::class, 'index'])->name('admin.backups.index');
        Route::post('/admin/backups/database', [BackupController::class, 'backupDatabase'])->name('admin.backups.database');
        Route::post('/admin/backups/files', [BackupController::class, 'backupFiles'])->name('admin.backups.files');
        Route::get('/admin/backups/download/{filename}', [BackupController::class, 'download'])->name('admin.backups.download');
        Route::delete('/admin/backups/{filename}', [BackupController::class, 'delete'])->name('admin.backups.delete');
        Route::post('/admin/backups/clear/cache', [BackupController::class, 'clearCache'])->name('admin.backups.clear.cache');
        Route::post('/admin/backups/clear/config', [BackupController::class, 'clearConfig'])->name('admin.backups.clear.config');
        Route::post('/admin/backups/clear/views', [BackupController::class, 'clearViews'])->name('admin.backups.clear.views');
        Route::post('/admin/backups/clear/routes', [BackupController::class, 'clearRoutes'])->name('admin.backups.clear.routes');
        Route::post('/admin/backups/clear/all', [BackupController::class, 'clearAll'])->name('admin.backups.clear.all');
        Route::post('/admin/backups/clear/logs', [BackupController::class, 'clearLogs'])->name('admin.backups.clear.logs');
    });

    /*
    | MODULO DE AUDITORÍA
    */
    Route::middleware('permission:users.manage')->group(function () {
        Route::get('/admin/audit-logs', [\App\Http\Controllers\AuditLogController::class, 'index'])->name('admin.audit.index');
    });
});

/*
|--------------------------------------------------------------------------
| APP PACIENTE (front del paciente)
|--------------------------------------------------------------------------
*/
Route::prefix('app')->name('app.')->middleware(['auth', 'role:paciente'])->group(function () {
    Route::get('/', [PatientController::class, 'dashboard'])->name('dashboard');

    Route::get('/perfil',           [PatientController::class, 'profile'])->name('profile');
    Route::post('/perfil',          [PatientController::class, 'updateProfile'])->name('profile.update');
    Route::post('/perfil/password', [PatientController::class, 'updatePassword'])->name('profile.password');

    // Odontograma del paciente (versión front paciente)
    Route::get('/odontograma',      [PatientController::class, 'odontogram'])->name('odontogram');

    // Citas
    Route::get('/citas',        [PatientController::class, 'appointmentsIndex'])->name('appointments.index');
    // Crear (Form)
    Route::get('/citas/nueva',  [PatientController::class, 'appointmentsCreate'])->name('appointments.create');
    Route::post('/citas',       [PatientController::class, 'appointmentsStore'])->name('appointments.store');
    Route::get('/citas/disponibilidad', [PatientController::class, 'availability'])->name('appointments.availability');
    Route::get('/citas/{appointment}', [PatientController::class, 'appointmentsShow'])->name('appointments.show');
    Route::post('/citas/{appointment}/cancelar', [PatientController::class, 'appointmentsCancel'])->name('appointments.cancel');
    Route::post('/citas/{appointment}/confirm', [PatientController::class, 'appointmentsConfirm'])->name('appointments.confirm');

    
    Route::get('/citas/slot-chair',     [PatientController::class, 'slotChair'])->name('appointments.slotChair');

    // Facturas
    Route::get('/facturas',           [PatientController::class, 'invoicesIndex'])->name('invoices.index');
    Route::get('/facturas/{invoice}', [PatientController::class, 'invoicesShow'])->name('invoices.show');
});

