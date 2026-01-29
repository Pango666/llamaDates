<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;

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

    Log::info("CRON: appointments:send-reminders STARTED. Window: {$startWindow} - {$endWindow}");
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

    Log::info("CRON: Found " . $appointments->count() . " appointments in window.");

    $count = 0;
    
    // Instanciar el Manager (o inyectarlo)
    $notifier = new \App\Services\NotificationManager();

    foreach ($appointments as $app) {
        // Verificar si el paciente tiene usuario asociado (para Push/Email/WhatsApp)
        if (!$app->patient || !$app->patient->user_id) {
            Log::warning("CRON: Appointment #{$app->id} ignored. No patient or user_id.");
            continue;
        }
        
        $user = \App\Models\User::find($app->patient->user_id);
        if (!$user) {
            Log::warning("CRON: Appointment #{$app->id} ignored. User ID {$app->patient->user_id} not found.");
            continue;
        }

        // Definir qué canales usar
        $channels = ['email'];
        if ($user) $channels[] = 'push';
        if ($user && !empty($user->phone)) $channels[] = 'whatsapp';

        // Log visual
        $this->output->write("Procesando Cita #{$app->id} User #{$user->id}... ");
        Log::info("CRON: Processing Appt #{$app->id} for User #{$user->id}. Channels: " . implode(',', $channels));

        // Enviar usando el Manager (él se encarga de no duplicar)
        // El Manager retorna array: ['email' => 'sent', 'whatsapp' => 'skipped_duplicate', etc]
        try {
            $results = $notifier->send(
                user: $user,
                type: 'appointment_reminder',
                channels: $channels,
                appointment: $app,
                data: [
                    'title' => '⏰ Recordatorio de Cita',
                    'body'  => "Hola {$user->name}, recordamos que tienes una cita hoy a las " . substr($app->start_time, 0, 5) . ". ¡Te esperamos!"
                ]
            );

            // Logging resultado
            $this->info("Res: " . json_encode($results));
            Log::info("CRON: Result Appt #{$app->id}: " . json_encode($results));
            
            if (($results['email'] ?? '') === 'sent' || ($results['push'] ?? '') === 'sent' || ($results['whatsapp'] ?? '') === 'sent') {
                $count++;
                
                // ANTI-BAN DELAY: Si se envió por WhatsApp, pausar 500ms-1s
                if (($results['whatsapp'] ?? '') === 'sent') {
                    usleep(500000); // 0.5 segundos
                }
            }
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            Log::error("CRON: Failed processing Appt #{$app->id}: " . $e->getMessage());
        }
    }
    $this->info("Proceso terminado. $count recordatorios enviados/procesados.");
    Log::info("CRON: appointments:send-reminders FINISHED. Processed: $count");

})->purpose('Send appointment reminders');

// Schedule the command
Schedule::command('appointments:send-reminders')->everyFiveMinutes();
