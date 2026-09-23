<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\CampaignEmail;
use App\Models\EmailTemplate;
use App\Services\AppScriptService;
use App\Services\GmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailDispatchController extends Controller
{
    protected AppScriptService $appScript;

    public function __construct()
    {
        $this->appScript = new AppScriptService();
    }

    // ============================================================
    // SEND INITIAL EMAIL — called by Elixir Oban worker
    // ============================================================
    public function sendInitial(Request $request)
    {
        $email = CampaignEmail::with(['campaign', 'gmailAccount'])
            ->find($request->campaign_email_id);

          

        if (!$email) {
            return response()->json(['error' => 'Email not found'], 404);
        }

        if ($email->status === 'sent') {
            return response()->json(['status' => 'already_sent']);
        }

        $account = $email->gmailAccount;

       

        if (!$account || !$account->is_active) {
            $email->update(['status' => 'failed']);
            return response()->json(['error' => 'Account inactive'], 400);
        }

        if ($account->hasReachedDailyLimit()) {
            return response()->json(['error' => 'Daily limit reached'], 400);
        }

        if (!$account->script_url) {
            $email->update(['status' => 'failed']);
            return response()->json(['error' => 'No Apps Script URL configured'], 400);
        }

        // Send via Apps Script
        $result = $this->appScript->sendEmail(
            scriptUrl: $account->script_url,
            to:        $email->to_email,
            subject:   $email->subject,
            body:      $email->body,
            name:     $account->name 
        );

        if ($result['success']) {
            $email->update([
                'status'           => 'sent',
                'gmail_message_id' => $result['message_id'],
                'gmail_thread_id'  => $result['thread_id'],
                'sent_at'          => now(),
            ]);

            $account->incrementSent();
            $email->campaign->refreshStats();

            Log::info('✅ Initial email sent', [
                'to'        => $email->to_email,
                'account'   => $account->email,
                'thread_id' => $result['thread_id'],
            ]);

            return response()->json(['status' => 'sent']);
        }

        $email->update(['status' => 'failed']);

        Log::error('❌ Initial email failed', [
            'id'    => $email->id,
            'error' => $result['error'],
        ]);

        return response()->json(['error' => $result['error']], 500);
    }

    // ============================================================
    // SEND FOLLOW UP — called by Elixir Oban worker
    // ============================================================
    public function sendFollowUp(Request $request)
    {
        $email = CampaignEmail::with(['campaign', 'gmailAccount'])
            ->find($request->campaign_email_id);

        if (!$email) {
            return response()->json(['error' => 'Email not found'], 404);
        }

        // Skip if replied
        if ($email->has_reply) {
            return response()->json(['status' => 'has_reply_skipped']);
        }

        // Skip if not sent
        if ($email->status !== 'sent') {
            return response()->json(['error' => 'Not sent yet'], 400);
        }

        $account = $email->gmailAccount;

        if (!$account || !$account->is_active) {
            return response()->json(['error' => 'Account inactive'], 400);
        }

        if ($account->hasReachedDailyLimit()) {
            return response()->json(['error' => 'Daily limit reached'], 400);
        }

        // Use Gmail API to check bounce + reply
        if ($email->gmail_thread_id) {
            try {
                $gmail = new GmailService($account);

                if ($gmail->threadHasBounce($email->gmail_thread_id)) {
                    $email->markAsBounced();
                    $email->campaign->refreshStats();
                    Log::info('⚠️ Bounce detected', ['to' => $email->to_email]);
                    return response()->json(['status' => 'bounced_skipped']);
                }

                if ($gmail->threadHasReply($email->gmail_thread_id, $email->to_email)) {
                    $email->markAsReplied();
                    $email->campaign->refreshStats();
                    Log::info('💬 Reply detected', ['to' => $email->to_email]);
                    return response()->json(['status' => 'reply_detected_skipped']);
                }

            } catch (\Exception $e) {
                Log::warning('Gmail API check failed', ['error' => $e->getMessage()]);
            }
        }

        // Get follow up template for this level
        $followUpLevel = $email->follow_up_count + 1;
        $template = EmailTemplate::getForFollowUpLevel(
            $email->campaign->user_id,
            $followUpLevel
        );

        if (!$template) {
            Log::warning('No follow up template', [
                'level' => $followUpLevel,
                'to'    => $email->to_email,
            ]);
            return response()->json(['error' => 'No follow up template found'], 400);
        }

        // Personalize
        $personalized = $template->personalize([
            'firstName' => $email->first_name,
            'company'   => $email->company_name,
            'domain'    => $email->campaign->domain,
            'price'     => $email->campaign->price,
            'yourName'  => $email->campaign->your_name,
        ]);

           $gmail = new GmailService($account);
        // $result = $this->appScript->sendFollowUp(
        //     scriptUrl: $account->script_url,
        //     to:        $email->to_email,
        //     subject:   $email->subject,
        //     body:      $personalized['body'],
        //     threadId:  $email->gmail_thread_id,
        // );
         $result = $gmail->sendFollowUp(
            $email->to_email,
            $email->subject,
            $personalized['body'],
            $email->gmail_thread_id,
            $email->gmail_message_id,
            $email->gmail_label_id
        );

        if ($result['success']) {
            $email->incrementFollowUp();
            $email->update([
                'template_number' => $template->id,
                'template_type'   => "followup_{$followUpLevel}",
            ]);

            $account->incrementSent();
            $email->campaign->refreshStats();

            Log::info('✅ Follow up sent', [
                'to'    => $email->to_email,
                'level' => $followUpLevel,
            ]);

            return response()->json(['status' => 'followup_sent']);
        }

        Log::error('❌ Follow up failed', [
            'id'    => $email->id,
            'error' => $result['error'],
        ]);

        return response()->json(['error' => $result['error']], 500);
    }
}