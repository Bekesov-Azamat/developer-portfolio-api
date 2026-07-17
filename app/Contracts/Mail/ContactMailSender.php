<?php

namespace App\Contracts\Mail;

use App\Models\ContactSubmission;

interface ContactMailSender
{
    public function sendOwner(
        ContactSubmission $submission,
        string $ownerAddress,
    ): void;

    public function sendUser(
        ContactSubmission $submission,
    ): void;
}
