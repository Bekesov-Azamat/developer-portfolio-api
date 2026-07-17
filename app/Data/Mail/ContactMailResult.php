<?php

namespace App\Data\Mail;

use App\Enums\MailStatus;
use App\Enums\ProcessingStatus;

final readonly class ContactMailResult
{
    public function __construct(
        public MailStatus $ownerStatus,
        public MailStatus $userStatus,
    ) {}

    public function processingStatus(): ProcessingStatus
    {
        if (
            $this->ownerStatus === MailStatus::Sent
            && $this->userStatus === MailStatus::Sent
        ) {
            return ProcessingStatus::Completed;
        }

        return ProcessingStatus::PartiallyCompleted;
    }
}
