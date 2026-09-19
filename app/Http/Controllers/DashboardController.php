<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\GmailAccount;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $accounts = GmailAccount::where('user_id', $userId)->get();

        $accountStats = $accounts->map(fn($account) => [
            'account'    => $account,
            'sent_today' => $account->sent_today,
            'total_sent' => $account->total_sent,
            'remaining'  => $account->remainingToday(),
            'limit'      => $account->daily_limit,
        ]);

        $campaignCounts = Campaign::where('user_id', $userId)
            ->selectRaw('COUNT(*) as total_campaigns')
            ->selectRaw('COALESCE(SUM(replied_count), 0) as total_replies')
            ->selectRaw('COALESCE(SUM(bounce_count), 0) as total_bounces')
            ->first();

        $stats = [
            'total_sent_today' => $accountStats->sum('sent_today'),
            'total_sent_all'   => $accountStats->sum('total_sent'),
            'total_accounts'   => $accounts->count(),
            'total_campaigns'  => (int) $campaignCounts->total_campaigns,
            'total_replies'    => (int) $campaignCounts->total_replies,
            'total_bounces'    => (int) $campaignCounts->total_bounces,
        ];

        $campaigns = Campaign::where('user_id', $userId)->latest()->take(5)->get();

        return view('dashboard.index', compact('stats', 'accountStats', 'campaigns'));
    }
}