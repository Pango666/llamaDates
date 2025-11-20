<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LlamaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $data   // title, preheader, banner_url, image_url, text/html, details[], button_text, button_url, footer, legal_note, subject, brand
    ) {}

    public function build()
    {
        $subject = $this->data['subject'] ?? 'LlamaDates';

        return $this->subject($subject)
            ->view('emails.templates.llamadates-generic', $this->data)
            ->text('emails.templates.llamadates-generic_text', $this->data);
    }
}
