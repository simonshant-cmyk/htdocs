<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventPublishedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string  $recipientName,
        public readonly string  $eventTitle,
        public readonly ?string $eventDate,
        public readonly ?string $venueName,
        public readonly int     $eventId,
        public readonly string  $orgName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Новое событие — ' . $this->eventTitle);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.event-published');
    }
}
