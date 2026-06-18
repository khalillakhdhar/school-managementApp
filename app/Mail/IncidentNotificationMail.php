<?php
namespace App\Mail;

use App\Models\Incident;
use App\Models\SchoolParent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class IncidentNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Incident $incident,
        public readonly SchoolParent $parent,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Incident Report — :student', ['student' => $this->incident->student?->full_name ?? '']),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.incident-notification',
        );
    }
}
