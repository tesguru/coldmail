<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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