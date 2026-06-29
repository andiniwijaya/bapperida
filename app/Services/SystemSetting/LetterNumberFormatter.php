<?php

namespace App\Services\SystemSetting;

/**
 * Formats letter numbers from a configurable template string.
 *
 * Business rules:
 * - Supports placeholders: letter_code, sequence_number, department, year, prefix.
 * - Legacy aliases: code, seq, dept (for backward-compatible templates).
 * - Sequence is zero-padded to three digits in formatted output.
 */
class LetterNumberFormatter
{
    /**
     * Build a letter number from template and segment values.
     *
     * @param  string  $template  e.g. {letter_code}/{sequence_number}/{department}/{year}
     * @param  array{letter_code: string, sequence_number: int, department_code: string, year: int|string, prefix?: string|null}  $parts
     */
    public function format(string $template, array $parts): string
    {
        $sequence = sprintf('%03d', $parts['sequence_number']);

        $replacements = [
            '{letter_code}' => $parts['letter_code'],
            '{sequence_number}' => $sequence,
            '{department}' => $parts['department_code'],
            '{year}' => (string) $parts['year'],
            '{prefix}' => $parts['prefix'] ?? '',
            '{code}' => $parts['letter_code'],
            '{seq}' => $sequence,
            '{dept}' => $parts['department_code'],
        ];

        $formatted = str_replace(array_keys($replacements), array_values($replacements), $template);

        $prefix = $parts['prefix'] ?? null;

        if ($prefix !== null && $prefix !== '' && ! str_contains($template, '{prefix}')) {
            return rtrim($prefix, '/').'/'.ltrim($formatted, '/');
        }

        return $formatted;
    }
}
