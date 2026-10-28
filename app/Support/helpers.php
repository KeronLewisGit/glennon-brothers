<?php

if (! function_exists('gb')) {
    /** A fact from config/glennon.php: gb('phone'), gb('projects'). */
    function gb(string $key, mixed $default = null): mixed
    {
        return config('glennon.'.$key, $default);
    }
}

if (! function_exists('phone_digits')) {
    /**
     * E.164 digits for an Irish or UK number written any common way, or null when it is
     * neither: 043 335 0800 → 353433350800, 01292 800600 → 441292800600, +353 86 … kept.
     * Numbers in another country's format are left to tel: as typed.
     */
    function phone_digits(?string $number): ?string
    {
        $raw = trim((string) $number);
        $digits = preg_replace('/\D+/', '', $raw) ?? '';
        if (str_starts_with($raw, '+') || str_starts_with($digits, '00')) {
            $digits = ltrim($digits, '0');

            return strlen($digits) >= 9 && strlen($digits) <= 15 ? $digits : null;
        }
        // A leading 0 is a national trunk prefix. 0 + 9/10 digits: Irish numbers have 7–9
        // digits after the 0 and area codes 1–99; UK numbers have 10 after it.
        if (str_starts_with($digits, '0')) {
            $national = substr($digits, 1);

            return match (true) {
                strlen($national) === 10 => '44'.$national,
                strlen($national) >= 7 && strlen($national) <= 9 => '353'.$national,
                default => null,
            };
        }

        return null;
    }
}

if (! function_exists('tel_href')) {
    function tel_href(?string $number): string
    {
        $digits = phone_digits($number);

        return $digits ? 'tel:+'.$digits : 'tel:'.preg_replace('/[^\d+]/', '', (string) $number);
    }
}

if (! function_exists('format_phone')) {
    /** Phone numbers are shown as they were written (the group prints +353 43 3350800); only whitespace is tidied. */
    function format_phone(?string $number): string
    {
        return trim((string) preg_replace('/\s+/', ' ', (string) $number));
    }
}

if (! function_exists('money')) {
    /** €12,450.00 or £8,900.00: quotes for Irish deliveries are in euro, for UK deliveries in sterling. */
    function money(float|int|string|null $amount, string $currency): string
    {
        if ($amount === null || $amount === '') {
            return '–';
        }
        $symbol = ['EUR' => '€', 'GBP' => '£'][$currency] ?? $currency.' ';

        return $symbol.number_format((float) $amount, 2);
    }
}

if (! function_exists('input_string')) {
    /** A request value as a string. Arrays (?type[]=x) and other junk become ''. */
    function input_string(mixed $value): string
    {
        return is_scalar($value) ? trim((string) $value) : '';
    }
}

if (! function_exists('lines_to_array')) {
    /**
     * Textarea with one item per line, bullets optional, to a clean list.
     *
     * @return list<string>
     */
    function lines_to_array(?string $text): array
    {
        $lines = preg_split('/\r\n|\r|\n/', (string) $text) ?: [];
        $clean = array_map(fn (string $l) => trim((string) preg_replace('/^(?:[-*]|•)\s*/u', '', $l)), $lines);

        return array_values(array_filter($clean, fn (string $l) => $l !== ''));
    }
}

if (! function_exists('compact_number')) {
    /** 1,284 → 1.3K, used on stat tiles. */
    function compact_number(int|float $n): string
    {
        return match (true) {
            abs($n) >= 1_000_000 => round($n / 1_000_000, 1).'M',
            abs($n) >= 10_000 => round($n / 1_000).'K',
            default => number_format($n),
        };
    }
}

if (! function_exists('photo_widths')) {
    /**
     * The WebP widths made for a photo: 480, 960 and 1920 where the original is that wide,
     * plus the original width when it is narrower than 1920 (the group's own photos are small).
     *
     * @return list<int>
     */
    function photo_widths(string $key): array
    {
        $w = (int) (config('glennon.photos.'.$key.'.w') ?? 1920);
        $widths = array_values(array_filter([480, 960, 1920], fn (int $x) => $x <= $w));
        if ($w < 1920 && ! in_array($w, $widths, true)) {
            $widths[] = $w;
        }

        return $widths;
    }
}

if (! function_exists('photo')) {
    /**
     * Responsive <img> attributes for a photo in public/img ({key}-{width}.webp).
     *
     * @return array{src: string, srcset: string, width: int, height: int, largest: string}
     */
    function photo(string $key): array
    {
        $meta = config('glennon.photos.'.$key) ?? ['w' => 1920, 'h' => 1280];
        $widths = photo_widths($key);
        $url = fn (int $w) => asset("img/{$key}-{$w}.webp");
        $middle = in_array(960, $widths, true) ? 960 : $widths[array_key_last($widths)];

        return [
            'src' => $url($middle),
            'srcset' => implode(', ', array_map(fn (int $w) => $url($w).' '.$w.'w', $widths)),
            'width' => (int) $meta['w'],
            'height' => (int) $meta['h'],
            'largest' => $url($widths[array_key_last($widths)]),
        ];
    }
}

if (! function_exists('is_illustrative')) {
    /** A stock photo that does not show the group's own plants or products. */
    function is_illustrative(?string $key): bool
    {
        return $key !== null && in_array($key, (array) config('glennon.illustrative_photos', []), true);
    }
}

if (! function_exists('file_size')) {
    /** 2.4 MB, 380 KB */
    function file_size(?int $bytes): string
    {
        $bytes = (int) $bytes;

        return $bytes >= 1_048_576 ? round($bytes / 1_048_576, 1).' MB' : max(1, (int) round($bytes / 1024)).' KB';
    }
}

if (! function_exists('plain_number')) {
    /** 9.50 → "9.5", 9.00 → "9": a figure without trailing zeros. */
    function plain_number(float $n, int $decimals = 2): string
    {
        return rtrim(rtrim(number_format($n, $decimals, '.', ''), '0'), '.');
    }
}

if (! function_exists('enum_options')) {
    /**
     * value => label pairs for a backed enum with a label() method, for <x-select> and <x-radios>.
     *
     * @param  class-string<BackedEnum>  $enum
     * @param  list<BackedEnum>|null  $cases
     * @return array<string, string>
     */
    function enum_options(string $enum, ?array $cases = null): array
    {
        $out = [];
        foreach ($cases ?? $enum::cases() as $case) {
            /** @var BackedEnum&object{label: callable} $case */
            $out[(string) $case->value] = method_exists($case, 'label') ? (string) $case->label() : (string) $case->value;
        }

        return $out;
    }
}
