<?php

namespace App\Repositories;

use App\Contracts\Repositories\ContactSubmissionRepository;
use App\Data\ContactSubmissionData;
use App\Enums\AiStatus;
use App\Enums\MailStatus;
use App\Enums\ProcessingStatus;
use App\Models\ContactSubmission;

final class EloquentContactSubmissionRepository implements ContactSubmissionRepository
{
    public function create(
        ContactSubmissionData $data,
        ?string $ipHash,
        ?string $userAgent,
    ): ContactSubmission {
        return ContactSubmission::query()->create([
            ...$data->toArray(),
            'processing_status' => ProcessingStatus::Pending,
            'ai_status' => AiStatus::NotAttempted,
            'owner_mail_status' => MailStatus::NotAttempted,
            'user_mail_status' => MailStatus::NotAttempted,
            'ip_hash' => $ipHash,
            'user_agent' => $userAgent,
        ]);
    }
}
