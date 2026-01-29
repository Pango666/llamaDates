<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\{
    AppointmentController,
    PatientController,
    ServiceController,
    ScheduleController,
    MedicalHistoryController,
    AttachmentController,
    ClinicalNoteController,
    ConsentController,
    DiagnosisController,
    OdontogramController,
    TreatmentPlanController
};
use App\Http\Controllers\Api\WhatsAppWebHookController;
use App\Models\Dentist;
use App\Http\Controllers\TestWhatsAppController;
use App\Services\WhatsAppService;

Route::post('/test/whatsapp/send-message', function (\Illuminate\Http\Request $request, WhatsAppService $wa) {
    $data = $request->validate([
        'phone'   => 'required|string',
        'message' => 'required|string',
    ]);

    $res = $wa->sendMessage($data['phone'], $data['message']);

    return response()->json([
        'success' => $res['success'],
        'data'    => $res['data'] ?? null,
        'message' => $res['success']
            ? 'Message sent successfully!'
            : 'Failed to send message.',
    ], $res['status'] ?? 500);
});
Route::post('/test/whatsapp/send-template', [TestWhatsAppController::class, 'sendTestTemplate']);

Route::get('/whatsapp/webhook', [WhatsAppWebHookController::class, 'verify']);
Route::post('/whatsapp/webhook', [WhatsAppWebhookController::class, 'handle']);

// --- PÚBLICO / SEMI-PÚBLICO ---
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login',    [AuthController::class, 'login']);

Route::get('/services', [ServiceController::class, 'index']);
Route::get('/dentists', fn() => Dentist::select('id', 'name')->orderBy('name')->get());

