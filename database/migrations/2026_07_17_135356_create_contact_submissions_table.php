<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_submissions', function (Blueprint $table): void {
            $table->id();
            $table->uuid('request_id')->unique();

            $table->string('name', 120);
            $table->string('phone', 32);
            $table->string('email');
            $table->text('comment');

            $table->string('sentiment', 32)->nullable();
            $table->decimal('sentiment_score', 5, 4)->nullable();
            $table->string('request_type', 64)->nullable();
            $table->text('auto_response')->nullable();

            $table->string('processing_status', 32)->default('pending');
            $table->string('ai_status', 32)->default('not_attempted');
            $table->string('owner_mail_status', 32)->default('not_attempted');
            $table->string('user_mail_status', 32)->default('not_attempted');

            $table->string('ai_provider', 64)->nullable();
            $table->string('ai_model', 120)->nullable();
            $table->unsignedInteger('ai_prompt_tokens')->nullable();
            $table->unsignedInteger('ai_completion_tokens')->nullable();
            $table->unsignedInteger('ai_total_tokens')->nullable();

            $table->char('ip_hash', 64)->nullable();
            $table->string('user_agent', 512)->nullable();

            $table->timestamp('ai_processed_at')->nullable();
            $table->timestamp('owner_mail_sent_at')->nullable();
            $table->timestamp('user_mail_sent_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index(['processing_status', 'created_at']);
            $table->index(['ai_status', 'created_at']);
            $table->index(['owner_mail_status', 'created_at']);
            $table->index(['user_mail_status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_submissions');
    }
};
