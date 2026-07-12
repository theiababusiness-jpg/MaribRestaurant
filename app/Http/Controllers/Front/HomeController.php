<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use App\Support\FrontLang;
use App\Support\SeoData;
use App\Support\SiteSettings;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $catSlug = trim((string) $request->query('cat', ''));
        $siteSettings = SiteSettings::current();

        $categories = Category::query()
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();

        $productsQuery = Product::query()
            ->where('is_active', 1)
            ->with(['category', 'optionGroups.options'])
            ->orderBy('sort_order')
            ->orderByDesc('id');

        if ($catSlug !== '') {
            $productsQuery->whereHas('category', fn ($query) => $query->where('slug', $catSlug));
        }

        if ($q !== '') {
            $productsQuery->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('name_en', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('description_en', 'like', "%{$q}%");
            });
        }

        $products = $productsQuery->take(12)->get();
        $banners = Banner::with('product')->active()->orderBy('sort_order')->get();

        $resolveImageUrl = function (?string $path): ?string {
            if (!filled($path)) {
                return null;
            }

            return asset(str_starts_with($path, 'banners/') ? 'storage/' . $path : $path);
        };

        $heroImage = $resolveImageUrl($banners->first()?->image_path) ?: asset('favicon.jpeg');

        $faqItems = [
            [
                'question' => FrontLang::t('أين يقع مطعم مأرب في الدمام؟', 'Where is Marib Restaurant located in Dammam?'),
                'answer' => FrontLang::t(
                    'يقع المطعم في حي السلام بالدمام، ويمكنك من الموقع الوصول بسرعة إلى المنيو وبيانات التواصل.',
                    'The restaurant is in Al Salam District, Dammam, and the site gives you quick access to the menu and contact details.'
                ),
            ],
            [
                'question' => FrontLang::t('ما الأكلات التي يشتهر بها مطعم مأرب؟', 'What dishes is Marib Restaurant known for?'),
                'answer' => FrontLang::t(
                    'كثير من الزوار يبدأون بالمندي أو الفحسة أو السلتة، ثم يكملون التصفح من المنيو حسب الذوق.',
                    'Many visitors start with mandi, fahsa, or saltah, then continue browsing the menu based on taste.'
                ),
            ],
            [
                'question' => FrontLang::t('هل يمكنني تصفح المنيو قبل الطلب؟', 'Can I browse the menu before ordering?'),
                'answer' => FrontLang::t(
                    'نعم، المنيو مرتب بشكل واضح لتنتقل من القسم إلى الطبق ثم إلى التفاصيل بسهولة.',
                    'Yes. The menu is organized clearly so you can move from category to dish to details with ease.'
                ),
            ],
        ];

        $seo = SeoData::make([
            'title' => FrontLang::t(
                'مطعم مأرب | مطعم يمني في الدمام حي السلام',
                'Marib Restaurant | Yemeni Restaurant in Dammam Al Salam'
            ),
            'description' => FrontLang::t(
                'مطعم مأرب في الدمام حي السلام يقدم أكلات يمنية شعبية مثل المندي والفحسة والسلتة مع منيو واضح وخيارات طلب سهلة.',
                'Marib Restaurant in Dammam Al Salam serves Yemeni dishes like mandi, fahsa, and saltah with a clear menu and easy ordering.'
            ),
            'image' => $heroImage,
        ]);

        $structuredData = [
            [
                '@context' => 'https://schema.org',
                '@type' => 'Restaurant',
                'name' => FrontLang::t('مطعم مأرب', 'Marib Restaurant'),
                'url' => route('home'),
                'telephone' => $siteSettings?->support_phone,
                'image' => $seo['image'],
                'servesCuisine' => ['Yemeni', 'Middle Eastern'],
                'hasMenu' => route('menu.index'),
                'areaServed' => [
                    '@type' => 'City',
                    'name' => FrontLang::t('الدمام', 'Dammam'),
                ],
                'address' => [
                    '@type' => 'PostalAddress',
                    'streetAddress' => FrontLang::db($siteSettings?->primary_address, $siteSettings?->primary_address_en),
                    'addressLocality' => 'Dammam',
                    'addressRegion' => FrontLang::t('المنطقة الشرقية', 'Eastern Province'),
                    'addressCountry' => 'SA',
                ],
                'sameAs' => array_values(array_filter([
                    $siteSettings?->instagram_url,
                    $siteSettings?->facebook_url,
                    $siteSettings?->x_url,
                    $siteSettings?->tiktok_url,
                ])),
            ],
            [
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => FrontLang::t('مطعم مأرب', 'Marib Restaurant'),
                'url' => route('home'),
                'potentialAction' => [
                    '@type' => 'SearchAction',
                    'target' => route('menu.index') . '?q={search_term_string}',
                    'query-input' => 'required name=search_term_string',
                ],
            ],
            [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => array_map(static function (array $item) {
                    return [
                        '@type' => 'Question',
                        'name' => $item['question'],
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => $item['answer'],
                        ],
                    ];
                }, $faqItems),
            ],
        ];

        return view('front.home', compact(
            'categories',
            'products',
            'banners',
            'faqItems',
            'catSlug',
            'q',
            'seo',
            'structuredData'
        ));
    }
}
