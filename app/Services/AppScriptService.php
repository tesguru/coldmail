<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AppScriptService
{
    // ============================================================
    // SEND INITIAL EMAIL
    // ============================================================
    public function sendEmail(
        string $scriptUrl,
        string $to,
        string $subject,
        string $body,
        string $name,
    ): array {
        try {
            $response = Http::timeout(60)->post($scriptUrl, [
                'action'    => 'send',
                'to'        => $to,
                'subject'   => $subject,
                'body'      => $body,
                'thread_id' => null,
                'name'      => $name,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if ($data['success'] ?? false) {
                    return [
                        'success'    => true,
                        'message_id' => $data['message_id'] ?? null,
                        'thread_id'  => $data['thread_id']  ?? null,
                    ];
                }

                return [
                    'success' => false,
                    'error'   => $data['error'] ?? 'Apps Script returned failure',
                ];
            }

            return [
                'success' => false,
                'error'   => 'HTTP ' . $response->status(),
            ];

        } catch (\Exception $e) {
            Log::error('AppScriptService::sendEmail failed', [
                'to'    => $to,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    // ============================================================
    // SEND FOLLOW UP EMAIL (in same thread)
    // ============================================================
    public function sendFollowUp(
        string $scriptUrl,
        string $to,
        string $subject,
        string $body,
        string $threadId,
    ): array {
        try {
            $response = Http::timeout(60)->post($scriptUrl, [
                'action'    => 'send',
                'to'        => $to,
                'subject'   => 'Re: ' . $subject,
                'body'      => $body,
                'thread_id' => $threadId,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if ($data['success'] ?? false) {
                    return [
                        'success'    => true,
                        'message_id' => $data['message_id'] ?? null,
                        'thread_id'  => $data['thread_id']  ?? null,
                    ];
                }

                return [
                    'success' => false,
                    'error'   => $data['error'] ?? 'Apps Script returned failure',
                ];
            }

            return [
                'success' => false,
                'error'   => 'HTTP ' . $response->status(),
            ];

        } catch (\Exception $e) {
            Log::error('AppScriptService::sendFollowUp failed', [
                'to'        => $to,
                'thread_id' => $threadId,
                'error'     => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    // ============================================================
    // GET STATS FROM APPS SCRIPT
    // ============================================================
  public function getStats(string $scriptUrl): array
{
    try {
        $response = Http::timeout(30)->get($scriptUrl, [
            'action' => 'stats',
        ]);

        if ($response->successful()) {
            $data = $response->json();
            
            // Ensure we return an array even if json() returns null
            return is_array($data) ? $data : [];
        }

        return [];

    } catch (\Exception $e) {
        Log::warning('AppScriptService::getStats failed', [
            'error' => $e->getMessage(),
            'url' => $scriptUrl,
        ]);
        return [];
    }
}

    // ============================================================
    // TEST CONNECTION
    // ============================================================
    public function testConnection(string $scriptUrl): bool
    {
        try {
            $response = Http::timeout(15)->get($scriptUrl, [
                'action' => 'status',
            ]);

            return $response->successful() &&
                ($response->json()['status'] ?? '') === 'online';

        } catch (\Exception $e) {
            return false;
        }
    }
}