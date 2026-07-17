<?php

namespace App\Enums;

enum AiStatus: string
{
    case NotAttempted = 'not_attempted';
    case Pending = 'pending';
    case Succeeded = 'succeeded';
    case Fallback = 'fallback';
    case Failed = 'failed';
}
