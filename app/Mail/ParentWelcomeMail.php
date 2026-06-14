<?php
namespace App\Mail;

use App\Models\SchoolParent;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ParentWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly SchoolParent $parent,
        public readonly string $temporaryPassword,
        public readonly string $loginUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Your School Portal Access'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.parent-welcome',
        );
    }
}
