<?php

namespace App\Support;

class SeoData
{
    public static function make(array $overrides = []): array
    {
        $settings = SiteSettings::current();
        $lang = FrontLang::get();
        $siteName = FrontLang::db(
            $settings?->site_name ?? SiteSettingDefaults::SITE_NAME_AR,
            $settings?->site_name_en ?? SiteSettingDefaults::SITE_NAME_EN
        );

        $defaultDescription = FrontLang::db(
            $settings?->default_meta_description ?? SiteSettingDefaults::DEFAULT_DESCRIPTION_AR,
            $settings?->default_meta_description_en ?? SiteSettingDefaults::DEFAULT_DESCRIPTION_EN
        );

        $canonical = $overrides['canonical'] ?? self::currentUrlForLang($lang);
        $image = $overrides['image'] ?? ($settings?->logo_path ? asset($settings->logo_path) : asset('favicon.jpeg'));

        return [
            'title' => $overrides['title'] ?? $siteName,
            'description' => $overrides['description'] ?? $defaultDescription,
            'canonical' => $canonical,
            'type' => $overrides['type'] ?? 'website',
            'image' => $image,
            'robots' => $overrides['robots'] ?? 'index,follow',
            'alternates' => $overrides['alternates'] ?? [
                'ar' => self::currentUrlForLang('ar'),
                'en' => self::currentUrlForLang('en'),
                'x-default' => self::currentUrlForLang('ar'),
            ],
            'site_name' => $siteName,
            'locale' => $lang === 'en' ? 'en_US' : 'ar_SA',
            'twitter_card' => $overrides['twitter_card'] ?? 'summary_large_image',
        ];
    }

    public static function currentUrlForLang(string $lang): string
    {
        $settings = SiteSettings::current();

        /*
        |--------------------------------------------------------------------------
        | Base URL
        |--------------------------------------------------------------------------
        */
        $baseUrl = rtrim(
            $settings?->canonical_base_url
                ?: config('app.url')
                ?: url('/'),
            '/'
        );

        /*
        |--------------------------------------------------------------------------
        | Current path
        |--------------------------------------------------------------------------
        */
        $path = request()->getPathInfo();

        $url = $path === '/'
            ? $baseUrl
            : $baseUrl . $path;

        /*
        |--------------------------------------------------------------------------
        | Clean query params
        |--------------------------------------------------------------------------
        |
        | ممنوع تدخل هذه القيم داخل canonical
        | لأنها تصنع صفحات بحث/فلترة قابلة للفهرسة
        |
        */
        $blockedParams = [
            'q',
            'search',
            'page',
            'sort',
            'filter',
        ];

        $query = request()->query();

        foreach ($blockedParams as $blocked) {
            unset($query[$blocked]);
        }

        /*
        |--------------------------------------------------------------------------
        | Add language query
        |--------------------------------------------------------------------------
        */
        $query['lang'] = $lang;

        return empty($query)
            ? $url
            : $url . '?' . http_build_query($query);
    }
}
