<?php

namespace App\Support;

class FrontLang
{
    public static function get(): string
    {
        $requestedLang = request()?->query('lang');

        if (in_array($requestedLang, ['ar', 'en'], true)) {
            return $requestedLang;
        }

        return session('front_lang', 'ar');
    }

    public static function t(string $ar, string $en): string
    {
        return self::get() === 'en' ? $en : $ar;
    }

    public static function db(?string $ar, ?string $en): string
    {
        if (self::get() === 'en') {
            return $en ?: ($ar ?? '');
        }

        return $ar ?? '';
    }
}
