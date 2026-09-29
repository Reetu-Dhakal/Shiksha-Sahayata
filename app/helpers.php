<?php

use Illuminate\Support\Carbon;

if (! function_exists('format_date')) {
    /**
     * Format a date using the current application locale.
     * Month names are translated through lang/{locale}/months.php.
     */
    function format_date(Carbon|string|int|null $date, string $format = 'j M Y'): string
    {
        if ($date === null || $date === '') {
            return '—';
        }

        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);

        if (app()->getLocale() !== 'np') {
            return $carbon->format($format);
        }

        $short = __('months.short');
        $long = __('months.long');

        if (! is_array($short) || ! is_array($long)) {
            return $carbon->format($format);
        }

        $result = '';
        $length = strlen($format);

        for ($i = 0; $i < $length; $i++) {
            $token = $format[$i];
            $result .= match ($token) {
                'M' => $short[$carbon->month - 1] ?? $carbon->format('M'),
                'F' => $long[$carbon->month - 1] ?? $carbon->format('F'),
                default => $carbon->format($token),
            };
        }

        return $result;
    }
}

if (! function_exists('pick_translation')) {
    /**
     * Return the Nepali value when the application runs in Nepali and one is stored,
     * otherwise fall back to the default (English) value.
     */
    function pick_translation(?string $default, ?string $alternate): ?string
    {
        if (app()->getLocale() === 'np' && $alternate !== null && trim($alternate) !== '') {
            return $alternate;
        }

        return $default;
    }
}