// --- PROTEGIDO CON JWT ---
Route::middleware('auth:api')->group(function () {   


    // Auth
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);

    // Disponibilidad + Citas
    Route::get('/appointments', [AppointmentController::class, 'index']);
    Route::post('/appointments', [AppointmentController::class, 'store']);   // <- importante
    Route::post('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel']);
    Route::get('/availability', [AppointmentController::class, 'availability']);
    Route::post('/appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])->middleware('role:admin,recepcion,odontologo');
    Route::get('/appointments/month-stats', [AppointmentController::class, 'monthStats']);

    

    // Pacientes
    Route::get('/patients',           [PatientController::class, 'index'])->middleware('role:admin,recepcion,odontologo');
    Route::post('/patients',          [PatientController::class, 'store'])->middleware('role:admin,recepcion');
    Route::get('/patients/{patient}', [PatientController::class, 'show'])->middleware('role:admin,recepcion,odontologo');
    Route::put('/patients/{patient}', [PatientController::class, 'update'])->middleware('role:admin,recepcion');
    Route::delete('/patients/{patient}', [PatientController::class, 'destroy'])->middleware('role:admin');

    // Servicios (ABM)
    Route::post('/services',                [ServiceController::class, 'store'])->middleware('role:admin');
    Route::put('/services/{service}',       [ServiceController::class, 'update'])->middleware('role:admin');
    Route::post('/services/{service}/toggle', [ServiceController::class, 'toggle'])->middleware('role:admin');

    // Horarios de odontólogo
    Route::get('/dentists/{id}/schedules', [ScheduleController::class, 'show'])->middleware('role:admin,recepcion,odontologo');
    Route::put('/dentists/{id}/schedules', [ScheduleController::class, 'upsert'])->middleware('role:admin');

    // Historia clínica
    Route::get('/patients/{id}/history', [MedicalHistoryController::class, 'show'])->middleware('role:admin,recepcion,odontologo');
    Route::put('/patients/{id}/history', [MedicalHistoryController::class, 'upsert'])->middleware('role:admin,recepcion,odontologo');

    // Adjuntos
    Route::get('/attachments',              [AttachmentController::class, 'index'])->middleware('role:admin,recepcion,odontologo');
    Route::post('/attachments',             [AttachmentController::class, 'store'])->middleware('role:admin,recepcion,odontologo');
    Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy'])->middleware('role:admin');


    // ODONTOGRAMA
    Route::get('/patients/{id}/odontograms', [OdontogramController::class, 'index'])->middleware('role:admin,recepcion,odontologo');
    Route::post('/patients/{id}/odontograms', [OdontogramController::class, 'store'])->middleware('role:admin,recepcion,odontologo');
    Route::get('/odontograms/{odontogram}', [OdontogramController::class, 'show'])->middleware('role:admin,recepcion,odontologo');
    Route::put('/odontograms/{odontogram}', [OdontogramController::class, 'update'])->middleware('role:admin,recepcion,odontologo');
    Route::delete('/odontograms/{odontogram}', [OdontogramController::class, 'destroy'])->middleware('role:admin,recepcion');
    Route::post('/odontograms/{odontogram}/teeth', [OdontogramController::class, 'upsertTeeth'])->middleware('role:admin,recepcion,odontologo');
    Route::delete('/odontograms/teeth/{tooth}', [OdontogramController::class, 'deleteTooth'])->middleware('role:admin,recepcion');

    // DIAGNÓSTICOS
    Route::get('/patients/{id}/diagnoses', [DiagnosisController::class, 'index'])->middleware('role:admin,recepcion,odontologo');
    Route::post('/patients/{id}/diagnoses', [DiagnosisController::class, 'store'])->middleware('role:admin,recepcion,odontologo');
    Route::put('/diagnoses/{diagnosis}', [DiagnosisController::class, 'update'])->middleware('role:admin,recepcion,odontologo');
    Route::delete('/diagnoses/{diagnosis}', [DiagnosisController::class, 'destroy'])->middleware('role:admin');

    // PLANES & TRATAMIENTOS
    Route::get('/patients/{id}/treatment-plans', [TreatmentPlanController::class, 'index'])->middleware('role:admin,recepcion,odontologo');
    Route::post('/patients/{id}/treatment-plans', [TreatmentPlanController::class, 'store'])->middleware('role:admin,recepcion,odontologo');
    Route::get('/treatment-plans/{plan}', [TreatmentPlanController::class, 'show'])->middleware('role:admin,recepcion,odontologo');
    Route::put('/treatment-plans/{plan}', [TreatmentPlanController::class, 'update'])->middleware('role:admin,recepcion,odontologo');
    Route::delete('/treatment-plans/{plan}', [TreatmentPlanController::class, 'destroy'])->middleware('role:admin');

    Route::post('/treatment-plans/{plan}/items', [TreatmentPlanController::class, 'addItem'])->middleware('role:admin,recepcion,odontologo');
    Route::put('/treatments/{treatment}', [TreatmentPlanController::class, 'updateItem'])->middleware('role:admin,recepcion,odontologo');
    Route::delete('/treatments/{treatment}', [TreatmentPlanController::class, 'deleteItem'])->middleware('role:admin');

    Route::post('/treatment-plans/{plan}/approve', [TreatmentPlanController::class, 'approve'])->middleware('role:admin,odontologo');

    // NOTAS CLÍNICAS
    Route::get('/patients/{id}/notes', [ClinicalNoteController::class, 'index'])->middleware('role:admin,recepcion,odontologo');
    Route::post('/patients/{id}/notes', [ClinicalNoteController::class, 'store'])->middleware('role:admin,recepcion,odontologo');
    Route::put('/notes/{note}', [ClinicalNoteController::class, 'update'])->middleware('role:admin,recepcion,odontologo');
    Route::delete('/notes/{note}', [ClinicalNoteController::class, 'destroy'])->middleware('role:admin');

    // CONSENTIMIENTOS
    Route::get('/patients/{id}/consents', [ConsentController::class, 'index'])->middleware('role:admin,recepcion,odontologo');
    Route::post('/patients/{id}/consents', [ConsentController::class, 'store'])->middleware('role:admin,recepcion,odontologo');
    Route::post('/consents/{consent}/sign', [ConsentController::class, 'sign'])->middleware('role:admin,recepcion,odontologo');
    Route::delete('/consents/{consent}', [ConsentController::class, 'destroy'])->middleware('role:admin');


    // routes/api.php
    Route::get('/appointments/summary', [AppointmentController::class, 'summary']); // ?month=YYYY-MM&dentist_id=?

});

