<?php

namespace App\Http\Controllers;

use App\Models\GmailAccount;
use App\Services\AppScriptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GmailAccountController extends Controller
{
    // ============================================================
    // GET ALL ACCOUNTS
    // ============================================================
    public function index()
    {
        $accounts = GmailAccount::where('user_id', Auth::id())
            ->get()
            ->map(function ($account) {
                return [
                    'id'           => $account->id,
                    'name'         => $account->name,
                    'email'        => $account->email,
                    'avatar'       => $account->avatar,
                    'script_url'   => $account->script_url,
                    'sent_today'   => $account->sent_today,
                    'total_sent'   => $account->total_sent,
                    'daily_limit'  => $account->daily_limit,
                    'remaining'    => $account->remainingToday(),
                    'is_active'    => $account->is_active,
                    'token_status' => $account->google_token ? 'valid' : 'missing',
                    'has_script'   => !empty($account->script_url),
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