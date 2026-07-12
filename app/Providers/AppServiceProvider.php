<?php

namespace App\Providers;

use App\Models\Branch;
use App\Support\SiteSettings;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('*', function ($view) {
            $view->with('siteSettings', SiteSettings::current());
            $view->with(
                'footerBranches',
                Schema::hasTable('branches')
                    ? Branch::query()->active()->orderBy('sort_order')->orderBy('id')->take(3)->get()
                    : collect()
            );
        });
    }
}
