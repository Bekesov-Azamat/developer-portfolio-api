<?php

namespace Tests\Unit\Services;

use App\Contracts\Mail\ContactMailSender;
use App\Enums\MailStatus;
use App\Models\ContactSubmission;
use App\Services\ContactMailService;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class ContactMailServiceTest extends TestCase
{
    public function test_it_sends_owner_and_user_emails(): void
    {
        config()->set('contact.mail.enabled', true);
        config()->set(
            'contact.mail.owner_address',
            'owner@example.com',
        );

        $submission = ContactSubmission::factory()->make();

        $sender = Mockery::mock(ContactMailSender::class);

        $sender
            ->shouldReceive('sendOwner')
            ->once()
            ->with($submission, 'owner@example.com');

        $sender
            ->shouldReceive('sendUser')
            ->once()
            ->with($submission);

        $result = (new ContactMailService($sender))
            ->send($submission);

        $this->assertSame(
            MailStatus::Sent,
            $result->ownerStatus,
        );
        $this->assertSame(
            MailStatus::Sent,
            $result->userStatus,
        );
    }

    public function test_owner_failure_does_not_block_user_email(): void
    {
        config()->set('contact.mail.enabled', true);
        config()->set(
            'contact.mail.owner_address',
            'owner@example.com',
        );

        $submission = ContactSubmission::factory()->make();

        $sender = Mockery::mock(ContactMailSender::class);

        $sender
            ->shouldReceive('sendOwner')
            ->once()
            ->with($submission, 'owner@example.com')
            ->andThrow(
                new RuntimeException(
                    'Simulated owner mail failure.',
                ),
            );

        $sender
            ->shouldReceive('sendUser')
            ->once()
            ->with($submission);

        $result = (new ContactMailService($sender))
            ->send($submission);

        $this->assertSame(
            MailStatus::Failed,
            $result->ownerStatus,
        );
        $this->assertSame(
            MailStatus::Sent,
            $result->userStatus,
        );
    }

    public function test_user_failure_preserves_successful_owner_delivery(): void
    {
        config()->set('contact.mail.enabled', true);
        config()->set(
            'contact.mail.owner_address',
            'owner@example.com',
        );

        $submission = ContactSubmission::factory()->make();

        $sender = Mockery::mock(ContactMailSender::class);

        $sender
            ->shouldReceive('sendOwner')
            ->once()
            ->with($submission, 'owner@example.com');

        $sender
            ->shouldReceive('sendUser')
            ->once()
            ->with($submission)
            ->andThrow(
                new RuntimeException(
                    'Simulated user mail failure.',
                ),
            );

        $result = (new ContactMailService($sender))
            ->send($submission);

        $this->assertSame(
            MailStatus::Sent,
            $result->ownerStatus,
        );
        $this->assertSame(
            MailStatus::Failed,
            $result->userStatus,
        );
    }

    public function test_invalid_owner_address_skips_owner_and_still_sends_user(): void
    {
        config()->set('contact.mail.enabled', true);
        config()->set(
            'contact.mail.owner_address',
            'invalid-owner-address',
        );

        $submission = ContactSubmission::factory()->make();

        $sender = Mockery::mock(ContactMailSender::class);

        $sender->shouldNotReceive('sendOwner');

        $sender
            ->shouldReceive('sendUser')
            ->once()
            ->with($submission);

        $result = (new ContactMailService($sender))
            ->send($submission);

        $this->assertSame(
            MailStatus::Skipped,
            $result->ownerStatus,
        );
        $this->assertSame(
            MailStatus::Sent,
            $result->userStatus,
        );
    }

    public function test_disabled_mail_skips_both_messages(): void
    {
        config()->set('contact.mail.enabled', false);

        $submission = ContactSubmission::factory()->make();

        $sender = Mockery::mock(ContactMailSender::class);

        $sender->shouldNotReceive('sendOwner');
        $sender->shouldNotReceive('sendUser');

        $result = (new ContactMailService($sender))
            ->send($submission);

        $this->assertSame(
            MailStatus::Skipped,
            $result->ownerStatus,
        );
        $this->assertSame(
            MailStatus::Skipped,
            $result->userStatus,
        );
    }
}
