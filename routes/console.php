<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('appointments:mark-non-attendance --chunk=500')
    ->everyMinute()
    ->withoutOverlapping();

/**
 * Send appointment reminders (1 hour before).
 */
Schedule::call(function () {
    $now = now();
    $startWindow = $now->copy(); // Desde AHORA (para cubrir eventuales caídas del cron)
    $endWindow   = $now->copy()->addMinutes(65);

    // Buscar citas confirmadas entre 60 y 65 minutos en el futuro
    $appointments = \App\Models\Appointment::with(['patient', 'service', 'dentist'])
        ->whereDate('date', $now->toDateString())
        ->whereIn('status', ['reserved', 'confirmed'])
        ->where('is_active', true)
        ->get()
        ->filter(function ($app) use ($startWindow, $endWindow) {
            $appStart = \Carbon\Carbon::parse($app->date . ' ' . $app->start_time);
            return $appStart->between($startWindow, $endWindow);
        });

    foreach ($appointments as $app) {
        if (!$app->patient || !$app->patient->email) continue;

        // Evitar duplicados recientes (check log de las últimas 2 horas)
        $alreadySent = \App\Models\EmailLog::where('to', $app->patient->email)
            ->where('subject', 'Recordatorio de Cita - DentalCare')
            ->where('created_at', '>=', now()->subHours(2))
            ->exists();

        if ($alreadySent) continue;

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

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error enviando recordatorio cita #{$app->id}: " . $e->getMessage());
             \App\Models\EmailLog::create([
                'to' => $app->patient->email,
                'subject' => 'Recordatorio de Cita - DentalCare',
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);
        }
    }
})->everyFiveMinutes()->name('appointments:send-reminders');
