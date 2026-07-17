<?php

namespace App\Mail;

use App\Models\ContactSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class OwnerContactSubmissionMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public ContactSubmission $submission,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [
                new Address(
                    $this->submission->email,
                    $this->submission->name,
                ),
            ],
            subject: sprintf(
                'Новое обращение с портфолио — %s',
                $this->submission->request_id,
            ),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.contact.owner',
        );
    }
}
