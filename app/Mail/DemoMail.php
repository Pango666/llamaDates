<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DemoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $nombre) {}

    public function build()
    {
        return $this->subject('Prueba LlamaDates')
            ->view('emails.demo')
            ->with(['nombre' => $this->nombre]);
    }
}
