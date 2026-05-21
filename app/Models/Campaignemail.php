<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignEmail extends Model
{
    protected $fillable = [
        'campaign_id',
        'user_id',
        'gmail_account_id',
        'to_email',
        'from_email',
        'first_name',
        'company_name',
        'subject',
        'body',
        'gmail_message_id',
        'gmail_thread_id',
        'gmail_label_id',
        'status',
        'has_reply',
        'is_bounced',
        'replied_at',
        'follow_up_count',
        'template_type',
        'template_number',
        'sent_at',
    ];

    protected $casts = [
        'has_reply'  => 'boolean',
        'is_bounced' => 'boolean',
        'sent_at'    => 'datetime',
        'replied_at' => 'datetime',
    ];

    // ============================================================
    // RELATIONSHIPS
    // ============================================================

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function gmailAccount()
    {
        return $this->belongsTo(GmailAccount::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ============================================================
    // HELPERS
    // ============================================================

    public function markAsReplied(): void
    {
        $this->update([
            'has_reply'  => true,
            'status'     => 'replied',
            'replied_at' => now(),
        ]);
    }

    public function markAsBounced(): void
    {
        $this->update([
            'is_bounced' => true,
            'status'     => 'bounced',
        ]);
    }

    public function incrementFollowUp(): void
    {
        $this->increment('follow_up_count');
        $this->update(['sent_at' => now()]);
    }

    public function remainingToday(): int
    {
        return max(0, $this->gmailAccount->daily_limit - $this->gmailAccount->sent_today);
    }
}