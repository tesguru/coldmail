<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\CampaignEmail;
use App\Models\GmailAccount;
use App\Services\AppScriptService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $userId    = Auth::id();
        $appScript = new AppScriptService();

        $accounts = GmailAccount::where('user_id', $userId)->get();

        $accountStats = $accounts->map(function ($account) use ($appScript) {
            $stats = $account->script_url
                ? $appScript->getStats($account->script_url)
                : [];

            return [
                'account'    => $account,
                'sent_today' => $stats['sent_today'] ?? $account->sent_today,
                'total_sent' => $stats['total_sent'] ?? $account->total_sent,
                'remaining'  => $stats['remaining']  ?? $account->remainingToday(),
                'limit'      => $stats['limit']       ?? $account->daily_limit,
            ];
        });

        $stats = [
            'total_sent_today' => $accountStats->sum('sent_today'),
            'total_sent_all'   => $accountStats->sum('total_sent'),
            'total_accounts'   => $accounts->count(),
            'total_campaigns'  => Campaign::where('user_id', $userId)->count(),
            'total_replies'    => CampaignEmail::where('user_id', $userId)->where('has_reply', true)->count(),
            'total_bounces'    => CampaignEmail::where('user_id', $userId)->where('is_bounced', true)->count(),
            'total_pending'    => CampaignEmail::where('user_id', $userId)->where('status', 'pending')->count(),
        ];

        $campaigns = Campaign::where('user_id', $userId)->latest()->take(5)->get();

        return view('dashboard.index', compact('stats', 'accountStats', 'campaigns'));
    }
}