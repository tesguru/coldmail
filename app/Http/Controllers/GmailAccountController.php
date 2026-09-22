<?php

namespace App\Http\Controllers;

use App\Models\GmailAccount;
use App\Services\AppScriptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GmailAccountController extends Controller
{
    // ============================================================
    // GET ALL ACCOUNTS
    // ============================================================
    public function index()
    {
        // Campaigns each account is actively working on (name + planned/pending volumes)
        $activeCampaigns = DB::table('campaign_gmail_accounts as cga')
            ->join('campaigns as c', 'c.id', '=', 'cga.campaign_id')
            ->where('c.user_id', Auth::id())
            ->whereIn('c.status', ['active', 'paused'])
            ->select('cga.gmail_account_id', 'c.name', 'cga.allocated_count')
            ->get()
            ->groupBy('gmail_account_id');

        $pendingCounts = DB::table('campaign_emails as ce')
            ->join('campaigns as c', 'c.id', '=', 'ce.campaign_id')
            ->where('c.user_id', Auth::id())
            ->whereIn('c.status', ['active', 'paused'])
            ->where('ce.status', 'pending')
            ->select('ce.gmail_account_id', DB::raw('COUNT(*) as total'))
            ->groupBy('ce.gmail_account_id')
            ->pluck('total', 'gmail_account_id');

        $accounts = GmailAccount::where('user_id', Auth::id())
            ->get()
            ->map(function ($account) use ($activeCampaigns, $pendingCounts) {
                $working = collect($activeCampaigns->get($account->id, []))
                    ->map(fn ($row) => [
                        'name'      => $row->name,
                        'allocated' => (int) $row->allocated_count,
                        'pending'   => (int) ($pendingCounts[$account->id] ?? 0),
                    ])
                    ->values();

                $lastSent = $account->lastSentAt();

                return [
                    'id'               => $account->id,
                    'name'             => $account->name,
                    'email'            => $account->email,
                    'avatar'           => $account->avatar,
                    'script_url'       => $account->script_url,
                    'sent_today'       => $account->sent_today,
                    'total_sent'       => $account->total_sent,
                    'daily_limit'      => $account->daily_limit,
                    'remaining'        => $account->remainingToday(),
                    'is_active'        => $account->is_active,
                    'in_use'           => $working->isNotEmpty(),
                    'campaigns'        => $working->all(),
                    'token_status'     => $account->google_token ? 'valid' : 'missing',
                    'has_script'       => !empty($account->script_url),
                    'last_sent_at'     => $lastSent?->toIso8601String(),
                    'next_available'   => $lastSent ? $account->nextAvailableAt()->toIso8601String() : now()->toIso8601String(),
                    'can_send'         => $lastSent === null ? true : $account->canSendNow(),
                    'window_hours'     => $account->rollingWindowHours(),
                    'sent_in_window'   => $account->sentInWindow(),
                ];
            });

        return response()->json([
            'success'  => true,
            'accounts' => $accounts,
        ]);
    }

    // ============================================================
    // UPDATE SCRIPT URL
    // ============================================================
    public function updateScript(Request $request, $id)
    {
        $account = GmailAccount::where('user_id', Auth::id())->findOrFail($id);

        $request->validate(['script_url' => 'required|url']);

        $account->update(['script_url' => $request->script_url]);

        return response()->json([
            'success' => true,
            'message' => 'Apps Script URL saved!',
        ]);
    }

    // ============================================================
    // UPDATE DAILY LIMIT
    // ============================================================
    public function updateLimit(Request $request, $id)
    {
        $account = GmailAccount::where('user_id', Auth::id())->findOrFail($id);

        $request->validate(['daily_limit' => 'required|integer|min:1|max:100']);

        $account->update(['daily_limit' => $request->daily_limit]);

        return response()->json([
            'success' => true,
            'message' => 'Daily limit updated!',
        ]);
    }

    // ============================================================
    // TOGGLE ACTIVE
    // ============================================================
    public function toggleActive($id)
    {
        $account = GmailAccount::where('user_id', Auth::id())->findOrFail($id);
        $account->update(['is_active' => !$account->is_active]);

        return response()->json([
            'success'   => true,
            'is_active' => $account->is_active,
            'message'   => $account->is_active ? 'Account activated.' : 'Account deactivated.',
        ]);
    }

    // ============================================================
    // TEST APPS SCRIPT CONNECTION
    // ============================================================
    public function test($id)
    {
        $account = GmailAccount::where('user_id', Auth::id())->findOrFail($id);

        if (!$account->script_url) {
            return response()->json([
                'success' => false,
                'message' => 'No Apps Script URL configured.',
            ]);
        }

        $online = (new AppScriptService())->testConnection($account->script_url);

        return response()->json([
            'success' => $online,
            'message' => $online ? '✅ Connected!' : '❌ Could not reach script.',
        ]);
    }

    // ============================================================
    // DELETE ACCOUNT
    // ============================================================
    public function destroy($id)
    {
        $account = GmailAccount::where('user_id', Auth::id())->findOrFail($id);
        $account->delete();

        return response()->json([
            'success' => true,
            'message' => 'Account removed.',
        ]);
    }
}