<?php

namespace App\Services;

use App\Models\NotificationLog;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class NotificationManager
{
    /**
     * Send a notification through requested channels, avoiding duplicates.
     *
     * @param User $user Recipient user
     * @param string $type e.g., 'reminder', 'confirmation'
     * @param array $channels ['email', 'push']
     * @param Appointment|null $appointment Related appointment (for deduplication)
     * @param array $data Additional data for the message
     * @return array Status of each channel
     */
    public function send(User $user, string $type, array $channels, ?Appointment $appointment = null, array $data = [])
    {
        $results = [];

        foreach ($channels as $channel) {
            if ($this->shouldSend($user, $type, $channel, $appointment)) {
                $results[$channel] = $this->dispatch($user, $type, $channel, $appointment, $data);
            } else {
                $results[$channel] = 'skipped_duplicate';
            }
        }

        return $results;
    }

    /**
     * Check if the notification should be sent.
     */
    protected function shouldSend(User $user, string $type, string $channel, ?Appointment $appointment)
    {
        if (!$appointment) return true; // Si no hay cita, no podemos chequear duplicados por cita

        // IMPORTANTE: Aquí está la lógica crítica.
        // Check for 'sent' OR 'pending' (to avoid race conditions if cron overlaps)
        return !NotificationLog::where('appointment_id', $appointment->id)
            ->where('channel', $channel)
            ->where('type', $type)
            ->whereIn('status', ['sent', 'pending'])
            ->exists();
    }

    /**
     * Dispatch the actual message and log the result.
     */
    protected function dispatch(User $user, string $type, string $channel, ?Appointment $appointment, array $data)
    {
        // 1. Prepare Log Entry
        $log = NotificationLog::create([
            'user_id' => $user->id,
            'appointment_id' => $appointment ? $appointment->id : null,
            'channel' => $channel,
            'type' => $type,
            'recipient' => $this->getRecipient($user, $channel),
            'status' => 'pending',
            'sent_at' => now(), // Assume attempt starts now
        ]);

        try {
            // 2. Send based on Channel
            if ($channel === 'email') {
                $this->sendEmail($user, $type, $appointment, $data);
            } elseif ($channel === 'push') {
                $this->sendPush($user, $type, $appointment, $data);
            } elseif ($channel === 'whatsapp') {
                $this->sendToWhatsApp($user, $data);
            }

            // 3. Mark Success
            $log->update(['status' => 'sent']);
            return 'sent';

        } catch (\Throwable $e) {
            // 4. Mark Failure
            Log::error("Notification Failed [{$channel}]: " . $e->getMessage());
            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
            return 'failed';
        }
    }

    protected function getRecipient(User $user, string $channel)
    {
        if ($channel === 'email') return $user->email ?? ($user->patient->email ?? 'unknown');
        if ($channel === 'push') return 'device_tokens';
        if ($channel === 'whatsapp') return $user->phone ?? 'no-phone';
        return 'unknown';
    }

    protected function sendEmail(User $user, string $type, ?Appointment $appointment, array $data)
    {
        $email = $user->email ?? $user->patient->email;
        if (!$email) throw new \Exception("User has no email");
        
        $mailable = null;

        if ($type === 'appointment_reminder') {
            $mailable = new \App\Mail\AppointmentReminder($appointment);
        } elseif ($type === 'appointment_confirmed') {
            $mailable = new \App\Mail\AppointmentConfirmation($appointment);
        } elseif ($type === 'manual_test') {
            // Envío manual directo sin clase Mailable
            Mail::raw($data['body'] ?? '', function ($message) use ($user, $data) {
                $message->to($user->email ?? $user->patient->email)
                    ->subject($data['title'] ?? 'Prueba de Sistema');
            });
            return;
        }

        if ($mailable) {
            Mail::to($email)->send($mailable);
        } else {
            throw new \Exception("Mailable not found for type: $type");
        }
    }

    protected function sendPush(User $user, string $type, ?Appointment $appointment, array $data)
    {
        $pushService = new \App\Services\PushNotificationService();
        
        $title = $data['title'] ?? 'Notificación';
        $body = $data['body'] ?? '';
        
        $payload = [
            'type' => $type,
            'appointment_id' => $appointment ? (string)$appointment->id : null
        ];

        $sent = $pushService->sendToUser($user->id, $title, $body, $payload);
        
        if (!$sent) throw new \Exception("No active tokens or Firebase error");
    }

    protected function sendToWhatsApp(User $user, array $data)
    {
        $phone = $user->phone;
        
        if (empty($phone)) throw new \Exception("Usuario sin teléfono");

        // Asegurar código de país (ej. Bolivia 591)
        // Limpiar caracteres no numéricos
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Si no empieza con 591 y es un número de 8 dígitos (celular boliviano), agregar 591
        // Ajusta esta lógica según tus necesidades reales
        if (strlen($phone) >= 8 && !str_starts_with($phone, '591')) {
            $phone = '591' . intval($phone); 
        }

        $response = Http::withHeaders([
            'x-webhook-secret' => env('WEBHOOK_SECRET', '')
        ])->post('http://localhost:3010/push-message', [
            'number' => $phone,
            'message' => ($data['title'] ?? '') . "\n\n" . ($data['body'] ?? '')
        ]);

        if (!$response->successful()) {
            throw new \Exception("Bot Error: " . $response->body());
        }

        return true;
    }
}
