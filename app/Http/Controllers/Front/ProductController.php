<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Support\FrontLang;
use App\Support\SeoData;

class ProductController extends Controller
{
    public function show(Product $product)
    {
        $product->load([
            'category',
            'optionGroups.options',
        ]);

        $seo = SeoData::make([
            'title' => FrontLang::db(
                $product->seo_title ?: $product->name,
                $product->seo_title_en ?: ($product->name_en ?: $product->name)
            ),
            'description' => FrontLang::db(
                $product->seo_description ?: ($product->description ?: 'تفاصيل المنتج والمكونات وخيارات التخصيص.'),
                $product->seo_description_en ?: ($product->description_en ?: 'Product details, ingredients, and customization options.')
            ),
            'type' => 'product',
            'image' => $product->image_path ? asset($product->image_path) : asset('favicon.jpeg'),
        ]);

        $structuredData = [
            [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    [
                        '@type' => 'ListItem',
                        'position' => 1,
                        'name' => FrontLang::t('الرئيسية', 'Home'),
                        'item' => route('home'),
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 2,
                        'name' => FrontLang::t('المنيو', 'Menu'),
                        'item' => route('menu.index'),
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 3,
                        'name' => FrontLang::db($product->category?->name, $product->category?->name_en),
                        'item' => $product->category ? route('menu.category', $product->category) : route('menu.index'),
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 4,
                        'name' => FrontLang::db($product->name, $product->name_en),
                        'item' => route('product.show', $product),
                    ],
                ],
            ],
            [
                '@context' => 'https://schema.org',
                '@type' => 'Product',
                'name' => FrontLang::db($product->name, $product->name_en),
                'description' => FrontLang::db($product->description, $product->description_en),
                'image' => $seo['image'],
                'sku' => (string) $product->id,
                'category' => FrontLang::db($product->category?->name, $product->category?->name_en),
                'offers' => [
                    '@type' => 'Offer',
                    'priceCurrency' => 'SAR',
                    'price' => (float) $product->price,
                    'availability' => $product->is_active
                        ? 'https://schema.org/InStock'
                        : 'https://schema.org/OutOfStock',
                    'url' => route('product.show', $product),
                ],
            ],
        ];

        return view('front.product', compact('product', 'seo', 'structuredData'));
    }
}
