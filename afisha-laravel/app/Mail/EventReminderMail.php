<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $recipientName,
        public readonly array  $tickets,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '🎫 Напоминание о событии завтра');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.event-reminder');
    }
}
