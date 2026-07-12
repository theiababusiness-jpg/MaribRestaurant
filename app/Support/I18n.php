<?php

namespace App\Support;

use Illuminate\Support\Facades\Session;

class I18n
{
    /**
     * t:
     * يحاول يقرأ قيمة مترجمة من نص JSON داخل نفس الحقل.
     * - لو النص مش JSON يرجعه كما هو.
     * - لو JSON يرجع ar/en حسب السيشن.
     */
    public static function t(?string $value, string $fallback = ''): string
    {
        // value: النص الأصلي من DB
        if ($value === null || trim($value) === '') {
            return $fallback;
        }

        // locale: اللغة الحالية من السيشن
        $locale = Session::get('locale', 'ar');

        // نجرب نفك JSON
        $decoded = json_decode($value, true);

        // لو مش JSON صالح
        if (!is_array($decoded)) {
            return $value;
        }

        // لو JSON: نرجع حسب اللغة، ثم fallback
        return $decoded[$locale]
            ?? $decoded['ar']
            ?? $decoded['en']
            ?? $fallback;
    }
}
