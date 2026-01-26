<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $appointment;
    public $url;

    public function __construct($appointment)
    {
        $this->appointment = $appointment;
        $this->url = \Illuminate\Support\Facades\URL::signedRoute(
            'appointments.confirm_email',
            ['appointment' => $appointment->id],
            now()->addHours(24) // Expira en 24 horas
        );
    }

    public function build()
    {
        return $this->subject('Confirmación de Cita - DentalCare')
                    ->view('emails.appointment_confirmation');
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
