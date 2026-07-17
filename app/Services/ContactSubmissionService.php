<?php

namespace App\Services;

use App\Contracts\Ai\AiAnalyzer;
use App\Contracts\Repositories\ContactSubmissionRepository;
use App\Data\Ai\AiAnalysisResult;
use App\Data\ContactSubmissionData;
use App\Enums\AiStatus;
use App\Exceptions\AiAnalysisException;
use App\Models\ContactSubmission;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final readonly class ContactSubmissionService
{
    public function __construct(
        private ContactSubmissionRepository $repository,
        private AiAnalyzer $aiAnalyzer,
    ) {}

    public function submit(
        ContactSubmissionData $data,
        ?string $ipAddress,
        ?string $userAgent,
    ): ContactSubmission {
        $submission = $this->repository->create(
            data: $data,
            ipHash: $this->hashIpAddress($ipAddress),
            userAgent: $this->sanitizeUserAgent($userAgent),
        );

        $submission = $this->repository->markAiProcessing(
            $submission,
        );

        if (! (bool) config('ai.enabled', false)) {
            return $this->saveFallback($submission);
        }

        try {
            $result = $this->aiAnalyzer->analyze(
                $submission->comment,
            );

            return $this->repository->saveAiResult(
                submission: $submission,
                result: $result,
                status: AiStatus::Succeeded,
            );
        } catch (AiAnalysisException $exception) {
            Log::warning(
                'AI analysis failed; fallback response used.',
                [
                    'request_id' => $submission->request_id,
                    'provider' => (string) config(
                        'ai.provider',
                        'groq',
                    ),
                    'model' => (string) config(
                        'ai.providers.groq.model',
                        'openai/gpt-oss-120b',
                    ),
                    'exception' => $exception::class,
                ],
            );

            return $this->saveFallback($submission);
        }
    }

    private function saveFallback(
        ContactSubmission $submission,
    ): ContactSubmission {
        $result = AiAnalysisResult::fallback(
            autoResponse: (string) config(
                'ai.fallback_response',
            ),
            provider: (string) config('ai.provider', 'groq'),
            model: (string) config(
                'ai.providers.groq.model',
                'openai/gpt-oss-120b',
            ),
        );

        return $this->repository->saveAiResult(
            submission: $submission,
            result: $result,
            status: AiStatus::Fallback,
        );
    }

    private function hashIpAddress(?string $ipAddress): ?string
    {
        if ($ipAddress === null || $ipAddress === '') {
            return null;
        }

        return hash_hmac(
            'sha256',
            $ipAddress,
            (string) config('app.key'),
        );
    }

    private function sanitizeUserAgent(?string $userAgent): ?string
    {
        if ($userAgent === null || trim($userAgent) === '') {
            return null;
        }

        return Str::limit(trim($userAgent), 512, '');
    }
}
