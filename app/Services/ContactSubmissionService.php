<?php

namespace App\Services;

use App\Contracts\Repositories\ContactSubmissionRepository;
use App\Data\ContactSubmissionData;
use App\Models\ContactSubmission;
use Illuminate\Support\Str;

final readonly class ContactSubmissionService
{
    public function __construct(
        private ContactSubmissionRepository $repository,
    ) {}

    public function submit(
        ContactSubmissionData $data,
        ?string $ipAddress,
        ?string $userAgent,
    ): ContactSubmission {
        return $this->repository->create(
            data: $data,
            ipHash: $this->hashIpAddress($ipAddress),
            userAgent: $this->sanitizeUserAgent($userAgent),
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
