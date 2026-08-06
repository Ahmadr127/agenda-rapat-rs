<?php

namespace App\Helpers;

class NameConverter
{
    /**
     * Common academic/professional degree prefixes & suffixes (Indonesian + general).
     * Add more as needed.
     */
    protected static array $degrees = [
        // Prefixes
        'Prof',
        'Dr',
        'Drs',
        'Ir',
        'H',
        'Hj',

        // Suffixes (post-name titles)
        'ST',
        'SE',
        'SKom',
        'SH',
        'SP',
        'SKG',
        'SKep',
        'SAg',
        'SIP',
        'SPsi',
        'SSos',
        'SFil',
        'SSi',
        'SPd',
        'SAP',
        'SHut',
        'MT',
        'MM',
        'MH',
        'MSi',
        'MKom',
        'MKes',
        'MAg',
        'MPd',
        'MBA',
        'MSc',
        'MFil',
        'MSos',
        'MAP',
        'MIP',
        'PhD',
        'DBA',
        'DEA',
        'DSc',
        'Amd',
        'AmdKeb',
        'AmdKep',
        'AmdFar',
        'Bc',
        'MARS',
        'SpOG',
        'SpPD',
        'SpA',
        'SpB',
        'SpS',
        'SpM',
    ];

    /**
     * Convert a raw full name from the employees table into:
     *  - 'name'  : cleaned display name (no degrees, no single-letter initials, no symbols)
     *  - 'email' : firstname + lastname, lowercased, no symbols
     *
     * @param  string  $fullName  e.g. "Dr. R. Ahmad Fauzi, S.T., M.M."
     * @param  string  $domain    e.g. "company.com"
     * @return array{name: string, email: string}
     */
    public static function convert(string $fullName, string $domain = 'company.com'): array
    {
        // 1. Strip everything after a comma (degree suffixes often follow a comma)
        $name = explode(',', $fullName)[0];

        // 2. Remove degree tokens (with or without surrounding dots/spaces)
        //    Handles patterns like "Dr.", "S.T.", "M.M.", "PhD", "Drs." etc.
        foreach (self::$degrees as $degree) {
            // Escape any regex special chars in the degree string
            $escaped = preg_quote($degree, '/');
            // Match the degree with optional surrounding dots, word boundaries, case-insensitive
            $name = preg_replace('/\b' . str_replace('\.', '\.?', $escaped) . '\.?\b/iu', '', $name);
        }

        // 3. Remove all remaining symbols (dots, commas, parentheses, hyphens used as separators, etc.)
        //    Keep only letters, spaces, and apostrophes (for names like O'Brien)
        $name = preg_replace("/[^a-zA-Z\s']/u", ' ', $name);

        // 4. Collapse multiple spaces
        $name = preg_replace('/\s+/', ' ', trim($name));

        // 5. Split into tokens
        $tokens = explode(' ', $name);

        // 6. Remove single-letter tokens (initials like "R", "A", "B")
        $tokens = array_filter($tokens, fn($token) => mb_strlen($token) > 1);

        // 7. Re-index and title-case
        $tokens = array_values(array_map('ucwords', $tokens));

        // 8. Build display name (first name only)
        $cleanName = $tokens[0] ?? '';

        // 9. Build email: first token + last token (lowercased, letters only)
        $emailFirst = isset($tokens[0]) ? preg_replace('/[^a-z]/i', '', strtolower($tokens[0])) : '';
        $emailLast = count($tokens) > 1
            ? preg_replace('/[^a-z]/i', '', strtolower(end($tokens)))
            : '';

        $localPart = $emailLast ? "{$emailFirst}.{$emailLast}" : $emailFirst;
        $email = "{$localPart}@{$domain}";

        return [
            'name' => $cleanName,
            'email' => $email,
        ];
    }

    /**
     * Bulk-process all rows from the employees table.
     *
     * Usage:
     *   $results = NameConverter::processEmployees('company.com');
     *
     * @param  string  $domain
     * @return \Illuminate\Support\Collection
     */
    public static function processEmployees(string $domain = 'company.com'): \Illuminate\Support\Collection
    {
        return \DB::table('employees')
            ->select('id', 'full_name')
            ->get()
            ->map(function ($employee) use ($domain) {
                $converted = self::convert($employee->full_name, $domain);

                return [
                    'id' => $employee->id,
                    'full_name' => $employee->full_name,
                    'name' => $converted['name'],
                    'email' => $converted['email'],
                ];
            });
    }

    /**
     * Update name & email columns directly on the employees table.
     *
     * Requires employees table to have `name` and `email` columns.
     *
     * Usage:
     *   $count = NameConverter::syncEmployees('company.com');
     *
     * @param  string  $domain
     * @return int  Number of rows updated
     */
    public static function syncEmployees(string $domain = 'company.com'): int
    {
        $updated = 0;

        \DB::table('employees')
            ->select('id', 'full_name')
            ->orderBy('id')
            ->chunk(200, function ($employees) use ($domain, &$updated) {
                foreach ($employees as $employee) {
                    $converted = self::convert($employee->full_name, $domain);

                    \DB::table('employees')
                        ->where('id', $employee->id)
                        ->update([
                            'name' => $converted['name'],
                            'email' => $converted['email'],
                            'updated_at' => now(),
                        ]);

                    $updated++;
                }
            });

        return $updated;
    }
}