<?php

namespace App\Http\Resources;

use App\Models\ContactSubmission;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ContactSubmission
 */
class ContactSubmissionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'request_id' => $this->request_id,
            'status' => $this->processing_status->value,
            'ai_status' => $this->ai_status->value,
            'analysis' => [
                'sentiment' => $this->sentiment?->value,
                'sentiment_score' => $this->sentiment_score !== null
                    ? (float) $this->sentiment_score
                    : null,
                'request_type' => $this->request_type?->value,
            ],
            'auto_response' => $this->auto_response,
            'submitted_at' => $this->created_at?->toISOString(),
        ];
    }
}
