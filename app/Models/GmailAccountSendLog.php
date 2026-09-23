<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GmailAccountSendLog extends Model
{
    protected $fillable = [
        'user_id',
        'gmail_account_id',
        'campaign_id',
        'campaign_email_id',
        'to_email',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function gmailAccount()
    {
        return $this->belongsTo(GmailAccount::class);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
}