<?php

declare(strict_types=1);

namespace App\Services\Helpers;

class Phone
{
    private const PHONE_REGEX = '/\+?\d[\d\s()\-]{7,25}\d/';

    /**
     * @return string[]
     */
    public static function getAllRawFromText(string $text): array
    {
        $text = trim($text);
        $matches = [];

        preg_match_all(self::PHONE_REGEX, $text, $matches);
        $phones = $matches[0] ?? [];

        array_walk($phones, fn (string &$phone) => trim($phone));

        return $phones;
    }

    /**
     * @return string[]
     */
    public static function getAllNormalizedFromText(string $text): array
    {
        $normalized = array_map(
            fn (string $phone) => preg_replace('/\D/', '', $phone),
            self::getAllRawFromText($text),
        );

        return array_values(array_unique($normalized));
    }

    public static function getFirstFromText(string $text): ?string
    {
        return self::getAllNormalizedFromText($text)[0] ?? null;
    }
}
