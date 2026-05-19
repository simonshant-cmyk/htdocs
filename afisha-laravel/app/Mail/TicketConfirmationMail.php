<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $recipientName,
        public readonly array  $tickets,
        public readonly string $totalFormatted,
        public readonly string $paymentMethod,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Билеты куплены — АфишаКолыма');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.ticket-confirmation');
    }
}
