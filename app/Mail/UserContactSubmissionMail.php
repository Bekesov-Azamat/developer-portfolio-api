<?php

namespace App\Mail;

use App\Models\ContactSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class UserContactSubmissionMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public ContactSubmission $submission,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Ваше обращение получено',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.contact.user',
        );
    }
}
