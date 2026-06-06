<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\CampaignEmail;
use App\Models\EmailTemplate;
use App\Models\GmailAccount;
use App\Services\AppScriptService;
use App\Services\GmailService;
use App\Services\ObanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CampaignController extends Controller
{
    // ============================================================
    // GET ALL CAMPAIGNS
    // ============================================================
    public function index()
    {
        $campaigns = Campaign::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($campaign) {
                return [
                    'id'              => $campaign->id,
                    'name'            => $campaign->name,
                    'domain'          => $campaign->domain,
                    'facebook_links'  => $campaign->facebook_links ?? [],  // ← NEW
                    'website_links'   => $campaign->website_links  ?? [],  // ← NEW
                    'price'           => $campaign->price,
                    'your_name'       => $campaign->your_name,
                    'label_name'      => $campaign->label_name,
                    'status'          => $campaign->status,
                    'total_emails'    => $campaign->total_emails,
                    'sent_count'      => $campaign->sent_count,
                    'replied_count'   => $campaign->replied_count,
                    'follow_up_count' => $campaign->follow_up_count,
                    'bounce_count'    => $campaign->bounce_count,
                    'pending_count'   => $campaign->emails()->where('status', 'pending')->count(),
                    'failed_count'    => $campaign->emails()->where('status', 'failed')->count(),
                    'created_at'      => $campaign->created_at,
                ];
            });

        return response()->json([
            'success'   => true,
            'campaigns' => $campaigns,
        ]);
    }

    // ============================================================
    // GET SINGLE CAMPAIGN
    // ============================================================
    public function show($id)
    {
        $campaign = Campaign::where('user_id', Auth::id())
            ->with(['emails.gmailAccount', 'gmailAccounts'])
            ->findOrFail($id);

        $emails = $campaign->emails->map(function ($email) {
            return [
                'id'              => $email->id,
                'to_email'        => $email->to_email,
                'from_email'      => $email->from_email,
                'first_name'      => $email->first_name,
                'company_name'    => $email->company_name,
                'subject'         => $email->subject,
                'status'          => $email->status,
                'has_reply'       => $email->has_reply,
                'is_bounced'      => $email->is_bounced,
                'replied_at'      => $email->replied_at,
                'follow_up_count' => $email->follow_up_count,
                'template_type'   => $email->template_type,
                'sent_at'         => $email->sent_at,
                'gmail_account'   => $email->gmailAccount?->email,
            ];
        });

        return response()->json([
            'success'  => true,
            'campaign' => [
                'id'              => $campaign->id,
                'name'            => $campaign->name,
                'domain'          => $campaign->domain,
                'facebook_links'  => $campaign->facebook_links ?? [],  // ← NEW
                'website_links'   => $campaign->website_links  ?? [],  // ← NEW
                'price'           => $campaign->price,
                'your_name'       => $campaign->your_name,
                'label_name'      => $campaign->label_name,
                'status'          => $campaign->status,
                'total_emails'    => $campaign->total_emails,
                'sent_count'      => $campaign->sent_count,
                'replied_count'   => $campaign->replied_count,
                'bounce_count'    => $campaign->bounce_count,
                'follow_up_count' => $campaign->follow_up_count,
                'gmail_accounts'  => $campaign->gmailAccounts->pluck('email'),
                'emails'          => $emails,
            ],
        ]);
    }

    // ============================================================
    // CREATE CAMPAIGN
    // ============================================================
    public function store(Request $request)
    {
        $request->validate([
            'name'             => 'required|string|max:255',
            'domain'           => 'required|string',
            'price'            => 'required|string',
            'your_name'        => 'required|string',
            'recipients'       => 'required|string',
            'gmail_accounts'   => 'required|array|min:1',
            'gmail_accounts.*' => 'exists:gmail_accounts,id',
            'split_mode'       => 'required|in:equal,custom',
            'custom_splits'    => 'nullable|array',
            'facebook_links'   => 'nullable|array',   // ← NEW
            'facebook_links.*' => 'url',               // ← NEW: each entry must be a valid URL
            'website_links'    => 'nullable|array',   // ← NEW
            'website_links.*'  => 'url',               // ← NEW
        ]);

        // Check duplicate name
        $exists = Campaign::where('user_id', Auth::id())
            ->where('name', $request->name)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'error'   => "Campaign \"{$request->name}\" already exists.",
            ]);
        }

        // Parse recipients
        $recipients = preg_split('/[\n,;]+/', $request->recipients);
        $recipients = array_values(array_filter(
            array_map('trim', $recipients),
            fn($e) => filter_var($e, FILTER_VALIDATE_EMAIL)
        ));

        if (empty($recipients)) {
            return response()->json([
                'success' => false,
                'error'   => 'No valid email addresses found.',
            ]);
        }

        // Get Gmail accounts
        $accounts = GmailAccount::where('user_id', Auth::id())
            ->whereIn('id', $request->gmail_accounts)
            ->where('is_active', true)
            ->get();

        if ($accounts->isEmpty()) {
            return response()->json([
                'success' => false,
                'error'   => 'No active Gmail accounts found.',
            ]);
        }

        // Check templates exist
        $templateCount = EmailTemplate::where('user_id', Auth::id())
            ->where('type', 'bulk_template')
            ->where('is_active', true)
            ->count();

        if ($templateCount === 0) {
            return response()->json([
                'success' => false,
                'error'   => 'No initial templates found. Create at least one bulk_template first.',
            ]);
        }

        // Split recipients
        $splits = $this->splitRecipients(
            $recipients,
            $accounts,
            $request->split_mode,
            $request->custom_splits ?? []
        );

        // Create Gmail label via first account
        $firstAccount = $accounts->first();
        $labelName    = "Outbound - {$request->domain}";
        $labelId      = null;

        try {
            $gmailService = new GmailService($firstAccount);
            $labelResult  = $gmailService->getOrCreateLabel($labelName);
            $labelId      = $labelResult['success'] ? $labelResult['label_id'] : null;
        } catch (\Exception $e) {
            Log::warning('Label creation failed', ['error' => $e->getMessage()]);
        }

        // Create campaign — includes facebook_links and website_links
        $campaign = Campaign::create([
            'user_id'        => Auth::id(),
            'name'           => $request->name,
            'domain'         => $request->domain,
            'facebook_links' => $request->facebook_links ?? [],  // ← NEW
            'website_links'  => $request->website_links  ?? [],  // ← NEW
            'price'          => $request->price,
            'your_name'      => $request->your_name,
            'label_name'     => $labelName,
            'gmail_label_id' => $labelId,
            'status'         => 'active',
            'total_emails'   => count($recipients),
        ]);

        // Attach accounts to campaign pivot
        foreach ($splits as $accountId => $accountRecipients) {
            $campaign->gmailAccounts()->attach($accountId, [
                'allocated_count' => count($accountRecipients),
            ]);
        }

        // Create email records + queue jobs
        $jobsCreated = 0;

        foreach ($splits as $accountId => $accountRecipients) {
            $account = $accounts->firstWhere('id', $accountId);
            $delay   = 0;

            foreach ($accountRecipients as $recipientEmail) {
                $template = EmailTemplate::getRandomByType(
                    userId: Auth::id(),
                    type: 'bulk_template'
                );

                if (!$template) continue;

                $names = \App\Services\GmailService::extractNamesFromEmail($recipientEmail);

                $personalized = $template->personalize([
                    'company'   => $names['company_name'],
                    'domain'    => $request->domain,
                    'price'     => $request->price,
                    'firstName' => $names['first_name'],
                    'yourName'  => $request->your_name,
                ]);

                $campaignEmail = CampaignEmail::create([
                    'campaign_id'      => $campaign->id,
                    'user_id'          => Auth::id(),
                    'gmail_account_id' => $account->id,
                    'to_email'         => $recipientEmail,
                    'from_email'       => $account->email,
                    'first_name'       => $names['first_name'],
                    'company_name'     => $names['company_name'],
                    'subject'          => $personalized['subject'],
                    'body'             => $personalized['body'],
                    'gmail_label_id'   => $labelId,
                    'template_type'    => 'bulk_template',
                    'template_number'  => $template->id,
                    'status'           => 'pending',
                ]);

                $delay += rand(2, 4);
                ObanService::insertEmailJob($campaignEmail->id, $delay);
                $jobsCreated++;
            }
        }

        return response()->json([
            'success'  => true,
            'message'  => "Campaign created! {$jobsCreated} emails queued.",
            'campaign' => [
                'id'           => $campaign->id,
                'name'         => $campaign->name,
                'total_emails' => count($recipients),
                'jobs_queued'  => $jobsCreated,
                'label'        => $labelName,
            ],
        ]);
    }

    // ============================================================
    // SEND FOLLOW UP
    // ============================================================
    public function sendFollowUp(Request $request, $id)
    {
        $campaign = Campaign::where('user_id', Auth::id())->findOrFail($id);

        $emails = CampaignEmail::where('campaign_id', $campaign->id)
            ->where('status', 'sent')
            ->where('has_reply', false)
            ->where('is_bounced', false)
            ->get();

        if ($emails->isEmpty()) {
            return response()->json([
                'success' => false,
                'error'   => 'No eligible prospects for follow up.',
            ]);
        }

        $nextLevel = ($emails->first()->follow_up_count ?? 0) + 1;
        $template  = EmailTemplate::getForFollowUpLevel(Auth::id(), $nextLevel);

        if (!$template) {
            return response()->json([
                'success' => false,
                'error'   => "No follow up template found for level {$nextLevel}. Please create a followup_{$nextLevel} template first.",
            ]);
        }

        $hasPriceVar = str_contains($template->body_template, '{price}') ||
                       str_contains($template->subject_template, '{price}');

        if ($hasPriceVar && !$request->has('price')) {
            return response()->json([
                'success'        => false,
                'needs_price'    => true,
                'campaign_price' => $campaign->price,
                'message'        => 'This follow up template contains {price}. Please confirm the price.',
                'eligible'       => $emails->count(),
                'level'          => $nextLevel,
            ]);
        }

        $price  = $request->input('price', $campaign->price);
        $queued = 0;
        $delay  = 0;

        foreach ($emails as $email) {
            $followUpLevel = $email->follow_up_count + 1;
            $tpl           = EmailTemplate::getForFollowUpLevel(Auth::id(), $followUpLevel);

            if (!$tpl) continue;

            if ($hasPriceVar && $price !== $campaign->price) {
                $email->update(['body' => str_replace('{price}', $price, $tpl->body_template)]);
            }

            $delay += rand(1, 3);
            ObanService::insertFollowUpJob($email->id, $delay);
            $queued++;
        }

        return response()->json([
            'success' => true,
            'message' => "{$queued} follow up emails queued for level {$nextLevel}.",
            'queued'  => $queued,
            'level'   => $nextLevel,
        ]);
    }

    // ============================================================
    // CHECK PRICE VAR
    // ============================================================
    public function checkPriceVar(Request $request)
    {
        $type = $request->input('type', 'bulk_template');

        $hasPriceVar = EmailTemplate::where('user_id', Auth::id())
            ->where('type', $type)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('body_template', 'like', '%{price}%')
                  ->orWhere('subject_template', 'like', '%{price}%');
            })
            ->exists();

        return response()->json([
            'success'       => true,
            'has_price_var' => $hasPriceVar,
            'type'          => $type,
        ]);
    }

    // ============================================================
    // FOLLOW UP STATUS
    // ============================================================
    public function followUpStatus($id)
    {
        $campaign = Campaign::where('user_id', Auth::id())->findOrFail($id);

        $eligible = CampaignEmail::where('campaign_id', $campaign->id)
            ->where('status', 'sent')
            ->where('has_reply', false)
            ->where('is_bounced', false)
            ->count();

        $firstEmail = CampaignEmail::where('campaign_id', $campaign->id)
            ->where('status', 'sent')
            ->first();

        $nextLevel   = $firstEmail ? ($firstEmail->follow_up_count + 1) : 1;
        $template    = EmailTemplate::getForFollowUpLevel(Auth::id(), $nextLevel);
        $hasPriceVar = $template
            ? (str_contains($template->body_template, '{price}') || str_contains($template->subject_template, '{price}'))
            : false;

        return response()->json([
            'success'        => true,
            'eligible'       => $eligible,
            'next_level'     => $nextLevel,
            'has_template'   => (bool) $template,
            'needs_price'    => $hasPriceVar,
            'campaign_price' => $campaign->price,
        ]);
    }

    // ============================================================
    // RETRY FAILED EMAILS
    // ============================================================
    public function retryFailed($id)
    {
        $campaign = Campaign::where('user_id', Auth::id())->findOrFail($id);

        $failedEmails = CampaignEmail::where('campaign_id', $campaign->id)
            ->where('status', 'failed')
            ->get();

        $delay = 0;

        foreach ($failedEmails as $email) {
            $email->update(['status' => 'pending']);
            $delay += rand(2, 4);
            ObanService::insertEmailJob($email->id, $delay);
        }

        return response()->json([
            'success' => true,
            'message' => "{$failedEmails->count()} emails requeued.",
        ]);
    }

    // ============================================================
    // PREVIEW SPLIT
    // ============================================================
    public function previewSplit(Request $request)
    {
        $request->validate([
            'recipients'     => 'required|string',
            'gmail_accounts' => 'required|array',
            'split_mode'     => 'required|in:equal,custom',
            'custom_splits'  => 'nullable|array',
        ]);

        $recipients = preg_split('/[\n,;]+/', $request->recipients);
        $recipients = array_values(array_filter(
            array_map('trim', $recipients),
            fn($e) => filter_var($e, FILTER_VALIDATE_EMAIL)
        ));

        $accounts = GmailAccount::where('user_id', Auth::id())
            ->whereIn('id', $request->gmail_accounts)
            ->where('is_active', true)
            ->get();

        $splits  = $this->splitRecipients(
            $recipients,
            $accounts,
            $request->split_mode,
            $request->custom_splits ?? []
        );

        $preview = [];

        foreach ($splits as $accountId => $accountRecipients) {
            $account   = $accounts->firstWhere('id', $accountId);
            $preview[] = [
                'account'   => $account->email,
                'count'     => count($accountRecipients),
                'remaining' => $account->remainingToday(),
            ];
        }

        return response()->json([
            'success'    => true,
            'total'      => count($recipients),
            'preview'    => $preview,
            'total_time' => $this->estimateTime(count($recipients)),
        ]);
    }

    // ============================================================
    // DELETE CAMPAIGN
    // ============================================================
    public function destroy($id)
    {
        $campaign = Campaign::where('user_id', Auth::id())->findOrFail($id);
        $campaign->delete();

        return response()->json([
            'success' => true,
            'message' => 'Campaign deleted.',
        ]);
    }

    // ============================================================
    // SPLIT RECIPIENTS ACROSS ACCOUNTS
    // ============================================================
    private function splitRecipients(
        array $recipients,
        $accounts,
        string $mode,
        array $customSplits
    ): array {
        $splits = [];
        $total  = count($recipients);
        $count  = $accounts->count();

        if ($mode === 'equal') {
            $perAccount = (int) ceil($total / $count);
            $offset     = 0;

            foreach ($accounts as $account) {
                $splits[$account->id] = array_slice($recipients, $offset, $perAccount);
                $offset += $perAccount;
            }
        } else {
            $offset = 0;
            foreach ($accounts as $account) {
                $amount = $customSplits[$account->id] ?? (int) ceil($total / $count);
                $splits[$account->id] = array_slice($recipients, $offset, $amount);
                $offset += $amount;
            }
        }

        return $splits;
    }

    // ============================================================
    // ESTIMATE SENDING TIME
    // ============================================================
    private function estimateTime(int $count): string
    {
        $avgMinutes = 1.5;
        $totalMins  = (int) ($count * $avgMinutes);
        $hours      = (int) floor($totalMins / 60);
        $mins       = $totalMins % 60;

        return $hours > 0 ? "{$hours}h {$mins}m" : "{$mins} minutes";
    }
}