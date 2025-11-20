<?php

namespace App\Services\Mailer;

use App\Mail\LlamaMail;
use Illuminate\Support\Facades\Mail;

class LlamaMailer
{
    public function send(string|array $to, array $payload, array $attachments = []): void
    {
        $mailable = new LlamaMail($payload);

        // Adjuntos
        foreach ($attachments as $att) {
            if (is_array($att) && isset($att['data'])) {
                $mailable->attachData($att['data'], $att['name'] ?? 'file', ['mime' => $att['mime'] ?? 'application/octet-stream']);
            } else {
                $mailable->attach($att);
            }
        }

        Mail::to($to)->send($mailable);
    }
}
