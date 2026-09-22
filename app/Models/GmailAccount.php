<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class GmailAccount extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'avatar',
        'google_token',
        'google_refresh_token',
        'script_url',
        'sent_today',
        'total_sent',
        'daily_limit',
        'last_reset_date',
        'is_active',
    ];

    protected $casts = [
        'is_active'       => 'boolean',
        'last_reset_date' => 'date',
    ];

    // ============================================================
    // RELATIONSHIPS
    // ============================================================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function campaigns()
    {
        return $this->belongsToMany(
            Campaign::class,
            'campaign_gmail_accounts'
        )->withPivot('allocated_count')->withTimestamps();
    }

    public function campaignEmails()
    {
        return $this->hasMany(CampaignEmail::class);
    }

    // ============================================================
    // DAILY LIMIT
    // ============================================================

    public function hasReachedDailyLimit(): bool
    {
        $this->resetDailyCountIfNeeded();
        return $this->sent_today >= $this->daily_limit;
    }

    public function incrementSent(): void
    {
        $this->resetDailyCountIfNeeded();
        $this->increment('sent_today');
        $this->increment('total_sent');
    }

    public function remainingToday(): int
    {
        $this->resetDailyCountIfNeeded();
        return max(0, $this->daily_limit - $this->sent_today);
    }

    // Rolling window quota — mirrors Google's real reset behaviour.
    // The anchor is the account's LAST send time: no emails may be sent again
    // until `rolling_window_hours` (default 2 days) have elapsed since then.
    //
    // If no send ever happened, the account is available immediately.
    public function lastSentAt(): ?Carbon
    {
        $value = $this->campaignEmails()
            ->whereNotNull('sent_at')
            ->max('sent_at');

        return $value ? Carbon::parse($value) : null;
    }

    public function rollingWindowHours(): int
    {
        return max(1, (int) config('coldmail.rolling_window_hours', 48));
    }

    public function nextAvailableAt(): Carbon
    {
        $last = $this->lastSentAt();

        if (!$last) {
            return now();
        }

        $days = max(1, (int) ceil($this->rollingWindowHours() / 24));
        $hour = (int) config('coldmail.rolling_window_fixed_hour', 0);

        return $last->copy()
            ->addDays($days)
            ->startOfDay()
            ->addHours($hour);
    }

    public function canSendNow(): bool
    {
        return now()->gte($this->nextAvailableAt());
    }

    // Emails sent inside the current rolling window (how many of the last N hours).
    public function sentInWindow(): int
    {
        return $this->campaignEmails()
            ->whereNotNull('sent_at')
            ->where('sent_at', '>=', now()->subHours($this->rollingWindowHours()))
            ->count();
    }

    protected function resetDailyCountIfNeeded(): void
    {
        $today = now()->toDateString();
        if ($this->last_reset_date?->toDateString() !== $today) {
            $this->update([
                'sent_today'      => 0,
                'last_reset_date' => $today,
            ]);
        }
    }
}