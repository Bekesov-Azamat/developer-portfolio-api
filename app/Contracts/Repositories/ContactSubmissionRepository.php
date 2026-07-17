<?php

namespace App\Contracts\Repositories;

use App\Data\Ai\AiAnalysisResult;
use App\Data\ContactSubmissionData;
use App\Enums\AiStatus;
use App\Models\ContactSubmission;

interface ContactSubmissionRepository
{
    public function create(
        ContactSubmissionData $data,
        ?string $ipHash,
        ?string $userAgent,
    ): ContactSubmission;

    public function markAiProcessing(
        ContactSubmission $submission,
    ): ContactSubmission;

    public function saveAiResult(
        ContactSubmission $submission,
        AiAnalysisResult $result,
        AiStatus $status,
    ): ContactSubmission;
}
