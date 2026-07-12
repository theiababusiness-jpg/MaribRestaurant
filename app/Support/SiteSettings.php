<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SiteSettings
{
    public static function current(): ?SiteSetting
    {
        try {
            if (!Schema::hasTable('site_settings')) {
                return null;
            }

            return Cache::remember('site_settings.current', 300, function () {
                return SiteSetting::query()->first() ?? SiteSetting::query()->create(SiteSetting::defaults());
            });
        } catch (Throwable) {
            return null;
        }
    }

    public static function forget(): void
    {
        Cache::forget('site_settings.current');
    }
}
