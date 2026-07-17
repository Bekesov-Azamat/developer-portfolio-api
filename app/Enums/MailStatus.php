<?php

namespace App\Enums;

enum MailStatus: string
{
    case NotAttempted = 'not_attempted';
    case Pending = 'pending';
    case Sent = 'sent';
    case Failed = 'failed';
    case Skipped = 'skipped';
}
