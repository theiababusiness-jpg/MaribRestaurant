<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Support\FrontLang;
use App\Support\SeoData;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        return $this->renderPage($request);
    }

    public function show(Request $request, Category $category)
    {
        return $this->renderPage($request, $category);
    }

    protected function renderPage(Request $request, ?Category $selectedCategory = null)
    {
        $q = trim((string) $request->query('q', ''));

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

        $activeCategorySlug = null;

        if ($q !== '') {
            $productsQuery->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('name_en', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('description_en', 'like', "%{$q}%");
            });
        } else {
            $selectedCategory = $selectedCategory ?: $categories->first();

            if ($selectedCategory) {
                $activeCategorySlug = $selectedCategory->slug;
                $productsQuery->where('category_id', $selectedCategory->id);
            }
        }

        $products = $productsQuery->get();

        $seoTitle = $selectedCategory
            ? FrontLang::db(
                $selectedCategory->seo_title ?: $selectedCategory->name,
                $selectedCategory->seo_title_en ?: ($selectedCategory->name_en ?: $selectedCategory->name)
            )
            : FrontLang::t('المنيو | مطاعم مأرب', 'Menu | Marib Restaurant');

        $seoDescription = $selectedCategory
            ? FrontLang::db(
                $selectedCategory->seo_description ?: ($selectedCategory->description ?: 'استعرض أطباق هذا التصنيف في مطاعم مأرب.'),
                $selectedCategory->seo_description_en ?: ($selectedCategory->description_en ?: 'Browse dishes in this category at Marib Restaurant.')
            )
            : FrontLang::t(
                'استعرض منيو مطاعم مأرب بالكامل وابحث عن الأكلات الشعبية والأصناف المتاحة.',
                'Browse the full Marib Restaurant menu and explore available Yemeni dishes.'
            );

        $seo = SeoData::make([
            'title' => $seoTitle,
            'description' => $seoDescription,
            /*
            |--------------------------------------------------------------------------
            | Prevent indexing search/filter pages
            |--------------------------------------------------------------------------
            */
            'robots' => $q !== ''
                ? 'noindex,follow'
                : 'index,follow',
        ]);

        $structuredData = [[
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_values(array_filter([
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
                $selectedCategory ? [
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => FrontLang::db($selectedCategory->name, $selectedCategory->name_en),
                    'item' => route('menu.category', $selectedCategory),
                ] : null,
            ])),
        ]];

        return view('front.menu', compact(
            'q',
            'categories',
            'products',
            'activeCategorySlug',
            'selectedCategory',
            'seo',
            'structuredData'
        ));
    }
}
