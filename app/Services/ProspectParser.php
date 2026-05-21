<?php

namespace App\Services;

class ProspectParser
{
    protected static array $freeDomains = [
        'gmail.com', 'yahoo.com', 'outlook.com', 'hotmail.com',
        'aol.com', 'icloud.com', 'protonmail.com', 'zoho.com',
        'yandex.com', 'mail.com', 'live.com',
    ];

    // ============================================================
    // PARSE SINGLE EMAIL
    // ============================================================
    public static function parse(string $email): array
    {
        $parts     = explode('@', strtolower(trim($email)));
        $localPart = $parts[0] ?? '';
        $domain    = $parts[1] ?? '';

        return [
            'email'        => strtolower(trim($email)),
            'first_name'   => self::extractFirstName($localPart),
            'company_name' => self::extractCompany($domain, $localPart),
        ];
    }

    // ============================================================
    // PARSE MULTIPLE EMAILS (textarea input)
    // ============================================================
    public static function parseMany(string $input): array
    {
        $lines = array_filter(
            array_map('trim', explode("\n", $input)),
            fn($line) => !empty($line) && str_contains($line, '@')
        );

        return array_values(array_map(
            fn($line) => self::parse($line),
            $lines
        ));
    }

    // ============================================================
    // EXTRACT FIRST NAME FROM LOCAL PART
    // ============================================================
    protected static function extractFirstName(string $localPart): string
    {
        // Remove numbers
        $name = preg_replace('/[0-9]+/', '', $localPart);

        // Replace separators with space
        $name = str_replace(['.', '_', '-', '+'], ' ', $name);

        // Clean up
        $name = trim(preg_replace('/\s+/', ' ', $name));

        // Get first word
        $parts     = explode(' ', $name);
        $firstName = ucfirst(strtolower($parts[0] ?? ''));

        return (strlen($firstName) >= 2) ? $firstName : 'there';
    }

    // ============================================================
    // EXTRACT COMPANY FROM DOMAIN
    // ============================================================
    protected static function extractCompany(string $domain, string $localPart): string
    {
        // Free email — use local part as company
        if (in_array($domain, self::$freeDomains)) {
            $clean = preg_replace('/[0-9._-]+/', ' ', $localPart);
            return ucwords(strtolower(trim($clean))) ?: 'Your Company';
        }

        // Business email — use domain name
        $domainParts = explode('.', $domain);
        $company     = ucwords(str_replace(['-', '_'], ' ', $domainParts[0] ?? ''));

        return $company ?: 'Your Company';
    }
}