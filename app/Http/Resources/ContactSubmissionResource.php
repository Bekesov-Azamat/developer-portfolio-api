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
            'submitted_at' => $this->created_at?->toISOString(),
        ];
    }
}
