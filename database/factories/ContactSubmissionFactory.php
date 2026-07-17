<?php

namespace Database\Factories;

use App\Enums\AiStatus;
use App\Enums\MailStatus;
use App\Enums\ProcessingStatus;
use App\Models\ContactSubmission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactSubmission>
 */
class ContactSubmissionFactory extends Factory
{
    protected $model = ContactSubmission::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => fake()->numerify('+7##########'),
            'email' => fake()->safeEmail(),
            'comment' => fake()->paragraph(),
            'processing_status' => ProcessingStatus::Pending,
            'ai_status' => AiStatus::NotAttempted,
            'owner_mail_status' => MailStatus::NotAttempted,
            'user_mail_status' => MailStatus::NotAttempted,
            'ip_hash' => hash('sha256', fake()->ipv4()),
            'user_agent' => fake()->userAgent(),
        ];
    }
}
