<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('appointments:mark-non-attendance --chunk=500')
    ->everyMinute()
    ->withoutOverlapping();

/**
 * Send appointment reminders (1 hour before).
 */
use Illuminate\Support\Facades\Artisan;

/**
 * Send appointment reminders (1 hour or less before).
 * Run manually: php artisan appointments:send-reminders
 */
Artisan::command('appointments:send-reminders', function () {
    $now = now();
    $startWindow = $now->copy(); 
    $endWindow   = $now->copy()->addMinutes(65);

    $this->info("Buscando citas entre {$startWindow} y {$endWindow}...");

    // Buscar citas confirmadas entre AHORA y 65 minutos en el futuro
    $appointments = \App\Models\Appointment::with(['patient', 'service', 'dentist'])
        ->whereDate('date', $now->toDateString())
        ->whereIn('status', ['reserved', 'confirmed'])
        ->where('is_active', true)
        ->get()
        ->filter(function ($app) use ($startWindow, $endWindow) {
            // $app->date is Carbon due to cast, so use ->format('Y-m-d')
            $appStart = \Carbon\Carbon::parse($app->date->format('Y-m-d') . ' ' . $app->start_time);
            return $appStart->between($startWindow, $endWindow);
        });

    $count = 0;
    foreach ($appointments as $app) {
        if (!$app->patient || !$app->patient->email) continue;

        // Evitar duplicados recientes
        $alreadySent = \App\Models\EmailLog::where('to', $app->patient->email)
            ->where('subject', 'Recordatorio de Cita - DentalCare')
            ->where('created_at', '>=', now()->subHours(2))
            ->exists();

        if ($alreadySent) {
            $this->comment("Skipping #{$app->id} (ya enviado).");
            continue;
        }

        try {
            \Illuminate\Support\Facades\Mail::to($app->patient->email)
                ->send(new \App\Mail\AppointmentReminder($app));

            \App\Models\EmailLog::create([
                'to' => $app->patient->email,
                'subject' => 'Recordatorio de Cita - DentalCare',
                'status' => 'sent',
                'sent_at' => now(),
            ]);
            
            \Illuminate\Support\Facades\Log::info("Recordatorio enviado a {$app->patient->email} para cita #{$app->id}");
            $this->info("Enviado a: {$app->patient->email}");
            $count++;

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error enviando recordatorio cita #{$app->id}: " . $e->getMessage());
             \App\Models\EmailLog::create([
                'to' => $app->patient->email,
                'subject' => 'Recordatorio de Cita - DentalCare',
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);
            $this->error("Error #{$app->id}: {$e->getMessage()}");
        }
    }
    $this->info("Proceso terminado. $count recordatorios enviados.");

})->purpose('Send appointment reminders');

// Schedule the command
Schedule::command('appointments:send-reminders')->everyFiveMinutes();
