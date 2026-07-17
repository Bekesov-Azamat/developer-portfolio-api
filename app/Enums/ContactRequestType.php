<?php

namespace App\Enums;

enum ContactRequestType: string
{
    case ProjectInquiry = 'project_inquiry';
    case JobOpportunity = 'job_opportunity';
    case Consultation = 'consultation';
    case Collaboration = 'collaboration';
    case Feedback = 'feedback';
    case Other = 'other';
}
