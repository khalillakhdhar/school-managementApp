<?php
namespace App\Mail;

use App\Models\Payment;
use App\Models\SchoolParent;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Payment $payment,
        public readonly SchoolParent $parent,
        public readonly int $daysOverdue,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Payment Reminder — :student', ['student' => $this->payment->student?->full_name ?? '']),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-reminder',
        );
    }
}
