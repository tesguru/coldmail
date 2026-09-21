<?php

namespace App\Console\Commands;

use App\Models\CampaignEmail;
use App\Services\ObanService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RetryFailedEmails extends Command
{
    protected $signature = 'emails:retry-failed';

    protected $description = 'Requeue failed campaign emails (only for active campaigns)';

    public function handle(): int
    {
        $maxAttempts = max(1, (int) config('coldmail.auto_retry_limit', 5));
        $batch       = 100;

        $failed = CampaignEmail::where('campaign_emails.status', 'failed')
            ->where('campaign_emails.send_attempts', '<', $maxAttempts)
            ->whereHas('campaign', fn ($q) => $q->where('status', 'active'))
            ->limit($batch)
            ->get();

        if ($failed->isEmpty()) {
            return self::SUCCESS;
        }

        $requeued = 0;

        foreach ($failed as $email) {
            $email->increment('send_attempts');
            $email->update(['status' => 'pending']);
            ObanService::insertEmailJob($email->id, rand(1, 3));
            $requeued++;
        }

        Log::info('Auto-retried failed emails', [
            'requeued'    => $requeued,
            'max_attempt' => $maxAttempts,
        ]);

        return self::SUCCESS;
    }
}