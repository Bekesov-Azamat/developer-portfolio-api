<?php

namespace App\Services;

use App\Contracts\Mail\ContactMailSender;
use App\Data\Mail\ContactMailResult;
use App\Enums\MailStatus;
use App\Models\ContactSubmission;
use Illuminate\Support\Facades\Log;
use Throwable;

final readonly class ContactMailService
{
    public function __construct(
        private ContactMailSender $sender,
    ) {}

    public function send(
        ContactSubmission $submission,
    ): ContactMailResult {
        if (! (bool) config('contact.mail.enabled', false)) {
            return new ContactMailResult(
                ownerStatus: MailStatus::Skipped,
                userStatus: MailStatus::Skipped,
            );
        }

        return new ContactMailResult(
            ownerStatus: $this->sendOwner($submission),
            userStatus: $this->sendUser($submission),
        );
    }

    private function sendOwner(
        ContactSubmission $submission,
    ): MailStatus {
        $ownerAddress = trim(
            (string) config('contact.mail.owner_address'),
        );

        if (
            $ownerAddress === ''
            || filter_var(
                $ownerAddress,
                FILTER_VALIDATE_EMAIL,
            ) === false
        ) {
            Log::warning(
                'Owner contact email is not configured; notification skipped.',
                [
                    'request_id' => $submission->request_id,
                    'recipient_role' => 'owner',
                ],
            );

            return MailStatus::Skipped;
        }

        try {
            $this->sender->sendOwner(
                submission: $submission,
                ownerAddress: $ownerAddress,
            );

            return MailStatus::Sent;
        } catch (Throwable $exception) {
            $this->logFailure(
                submission: $submission,
                recipientRole: 'owner',
                exception: $exception,
            );

            return MailStatus::Failed;
        }
    }

    private function sendUser(
        ContactSubmission $submission,
    ): MailStatus {
        try {
            $this->sender->sendUser($submission);

            return MailStatus::Sent;
        } catch (Throwable $exception) {
            $this->logFailure(
                submission: $submission,
                recipientRole: 'user',
                exception: $exception,
            );

            return MailStatus::Failed;
        }
    }

    private function logFailure(
        ContactSubmission $submission,
        string $recipientRole,
        Throwable $exception,
    ): void {
        Log::warning(
            'Contact email delivery failed.',
            [
                'request_id' => $submission->request_id,
                'recipient_role' => $recipientRole,
                'exception' => $exception::class,
            ],
        );
    }
}
