<?php

namespace Tests\Unit\Mail;

use App\Enums\AiStatus;
use App\Enums\ContactRequestType;
use App\Enums\Sentiment;
use App\Mail\OwnerContactSubmissionMail;
use App\Mail\UserContactSubmissionMail;
use App\Models\ContactSubmission;
use Illuminate\Support\Str;
use Tests\TestCase;

class ContactMailablesTest extends TestCase
{
    public function test_owner_mail_contains_submission_and_reply_to_user(): void
    {
        $submission = $this->submission();

        $mail = new OwnerContactSubmissionMail($submission);

        $mail->assertHasSubject(
            "Новое обращение с портфолио — {$submission->request_id}",
        );
        $mail->assertHasReplyTo(
            'client@example.com',
            'Иван Петров',
        );
        $mail->assertSeeInHtml(
            'Нужна разработка Laravel API.',
        );
        $mail->assertSeeInHtml(
            'project_inquiry',
        );
        $mail->assertSeeInText(
            'client@example.com',
        );
        $mail->assertSeeInText(
            'Здравствуйте! Спасибо за обращение.',
        );
    }

    public function test_user_mail_contains_auto_response_and_submission_copy(): void
    {
        $submission = $this->submission();

        $mail = new UserContactSubmissionMail($submission);

        $mail->assertHasSubject(
            'Ваше обращение получено',
        );
        $mail->assertSeeInHtml(
            'Здравствуйте! Спасибо за обращение.',
        );
        $mail->assertSeeInHtml(
            'Нужна разработка Laravel API.',
        );
        $mail->assertSeeInText(
            $submission->request_id,
        );
        $mail->assertSeeInText(
            '+7 700 123 45 67',
        );
        $mail->assertDontSeeInHtml(
            'openai/gpt-oss-120b',
        );
    }

    private function submission(): ContactSubmission
    {
        return ContactSubmission::factory()->make([
            'request_id' => Str::uuid()->toString(),
            'name' => 'Иван Петров',
            'phone' => '+7 700 123 45 67',
            'email' => 'client@example.com',
            'comment' => 'Нужна разработка Laravel API.',
            'sentiment' => Sentiment::Positive,
            'sentiment_score' => 0.6,
            'request_type' => ContactRequestType::ProjectInquiry,
            'auto_response' => 'Здравствуйте! Спасибо за обращение.',
            'ai_status' => AiStatus::Succeeded,
            'ai_provider' => 'groq',
            'ai_model' => 'openai/gpt-oss-120b',
            'created_at' => now(),
        ]);
    }
}
