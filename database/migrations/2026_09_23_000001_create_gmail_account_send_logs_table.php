<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gmail_account_send_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('gmail_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('campaign_email_id')->nullable();
            $table->string('to_email')->nullable();
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->index(['gmail_account_id', 'sent_at']);
        });

        DB::statement(
            "INSERT INTO gmail_account_send_logs
                (user_id, gmail_account_id, campaign_id, campaign_email_id, to_email, sent_at, created_at, updated_at)
             SELECT user_id, gmail_account_id, campaign_id, id, to_email, sent_at, sent_at, sent_at
             FROM campaign_emails
             WHERE sent_at IS NOT NULL"
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('gmail_account_send_logs');
    }
};