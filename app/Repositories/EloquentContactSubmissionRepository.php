<?php

namespace App\Repositories;

use App\Contracts\Repositories\ContactSubmissionRepository;
use App\Data\Ai\AiAnalysisResult;
use App\Data\ContactSubmissionData;
use App\Data\Mail\ContactMailResult;
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

    public function markAiProcessing(
        ContactSubmission $submission,
    ): ContactSubmission {
        $submission->update([
            'processing_status' => ProcessingStatus::Processing,
            'ai_status' => AiStatus::Pending,
        ]);

        return $submission->refresh();
    }

    public function saveAiResult(
        ContactSubmission $submission,
        AiAnalysisResult $result,
        AiStatus $status,
    ): ContactSubmission {
        $submission->update([
            'sentiment' => $result->sentiment,
            'sentiment_score' => $result->sentimentScore,
            'request_type' => $result->requestType,
            'auto_response' => $result->autoResponse,
            'ai_status' => $status,
            'ai_provider' => $result->provider,
            'ai_model' => $result->model,
            'ai_prompt_tokens' => $result->promptTokens,
            'ai_completion_tokens' => $result->completionTokens,
            'ai_total_tokens' => $result->totalTokens,
            'ai_processed_at' => now(),
        ]);

        return $submission->refresh();
    }

    public function markMailProcessing(
        ContactSubmission $submission,
    ): ContactSubmission {
        $submission->update([
            'owner_mail_status' => MailStatus::Pending,
            'user_mail_status' => MailStatus::Pending,
        ]);

        return $submission->refresh();
    }

    public function saveMailResult(
        ContactSubmission $submission,
        ContactMailResult $result,
    ): ContactSubmission {
        $completedAt = now();

        $submission->update([
            'processing_status' => $result->processingStatus(),
            'owner_mail_status' => $result->ownerStatus,
            'user_mail_status' => $result->userStatus,
            'owner_mail_sent_at' => $result->ownerStatus === MailStatus::Sent
                ? $completedAt
                : null,
            'user_mail_sent_at' => $result->userStatus === MailStatus::Sent
                ? $completedAt
                : null,
            'completed_at' => $completedAt,
        ]);

        return $submission->refresh();
    }
}
