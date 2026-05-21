<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('gmail_account_id')->constrained()->cascadeOnDelete();

            // Prospect info
            $table->string('to_email');
            $table->string('from_email');
            $table->string('first_name')->nullable();
            $table->string('company_name')->nullable();

            // Email content
            $table->string('subject');
            $table->longText('body');

            // Gmail tracking
            $table->string('gmail_message_id')->nullable();
            $table->string('gmail_thread_id')->nullable();
            $table->string('gmail_label_id')->nullable();

            // Status
            $table->string('status')->default('pending'); // pending, sent, failed, bounced, replied
            $table->boolean('has_reply')->default(false);
            $table->boolean('is_bounced')->default(false);
            $table->timestamp('replied_at')->nullable();

            // Template tracking
            $table->integer('follow_up_count')->default(0);
            $table->string('template_type')->nullable();
            $table->unsignedBigInteger('template_number')->nullable();

            // Timestamps
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_emails');
    }
};