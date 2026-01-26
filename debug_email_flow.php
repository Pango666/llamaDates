<?php

use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\EmailLog;
use App\Mail\AppointmentConfirmation;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- STARTING EMAIL TEST ---\n";

// 1. Check Configuration
$transport = config('mail.default');
echo "Mail Transport: {$transport}\n";
echo "Mail Host: " . config('mail.mailers.smtp.host') . "\n";
echo "Mail Port: " . config('mail.mailers.smtp.port') . "\n";
echo "Mail Username: " . config('mail.mailers.smtp.username') . "\n";

// 2. Find Patient
$patient = Patient::where('email', '!=', '')->first();
if (!$patient) {
    die("No patient with email found.\n");
}
echo "Testing with Patient: {$patient->name} ({$patient->email})\n";

// 3. Create Mock Appointment (InMemory)
$appt = Appointment::first();
if(!$appt) {
    die("No appointments in DB to test with.\n");
}
$appt->patient = $patient; // force relation for test

// 4. Try Send
try {
    echo "Attempting to send...\n";
    Mail::to($patient->email)->send(new AppointmentConfirmation($appt));
    echo "Mail::send completed without exception.\n";
    
    // Log success manually to verify DB connection
    EmailLog::create([
        'to' => $patient->email,
        'subject' => 'TEST EMAIL - ' . now(),
        'status' => 'sent',
        'sent_at' => now(),
    ]);
    echo "Logged to DB successfully.\n";

} catch (\Exception $e) {
    echo "ERROR SENDING: " . $e->getMessage() . "\n";
    EmailLog::create([
        'to' => $patient->email,
        'subject' => 'TEST EMAIL FAILED',
        'status' => 'failed',
        'error' => $e->getMessage(),
    ]);
}

echo "--- END TEST ---\n";
