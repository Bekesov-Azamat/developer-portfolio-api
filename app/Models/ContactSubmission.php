<?php

namespace App\Models;

use App\Enums\AiStatus;
use App\Enums\ContactRequestType;
use App\Enums\MailStatus;
use App\Enums\ProcessingStatus;
use App\Enums\Sentiment;
use Database\Factories\ContactSubmissionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property Sentiment|null $sentiment
 * @property ContactRequestType|null $request_type
 * @property ProcessingStatus $processing_status
 * @property AiStatus $ai_status
 * @property MailStatus $owner_mail_status
 * @property MailStatus $user_mail_status
 */
class ContactSubmission extends Model
{
    /** @use HasFactory<ContactSubmissionFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'phone',
        'email',
        'comment',
        'sentiment',
        'sentiment_score',
        'request_type',
        'auto_response',
        'processing_status',
        'ai_status',
        'owner_mail_status',
        'user_mail_status',
        'ai_provider',
        'ai_model',
        'ai_prompt_tokens',
        'ai_completion_tokens',
        'ai_total_tokens',
        'ip_hash',
        'user_agent',
        'ai_processed_at',
        'owner_mail_sent_at',
        'user_mail_sent_at',
        'completed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sentiment' => Sentiment::class,
            'sentiment_score' => 'decimal:4',
            'request_type' => ContactRequestType::class,
            'processing_status' => ProcessingStatus::class,
            'ai_status' => AiStatus::class,
            'owner_mail_status' => MailStatus::class,
            'user_mail_status' => MailStatus::class,
            'ai_prompt_tokens' => 'integer',
            'ai_completion_tokens' => 'integer',
            'ai_total_tokens' => 'integer',
            'ai_processed_at' => 'immutable_datetime',
            'owner_mail_sent_at' => 'immutable_datetime',
            'user_mail_sent_at' => 'immutable_datetime',
            'completed_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(static function (self $submission): void {
            $submission->request_id ??= (string) Str::uuid();
        });
    }
}
