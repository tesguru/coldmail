<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_gmail_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('gmail_account_id')->constrained()->cascadeOnDelete();
            $table->integer('allocated_count')->default(0); // how many emails assigned to this account
            $table->timestamps();

            $table->unique(['campaign_id', 'gmail_account_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_gmail_accounts');
    }
};