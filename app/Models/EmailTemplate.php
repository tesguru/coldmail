<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'category',
        'type',
        'subject_template',
        'body_template',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ============================================================
    // RELATIONSHIPS
    // ============================================================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ============================================================
    // GET RANDOM BY TYPE
    // ============================================================

    public static function getRandomByType(
        int $userId,
        string $type,
        ?int $excludeNumber = null
    ): ?self {
        return self::where('user_id', $userId)
            ->where('type', $type)
            ->where('is_active', true)
            ->when($excludeNumber, fn($q) => $q->where('id', '!=', $excludeNumber))
            ->inRandomOrder()
            ->first();
    }

    // ============================================================
    // PERSONALIZE
    // ============================================================

    public function personalize(array $variables): array
    {
        $subject = $this->subject_template;
        $body    = $this->body_template;

        $replacements = [
            '{company}'   => $variables['company']   ?? 'your company',
            '{domain}'    => $variables['domain']     ?? '',
            '{price}'     => $variables['price']      ?? '',
            '{firstName}' => $variables['firstName']  ?? 'there',
            '{yourName}'  => $variables['yourName']   ?? '',
        ];

        foreach ($replacements as $key => $value) {
            $subject = str_replace($key, $value, $subject);
            $body    = str_replace($key, $value, $body);
        }

        return compact('subject', 'body');
    }

    // ============================================================
    // FOLLOW UP FALLBACK
    // Get template for level, fall back to nearest previous if none
    // ============================================================

    public static function getForFollowUpLevel(int $userId, int $level): ?self
    {
        // Try exact level first
        $template = self::where('user_id', $userId)
            ->where('type', "followup_{$level}")
            ->where('is_active', true)
            ->inRandomOrder()
            ->first();

        if ($template) return $template;

        // Fall back to nearest previous level
        for ($i = $level - 1; $i >= 1; $i--) {
            $template = self::where('user_id', $userId)
                ->where('type', "followup_{$i}")
                ->where('is_active', true)
                ->inRandomOrder()
                ->first();

            if ($template) return $template;
        }

        return null;
    }
}