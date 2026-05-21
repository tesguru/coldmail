<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'domain',
        'price',
        'your_name',
        'label_name',
        'gmail_label_id',
        'status',
        'total_emails',
        'sent_count',
        'replied_count',
        'follow_up_count',
        'bounce_count',
    ];

    // ============================================================
    // RELATIONSHIPS
    // ============================================================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function emails()
    {
        return $this->hasMany(CampaignEmail::class);
    }

    public function gmailAccounts()
    {
        return $this->belongsToMany(
            GmailAccount::class,
            'campaign_gmail_accounts'
        )->withPivot('allocated_count')->withTimestamps();
    }

    // ============================================================
    // REFRESH STATS
    // ============================================================

    public function refreshStats(): void
    {
        $this->update([
            'sent_count'     => $this->emails()->where('status', 'sent')->count(),
            'replied_count'  => $this->emails()->where('has_reply', true)->count(),
            'bounce_count'   => $this->emails()->where('is_bounced', true)->count(),
            'follow_up_count'=> $this->emails()->where('follow_up_count', '>', 0)->count(),
        ]);
    }
}