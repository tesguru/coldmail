<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaign_emails', function (Blueprint $table) {
            $table->unsignedInteger('send_attempts')->default(0)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('campaign_emails', function (Blueprint $table) {
            $table->dropColumn('send_attempts');
        });
    }
};