<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Support\FrontLang;
use App\Support\SeoData;

class PageController extends Controller
{
    public function about()
    {
        $seo = SeoData::make([
            'title' => FrontLang::t('من نحن | مطاعم مأرب', 'About Us | Marib Restaurant'),
            'description' => FrontLang::t(
                'تعرف على قصة مطاعم مأرب ورؤيتنا ورسالتنا في تقديم الأكلات الشعبية الأصيلة في السعودية.',
                'Learn about the story, vision, and mission of Marib Restaurant in serving authentic Yemeni cuisine in Saudi Arabia.'
            ),
        ]);

        $structuredData = [[
            '@context' => 'https://schema.org',
            '@type' => 'Restaurant',
            'name' => FrontLang::t('مطاعم مأرب', 'Marib Restaurant'),
            'url' => route('about'),
        ]];

        return view('front.about', compact('seo', 'structuredData'));
    }
}
