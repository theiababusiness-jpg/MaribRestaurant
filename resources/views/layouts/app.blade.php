@php
    use App\Support\FrontLang;
    use App\Support\SeoData;

    $frontLang = FrontLang::get();
    $seoData = $seo ?? SeoData::make();
    $structuredDataBlocks = $structuredData ?? [];
    $siteName = FrontLang::db($siteSettings?->site_name, $siteSettings?->site_name_en) ?: FrontLang::t('مطاعم مأرب', 'Marib Restaurant');
    $contactPhones = implode(' - ', array_filter([
        $siteSettings?->support_phone,
        $siteSettings?->secondary_phone,
    ]));
    $socialLinks = array_values(array_filter([
        ['label' => 'Instagram', 'url' => $siteSettings?->instagram_url],
        ['label' => 'Facebook', 'url' => $siteSettings?->facebook_url],
        ['label' => 'X', 'url' => $siteSettings?->x_url],
        ['label' => 'TikTok', 'url' => $siteSettings?->tiktok_url],
    ], fn ($item) => filled($item['url'] ?? null)));
@endphp
<!DOCTYPE html>
<html lang="{{ $frontLang }}" dir="{{ $frontLang === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $seoData['title'] ?? $siteName }}</title>
    <meta name="description" content="{{ $seoData['description'] ?? '' }}">
    <meta name="robots" content="{{ $seoData['robots'] ?? 'index,follow' }}">
    <link rel="canonical" href="{{ $seoData['canonical'] ?? url()->current() }}">
    <link rel="icon" href="{{ asset('favicon.jpeg') }}" type="image/jpeg">

    @foreach(($seoData['alternates'] ?? []) as $hrefLang => $href)
        <link rel="alternate" hreflang="{{ $hrefLang }}" href="{{ $href }}">
    @endforeach

    <meta property="og:locale" content="{{ $seoData['locale'] ?? ($frontLang === 'en' ? 'en_US' : 'ar_SA') }}">
    <meta property="og:type" content="{{ $seoData['type'] ?? 'website' }}">
    <meta property="og:title" content="{{ $seoData['title'] ?? $siteName }}">
    <meta property="og:description" content="{{ $seoData['description'] ?? '' }}">
    <meta property="og:url" content="{{ $seoData['canonical'] ?? url()->current() }}">
    <meta property="og:site_name" content="{{ $seoData['site_name'] ?? $siteName }}">
    <meta property="og:image" content="{{ $seoData['image'] ?? asset('favicon.jpeg') }}">
    <meta name="twitter:card" content="{{ $seoData['twitter_card'] ?? 'summary_large_image' }}">
    <meta name="twitter:title" content="{{ $seoData['title'] ?? $siteName }}">
    <meta name="twitter:description" content="{{ $seoData['description'] ?? '' }}">
    <meta name="twitter:image" content="{{ $seoData['image'] ?? asset('favicon.jpeg') }}">

    @if($siteSettings?->search_console_verification)
        <meta name="google-site-verification" content="{{ $siteSettings->search_console_verification }}">
    @endif

    <link rel="stylesheet" href="{{ asset('css/variables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">

    @if($siteSettings?->ga_measurement_id)
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $siteSettings->ga_measurement_id }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ $siteSettings->ga_measurement_id }}');
        </script>
    @endif

    @if(! empty($structuredDataBlocks))
        <script type="application/ld+json">
            {!! json_encode($structuredDataBlocks, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
        </script>
    @endif
</head>
<body dir="{{ $frontLang === 'ar' ? 'rtl' : 'ltr' }}">
<header class="site-header">
    <div class="container">
        <div class="header-desktop">
            <nav class="nav">
                <div style="margin-inline-start:10px; display:flex; gap:8px; align-items:center;">
                    @if($frontLang === 'ar')
                        <a class="nav__link" href="{{ route('front.lang', 'en') }}">EN</a>
                    @else
                        <a class="nav__link" href="{{ route('front.lang', 'ar') }}">AR</a>
                    @endif
                    <button class="nav__link theme-toggle" type="button" style="border: none; cursor: pointer; font-family: inherit; display: inline-flex; align-items: center; justify-content: center; padding: 8px 12px; line-height: 1;">☾</button>
                </div>

                <a class="nav__link" href="{{ route('home') }}">{{ FrontLang::t('الرئيسية', 'Home') }}</a>
                <a class="nav__link" href="{{ route('menu.index') }}">{{ FrontLang::t('المنيو', 'Menu') }}</a>
                <a class="nav__link" href="{{ route('cart.index') }}">{{ FrontLang::t('السلة', 'Cart') }}</a>
                <a class="nav__link" href="{{ route('contact') }}">{{ FrontLang::t('تواصل معنا', 'Contact Us') }}</a>
                <a class="nav__link" href="{{ route('about') }}">{{ FrontLang::t('من نحن', 'About Us') }}</a>
            </nav>
        </div>

        <div class="header-mobile" style="justify-content: space-between;">
            <div style="display:flex; gap:8px; align-items:center;">
                <button id="themeToggle" class="theme-toggle nav__link" type="button" style="border: none; cursor: pointer; font-family: inherit; display: inline-flex; align-items: center; justify-content: center; padding: 8px 12px; line-height: 1;">☾</button>
                <a href="{{ route('cart.index') }}" class="nav__link cart-link" title="{{ FrontLang::t('السلة', 'Cart') }}">
                    🛒
                    @if(session('cart') && count(session('cart')) > 0)
                        <span class="cart-badge">{{ count(session('cart')) }}</span>
                    @endif
                </a>
            </div>
            <div>
                <button class="header-burger" type="button" onclick="toggleMobileMenu()" aria-label="menu">☰</button>
            </div>
        </div>
    </div>

    <div id="mobileOverlay" class="mobile-overlay" onclick="toggleMobileMenu()"></div>

    <nav id="mobileMenu" class="mobile-drawer" aria-hidden="true">
        <div class="mobile-drawer__head">
            <strong>{{ FrontLang::t('القائمة', 'Menu') }}</strong>
            <button class="mobile-drawer__close" type="button" onclick="toggleMobileMenu()">✕</button>
        </div>

        <div class="mobile-drawer__links">
            <a class="mobile-link" href="{{ route('front.lang', 'ar') }}">AR</a>
            <a class="mobile-link" href="{{ route('front.lang', 'en') }}">EN</a>
            <a class="mobile-link" href="{{ route('home') }}" onclick="toggleMobileMenu()">{{ FrontLang::t('الرئيسية', 'Home') }}</a>
            <a class="mobile-link" href="{{ route('menu.index') }}" onclick="toggleMobileMenu()">{{ FrontLang::t('المنيو', 'Menu') }}</a>
            <a class="mobile-link" href="{{ route('cart.index') }}" onclick="toggleMobileMenu()">{{ FrontLang::t('السلة', 'Cart') }}</a>
            <a class="mobile-link" href="{{ route('contact') }}" onclick="toggleMobileMenu()">{{ FrontLang::t('تواصل معنا', 'Contact Us') }}</a>
            <a class="mobile-link" href="{{ route('about') }}" onclick="toggleMobileMenu()">{{ FrontLang::t('من نحن', 'About Us') }}</a>
        </div>
    </nav>
</header>

@if(session('success'))
    <div id="flashMessage" class="flash-success">{{ session('success') }}</div>
@endif

<main class="main">
    <div class="container">
        @yield('content')
    </div>
</main>

<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-col footer-about">
            <h4>{{ FrontLang::t('عن مطاعم مأرب', 'About Marib Restaurant') }}</h4>
            <p>
                {{ FrontLang::t(
                    'مطاعم مأرب تقدم الأكلات الشعبية الأصيلة بخيارات طلب مرنة تشمل الاستلام من الفرع أو التوصيل مع أكثر من وسيلة دفع.',
                    'Marib Restaurant serves authentic Yemeni dishes with flexible ordering options including branch pickup, delivery, and multiple payment methods.'
                ) }}
            </p>
        </div>

        <div class="footer-col footer-branches">
            <h4>{{ FrontLang::t('فروعنا', 'Our Branches') }}</h4>

            @forelse($footerBranches as $branch)
                <div class="branch-box">
                    <h5>{{ FrontLang::db($branch->name, $branch->name_en) }}</h5>
                    @if($branch->address || $branch->address_en)
                        <p>{{ FrontLang::db($branch->address, $branch->address_en) }}</p>
                    @endif
                    @if($branch->phone || $branch->whatsapp_number)
                        <p class="branch-phones">{{ implode(' - ', array_filter([$branch->phone, $branch->whatsapp_number])) }}</p>
                    @endif
                </div>
            @empty
                <div class="branch-box">
                    <h5>{{ FrontLang::t('الفرع الرئيسي', 'Main Branch') }}</h5>
                    <p>{{ FrontLang::db($siteSettings?->primary_address, $siteSettings?->primary_address_en) ?: FrontLang::t('سيتم تحديث بيانات الفروع قريبًا.', 'Branch details will be updated soon.') }}</p>
                    @if($contactPhones)
                        <p class="branch-phones">{{ $contactPhones }}</p>
                    @endif
                </div>
            @endforelse
        </div>

        <div class="footer-col">
            <h4>{{ FrontLang::t('معلومات التواصل', 'Contact Info') }}</h4>
            <ul>
                @if($contactPhones)
                    <li>{{ $contactPhones }}</li>
                @endif
                @if($siteSettings?->whatsapp_number)
                    <li>{{ FrontLang::t('واتساب', 'WhatsApp') }}: {{ $siteSettings->whatsapp_number }}</li>
                @endif
                @if($siteSettings?->working_hours || $siteSettings?->working_hours_en)
                    <li>{{ FrontLang::db($siteSettings?->working_hours, $siteSettings?->working_hours_en) }}</li>
                @endif
            </ul>
        </div>

        <div class="footer-col">
            <h4>{{ FrontLang::t('روابط سريعة', 'Quick Links') }}</h4>
            <ul>
                <li><a href="{{ route('home') }}">{{ FrontLang::t('الرئيسية', 'Home') }}</a></li>
                <li><a href="{{ route('menu.index') }}">{{ FrontLang::t('المنيو', 'Menu') }}</a></li>
                <li><a href="{{ route('about') }}">{{ FrontLang::t('من نحن', 'About Us') }}</a></li>
                <li><a href="{{ route('contact') }}">{{ FrontLang::t('تواصل معنا', 'Contact Us') }}</a></li>
                <li><a href="{{ route('sitemap') }}">Sitemap</a></li>
                @if($siteSettings?->google_maps_url)
                    <li><a href="{{ $siteSettings->google_maps_url }}" target="_blank" rel="noopener">{{ FrontLang::t('الموقع على الخريطة', 'Open in Maps') }}</a></li>
                @endif
            </ul>

            @if(! empty($socialLinks))
                <div style="margin-top:12px; display:flex; gap:8px; flex-wrap:wrap;">
                    @foreach($socialLinks as $social)
                        <a class="btn btn--ghost" href="{{ $social['url'] }}" target="_blank" rel="noopener">{{ $social['label'] }}</a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="footer-bottom">© {{ now()->year }} {{ FrontLang::t('جميع الحقوق محفوظة', 'All rights reserved') }} | {{ $siteName }}</div>
</footer>

<script>
    (function () {
        const flash = document.getElementById('flashMessage');

        if (flash) {
            setTimeout(() => {
                flash.style.opacity = '0';
                flash.style.transition = 'opacity .4s ease';
                setTimeout(() => flash.remove(), 400);
            }, 3000);
        }

        const savedTheme = localStorage.getItem('theme');
        const isDark = savedTheme === 'dark';
        if (isDark) {
            document.body.classList.add('dark');
        }

        const themeToggles = document.querySelectorAll('.theme-toggle');
        // Set initial icon based on theme
        themeToggles.forEach(toggle => {
            toggle.textContent = isDark ? '☀️' : '☾';
        });

        themeToggles.forEach(toggle => {
            toggle.addEventListener('click', () => {
                document.body.classList.toggle('dark');
                const darkNow = document.body.classList.contains('dark');
                localStorage.setItem('theme', darkNow ? 'dark' : 'light');
                
                themeToggles.forEach(t => {
                    t.textContent = darkNow ? '☀️' : '☾';
                });
            });
        });
    })();

    function toggleMobileMenu() {
        const drawer = document.getElementById('mobileMenu');
        const overlay = document.getElementById('mobileOverlay');

        if (!drawer || !overlay) {
            return;
        }

        const isOpen = drawer.classList.contains('is-open');

        if (isOpen) {
            drawer.classList.remove('is-open');
            overlay.classList.remove('is-open');
            drawer.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('no-scroll');
        } else {
            drawer.classList.add('is-open');
            overlay.classList.add('is-open');
            drawer.setAttribute('aria-hidden', 'false');
            document.body.classList.add('no-scroll');
        }
    }
</script>
<script src="{{ asset('js/auto-translate.js') }}" defer></script>
<script src="{{ asset('js/front.js') }}"></script>
</body>
</html>