// Routes for WhatsApp Bot
Route::group(['prefix' => 'bot'], function () {
    Route::post('/check-patient', [\App\Http\Controllers\BotController::class, 'checkPatient']);
    Route::post('/register',      [\App\Http\Controllers\BotController::class, 'registerPatient']);
    Route::get('/services',       [\App\Http\Controllers\BotController::class, 'getServices']);
    Route::get('/dentists',       [\App\Http\Controllers\BotController::class, 'getDentists']);
    Route::post('/slots',         [\App\Http\Controllers\BotController::class, 'getSlots']);
    Route::post('/book',          [\App\Http\Controllers\BotController::class, 'bookAppointment']);
    Route::post('/my-appointments', [\App\Http\Controllers\BotController::class, 'myAppointments']);
    Route::post('/diagnosis',     [\App\Http\Controllers\BotController::class, 'aiDiagnosis']);
});
// --- MOBILE APP API (v1) ---
Route::group(['prefix' => 'v1/mobile', 'namespace' => 'App\Http\Controllers\Api\Mobile'], function () {
    
    // Auth (Public)
    Route::post('/login', [\App\Http\Controllers\Api\Mobile\AuthController::class, 'login']);
    Route::post('/password/email', [\App\Http\Controllers\Api\Mobile\PasswordResetController::class, 'sendResetLinkEmail']);

    // Protected
    Route::group(['middleware' => 'auth:api'], function () {
        // Auth & Profile
        Route::post('/logout',  [\App\Http\Controllers\Api\Mobile\AuthController::class, 'logout']);
        Route::post('/refresh', [\App\Http\Controllers\Api\Mobile\AuthController::class, 'refresh']);
        Route::get('/me',       [\App\Http\Controllers\Api\Mobile\AuthController::class, 'me']);
        Route::post('/change-password', [\App\Http\Controllers\Api\Mobile\AuthController::class, 'changePassword']);
        Route::post('/device-token', [\App\Http\Controllers\Api\Mobile\DeviceTokenController::class, 'store']);
        Route::delete('/device-token', [\App\Http\Controllers\Api\Mobile\DeviceTokenController::class, 'destroy']);
        Route::post('/device-token/test', [\App\Http\Controllers\Api\Mobile\DeviceTokenController::class, 'test']);

        // Profile
        Route::get('/profile',  [\App\Http\Controllers\Api\Mobile\PatientController::class, 'show']);
        Route::put('/profile',  [\App\Http\Controllers\Api\Mobile\PatientController::class, 'update']);
        Route::get('/odontogram', [\App\Http\Controllers\Api\Mobile\PatientController::class, 'odontogram']);
        
        // Billing
        Route::get('/invoices', [\App\Http\Controllers\Api\Mobile\BillingController::class, 'index']);
        Route::get('/invoices/{id}', [\App\Http\Controllers\Api\Mobile\BillingController::class, 'show']);

        // Public-ish Lists (but protected by auth for app usage)
        Route::get('/dentists', [\App\Http\Controllers\Api\Mobile\DentistController::class, 'index']);
        Route::get('/services', [\App\Http\Controllers\Api\Mobile\ServiceController::class, 'index']);

        // Appointments
        Route::get('/appointments',          [\App\Http\Controllers\Api\Mobile\AppointmentController::class, 'index']);
        Route::post('/appointments',         [\App\Http\Controllers\Api\Mobile\AppointmentController::class, 'store']);
        Route::post('/appointments/slots',   [\App\Http\Controllers\Api\Mobile\AppointmentController::class, 'slots']);
        Route::get('/appointments/{id}',     [\App\Http\Controllers\Api\Mobile\AppointmentController::class, 'show']);
        Route::post('/appointments/{id}/cancel', [\App\Http\Controllers\Api\Mobile\AppointmentController::class, 'cancel']);
    });
});
