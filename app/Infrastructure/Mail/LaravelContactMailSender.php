<?php

namespace App\Infrastructure\Mail;

use App\Contracts\Mail\ContactMailSender;
use App\Mail\OwnerContactSubmissionMail;
use App\Mail\UserContactSubmissionMail;
use App\Models\ContactSubmission;
use Illuminate\Support\Facades\Mail;

final class LaravelContactMailSender implements ContactMailSender
{
    public function sendOwner(
        ContactSubmission $submission,
        string $ownerAddress,
    ): void {
        Mail::to($ownerAddress)->send(
            new OwnerContactSubmissionMail($submission),
        );
    }

    public function sendUser(
        ContactSubmission $submission,
    ): void {
        Mail::to($submission->email)->send(
            new UserContactSubmissionMail($submission),
        );
    }
}
