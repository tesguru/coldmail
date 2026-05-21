<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GmailAccountController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\Internal\EmailDispatchController;
use Illuminate\Support\Facades\Route;

Route::get('/oban-dashboard', function () {
    return view('oban.dashboard');
})->name('oban-dashboard');






Route::get('/oban-status', function () {
    $jobs = \DB::table('oban_jobs')
        ->orderBy('inserted_at', 'desc')
        ->limit(20)
        ->get()
        ->map(function ($job) {
            return [
                'id'           => $job->id,
                'state'        => $job->state,
                'queue'        => $job->queue,
                'worker'       => class_basename(str_replace('Elixir.', '', $job->worker)),
                'args'         => json_decode($job->args),
                'attempt'      => $job->attempt,
                'max_attempts' => $job->max_attempts,
                'inserted_at'  => $job->inserted_at,
                'scheduled_at' => $job->scheduled_at,
                'attempted_at' => $job->attempted_at,
                'completed_at' => $job->completed_at,
                'discarded_at' => $job->discarded_at,
            ];
        });

    $summary = [
        'available'  => \DB::table('oban_jobs')->where('state', 'available')->count(),
        'scheduled'  => \DB::table('oban_jobs')->where('state', 'scheduled')->count(),
        'executing'  => \DB::table('oban_jobs')->where('state', 'executing')->count(),
        'completed'  => \DB::table('oban_jobs')->where('state', 'completed')->count(),
        'retryable'  => \DB::table('oban_jobs')->where('state', 'retryable')->count(),
        'discarded'  => \DB::table('oban_jobs')->where('state', 'discarded')->count(),
        'cancelled'  => \DB::table('oban_jobs')->where('state', 'cancelled')->count(),
    ];

    return response()->json([
        'summary' => $summary,
        'jobs'    => $jobs,
    ]);
});


Route::get('/login', fn() => view('auth.login'))->name('login');
Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('google.callback');
Route::get('/auth/google/account', [GoogleController::class, 'redirectAccount'])->name('google.add-account');
Route::post('/auth/logout', [GoogleController::class, 'logout'])->name('google.logout');


Route::middleware('auth')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/accounts', fn() => view('accounts.index'))->name('accounts.index');
    Route::get('/templates', fn() => view('templates.index'))->name('templates.index');
    Route::get('/campaigns', fn() => view('campaigns.index'))->name('campaigns.index');
    Route::get('/campaigns/{id}', fn($id) => view('campaigns.show', ['id' => $id]))->name('campaigns.show');

});


Route::middleware('auth')->prefix('api')->group(function () {

    // Gmail Accounts
    Route::get('gmail-accounts', [GmailAccountController::class, 'index']);
    Route::post('gmail-accounts/{id}/script', [GmailAccountController::class, 'updateScript']);
    Route::post('gmail-accounts/{id}/limit', [GmailAccountController::class, 'updateLimit']);
    Route::post('gmail-accounts/{id}/toggle', [GmailAccountController::class, 'toggleActive']);
    Route::post('gmail-accounts/{id}/test', [GmailAccountController::class, 'test']);
    Route::delete('gmail-accounts/{id}', [GmailAccountController::class, 'destroy']);

    // Templates
    Route::get('templates', [TemplateController::class, 'index']);
    Route::get('templates/all', [TemplateController::class, 'all']);
    Route::post('templates', [TemplateController::class, 'store']);
    Route::put('templates/{id}', [TemplateController::class, 'update']);
    Route::delete('templates/{id}', [TemplateController::class, 'destroy']);

    // Campaigns
    Route::get('campaigns', [CampaignController::class, 'index']);
    Route::post('campaigns', [CampaignController::class, 'store']);
    Route::get('campaigns/{id}', [CampaignController::class, 'show']);
    Route::delete('campaigns/{id}', [CampaignController::class, 'destroy']);
    Route::post('campaigns/{id}/follow-up', [CampaignController::class, 'sendFollowUp']);
    Route::post('campaigns/{id}/retry-failed', [CampaignController::class, 'retryFailed']);
    Route::post('campaigns/preview-split', [CampaignController::class, 'previewSplit']);
    Route::post('campaigns/{id}/follow-up', [CampaignController::class, 'sendFollowUp']);
Route::get('campaigns/{id}/follow-up-status', [CampaignController::class, 'followUpStatus']);
Route::get('/templates/check-price-var', [CampaignController::class, 'checkPriceVar']);
});


Route::prefix('internal')->group(function () {
    Route::get('send-initial', [EmailDispatchController::class, 'sendInitial']);
    Route::get('send-followup', [EmailDispatchController::class, 'sendFollowUp']);
});