<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $appointment;
    public $daysUntil;

    /**
     * Create a new message instance.
     */
    public function __construct(Appointment $appointment, int $daysUntil)
    {
        $this->appointment = $appointment;
        $this->daysUntil = $daysUntil;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->daysUntil === 3 
            ? 'Recordatorio: Su cita en 3 días' 
            : 'Recordatorio: Su cita mañana';

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'view.name',
        );
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

    public function build()
    {
        return $this->subject($this->envelope()->subject)
                   ->markdown('emails.appointment-reminder');
    }
}
