<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;

class SitemapController extends Controller
{
    public function index()
    {
        $urls = collect([

            /*
            |--------------------------------------------------------------------------
            | Home
            |--------------------------------------------------------------------------
            */
            [
                'loc' => route('home', ['lang' => 'ar']),
                'alternate_ar' => route('home', ['lang' => 'ar']),
                'alternate_en' => route('home', ['lang' => 'en']),
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'daily',
                'priority' => '1.0',
            ],

            /*
            |--------------------------------------------------------------------------
            | Menu
            |--------------------------------------------------------------------------
            */
            [
                'loc' => route('menu.index', ['lang' => 'ar']),
                'alternate_ar' => route('menu.index', ['lang' => 'ar']),
                'alternate_en' => route('menu.index', ['lang' => 'en']),
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'daily',
                'priority' => '0.9',
            ],

            /*
            |--------------------------------------------------------------------------
            | About
            |--------------------------------------------------------------------------
            */
            [
                'loc' => route('about', ['lang' => 'ar']),
                'alternate_ar' => route('about', ['lang' => 'ar']),
                'alternate_en' => route('about', ['lang' => 'en']),
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.5',
            ],

            /*
            |--------------------------------------------------------------------------
            | Contact
            |--------------------------------------------------------------------------
            */
            [
                'loc' => route('contact', ['lang' => 'ar']),
                'alternate_ar' => route('contact', ['lang' => 'ar']),
                'alternate_en' => route('contact', ['lang' => 'en']),
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.5',
            ],

        ]);

        /*
        |--------------------------------------------------------------------------
        | Categories
        |--------------------------------------------------------------------------
        */
        $categoryUrls = Category::query()
            ->where('is_active', 1)
            ->get()
            ->map(function ($category) {

                return [

                    'loc' => route('menu.category', [
                        $category,
                        'lang' => 'ar',
                    ]),

                    'alternate_ar' => route('menu.category', [
                        $category,
                        'lang' => 'ar',
                    ]),

                    'alternate_en' => route('menu.category', [
                        $category,
                        'lang' => 'en',
                    ]),

                    'lastmod' => optional($category->updated_at)->toAtomString()
                        ?? now()->toAtomString(),

                    'changefreq' => 'weekly',

                    'priority' => '0.8',
                ];
            });

        /*
        |--------------------------------------------------------------------------
        | Products
        |--------------------------------------------------------------------------
        */
        $productUrls = Product::query()
            ->where('is_active', 1)
            ->get()
            ->map(function ($product) {

                return [

                    'loc' => route('product.show', [
                        $product,
                        'lang' => 'ar',
                    ]),

                    'alternate_ar' => route('product.show', [
                        $product,
                        'lang' => 'ar',
                    ]),

                    'alternate_en' => route('product.show', [
                        $product,
                        'lang' => 'en',
                    ]),

                    'lastmod' => optional($product->updated_at)->toAtomString()
                        ?? now()->toAtomString(),

                    'changefreq' => 'weekly',

                    'priority' => '0.7',
                ];
            });

        $urls = $urls
            ->concat($categoryUrls)
            ->concat($productUrls);

        return response(
            view()->file(resource_path('views/sitemap.xml.blade.php'), compact('urls'))->render()
        )->header('Content-Type', 'application/xml');
    }
}
