<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrgStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string  $orgName,
        public readonly bool    $approved,
        public readonly ?string $rejectionReason = null,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->approved
            ? 'Ваша организация одобрена — АфишаКолыма'
            : 'Ваша организация отклонена — АфишаКолыма';
        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.org-status');
    }
}
