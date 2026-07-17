<?php

namespace App\Contracts\Repositories;

use App\Data\ContactSubmissionData;
use App\Models\ContactSubmission;

interface ContactSubmissionRepository
{
    public function create(
        ContactSubmissionData $data,
        ?string $ipHash,
        ?string $userAgent,
    ): ContactSubmission;
}
