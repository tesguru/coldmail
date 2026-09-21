<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GmailAccountController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\Internal\EmailDispatchController;
use App\Http\Controllers\ObanStatusController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/oban', [ObanStatusController::class, 'dashboard'])->name('oban.dashboard');
    Route::get('/oban-dashboard', [ObanStatusController::class, 'dashboard'])->name('oban-dashboard');
    Route::get('/oban-status', [ObanStatusController::class, 'status'])->name('oban.status');
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