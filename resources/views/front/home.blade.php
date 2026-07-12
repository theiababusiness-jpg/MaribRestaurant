@extends('layouts.app')

@section('content')

@php
use App\Support\FrontLang;
use Carbon\Carbon;
@endphp


<!-- =========================
     HERO (خلفية + ترحيب + بحث)
     ========================= --><section class="hero dark-section">
    <div class="hero__overlay"></div>

    <div class="hero__content">

        <p class="hero__badge">
            {{ FrontLang::t('مطعم مأرب • حي السلام • الدمام', 'Marib Restaurant • Al Salam • Dammam') }}
        </p>

        <h1 class="hero__title">
            {{ FrontLang::t('أكلات يمنية بطابع أصيل', 'Authentic Yemeni Cuisine') }}
        </h1>

        <p class="hero__subtitle">
            {{ FrontLang::t(
                'من المندي إلى الفحسة والسلتة، يقدّم مطعم مأرب تجربة يمنية شعبية واضحة وسهلة تساعدك تصل لطلبك بسرعة.',
                'From mandi to fahsa and saltah, Marib Restaurant offers a simple Yemeni dining experience designed to help you reach your order quickly.'
            ) }}
        </p>

        <!-- البحث -->
        <form class="searchbar" action="/menu" method="GET">
            <input
                class="searchbar__input dark-input"
                type="text"
                name="q"
                placeholder="{{ FrontLang::t('ابحث عن طبق', 'Search for a dish') }}"
            >

            <button class="searchbar__btn dark-btn" type="submit">
                {{ FrontLang::t('بحث', 'Search') }}
            </button>
        </form>

        <div class="hero__actions">
            <a class="btn dark-btn" href="{{ route('menu.index') }}">
                {{ FrontLang::t('عرض المنيو', 'View Menu') }}
            </a>
        </div>

    </div>
</section>
<div class="section-head value-card">

    <h2 class="section-title dark-text">
        {{ FrontLang::t('نكهات يمنية بطابع معروف', 'Yemeni flavors with an authentic touch') }}
    </h2>

    <p class="section-description">
        {{ FrontLang::t(
            'من المندي إلى الفحسة والسلتة، يقدّم مطعم مأرب أكلات يمنية شعبية بطابع بسيط يساعدك تصل لطلبك بسرعة بدون تعقيد.',
            'From mandi to fahsa and saltah, Marib Restaurant serves authentic Yemeni dishes in a simple and clear experience.'
        ) }}
    </p>

</div>

{{-- ==================================
    SLIDER (عروض ديناميكية من لوحة المدير)
================================== --}}
@php
    $banners = \App\Models\Banner::with('product')
        ->active()
        ->orderBy('sort_order')
        ->get();
@endphp


@if($banners->count() > 0)
<section style="margin-top:16px;" class="dark-section">

    <div class="section-head">
        <h2 class="section-title dark-text">
            {{ FrontLang::t('عروض مميزة', 'Offers & Features') }}
        </h2>
    </div>

    <!-- السلايدر -->
    <div class="slider" id="heroSlider" aria-label="{{ FrontLang::t('عروض مطعم مأرب', 'Marib Restaurant Offers') }}">
         @foreach($banners as $i => $banner)

            @php
                $bannerLink = null;

                if (($banner->link_type ?? 'none') === 'menu') {
                    $bannerLink = route('menu.index');
                } elseif (($banner->link_type ?? 'none') === 'product') {
                    if (!empty($banner->product) && !empty($banner->product->slug)) {
                        $bannerLink = route('product.show', $banner->product->slug);
                    }
                }
            @endphp

            @if($bannerLink)
                <div style="margin-top:10px;">
                    <a class="btn " href="{{ $bannerLink }}">
                        {{ !empty($banner->link_text)
                            ? $banner->link_text
                            : FrontLang::t('المزيد', 'More') }}
                    </a>
                </div>
            @endif

            <div class="slide {{ $i === 0 ? 'active' : '' }}">
                <img
                src="{{ $banner->image_path
                    ? asset(str_starts_with($banner->image_path, 'banners/')
                        ? 'storage/'.$banner->image_path
                        : $banner->image_path)
                    : '' }}"
                alt="{{ $banner->title }}"
                loading="lazy"
            >


                <div class="slide__caption">
                    <h2 >{{ $banner->title }}</h2>

                    @if(!empty($banner->subtitle))
                        <p>{{ $banner->subtitle }}</p>
                    @endif

                    @if($bannerLink)
                        <div style="margin-top:10px;">
                            <a class="btn dark-btn" href="{{ $bannerLink }}">
                                {{ $banner->link_text ?: FrontLang::t('المزيد', 'More') }}
                            </a>
                        </div>
                    @endif

                </div>
            </div>
        @endforeach
    </div>

    <div
        class="promo__dots"
        id="heroDots"
        style="margin-top:10px; display:flex; gap:8px; justify-content:center;"
    ></div>

</section>
@endif
<!-- سلايدر جافاسكريبت -->

<script>
/*
  سلايدر بسيط:
  - تقليب تلقائي
  - سحب بالجوال يمين/يسار
  - نقاط (Dots) للتنقل
*/
(function () {
  const slider = document.getElementById('heroSlider');            // slider: عنصر السلايدر الرئيسي
  const dotsWrap = document.getElementById('heroDots');            // dotsWrap: حاوية النقاط
  if (!slider) return;

  const slides = Array.from(slider.querySelectorAll('.slide'));    // slides: كل الشرائح
  if (slides.length === 0) return;

  let index = 0;          // index: رقم السلايد الحالي
  let startX = 0;         // startX: بداية السحب بالجوال
  let dragging = false;   // dragging: هل المستخدم يسحب حاليا؟
  let timer = null;       // timer: مؤقت التقليب التلقائي

  // makeDot: ينشئ نقطة واحدة
  function makeDot(i){
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.setAttribute('aria-label', 'انتقل إلى العرض رقم ' + (i+1));
    btn.style.width = '10px';
    btn.style.height = '10px';
    btn.style.borderRadius = '999px';
    btn.style.border = '0';
    btn.style.cursor = 'pointer';
    btn.style.opacity = '0.45';
    btn.style.background = 'var(--primary-color)';
    btn.addEventListener('click', () => {
      showSlide(i);
      startAuto();
    });
    return btn;
  }

  // updateDots: يحدث شكل النقاط
  function updateDots(){
    if (!dotsWrap) return;
    const dots = dotsWrap.querySelectorAll('button');
    dots.forEach((d, i) => {
      d.style.opacity = (i === index) ? '1' : '0.45';
      d.style.transform = (i === index) ? 'scale(1.15)' : 'scale(1)';
    });
  }

  // showSlide: يعرض سلايد واحد ويخفي الباقي
  function showSlide(i){
    slides.forEach((s, k) => s.classList.toggle('active', k === i));
    index = i;
    updateDots();
  }

  // next/prev: تنقل
  function next(){ showSlide((index + 1) % slides.length); }
  function prev(){ showSlide((index - 1 + slides.length) % slides.length); }

  // startAuto/stopAuto: تقليب تلقائي
  function startAuto(){
    stopAuto();
    timer = setInterval(next, 4500);
  }
  function stopAuto(){
    if (timer) clearInterval(timer);
    timer = null;
  }

  // إنشاء النقاط حسب عدد السلايدات
  if (dotsWrap){
    dotsWrap.innerHTML = '';
    slides.forEach((_, i) => dotsWrap.appendChild(makeDot(i)));
  }

  // Touch events (سحب للجوال)
  slider.addEventListener('touchstart', (e) => {
    dragging = true;
    startX = e.touches[0].clientX;
    stopAuto();
  }, { passive: true });

  slider.addEventListener('touchend', (e) => {
    if (!dragging) return;
    dragging = false;

    const endX = e.changedTouches[0].clientX;
    const diff = endX - startX;

    // لو السحب أكثر من 40px نعتبره تنقل
    if (Math.abs(diff) > 40) {
      if (diff < 0) next();
      else prev();
    }
    startAuto();
  }, { passive: true });

  // تشغيل أولي
  showSlide(0);
  startAuto();
})();
</script>


<div class="section-head">

    <h2 class="section-title dark-text">
        {{ FrontLang::t('ابدأ من القسم الأقرب لذوقك', 'Start with the category that matches your taste') }}
    </h2>

    <p class="section-description">
        {{ FrontLang::t(
            'تصفح أقسام المنيو بسرعة ثم انتقل مباشرة إلى الأطباق والتفاصيل.',
            'Browse menu categories quickly and jump directly to dishes and details.'
        ) }}
    </p>

</div>
{{-- شريط تصنيفات --}}
<div style="margin-top:14px;" class="dark-section">
    <div style="display:flex; gap:10px; overflow:auto; padding-bottom:6px; -webkit-overflow-scrolling:touch;">

        <a class="btn dark-btn" href="{{ route('menu.index') }}">
            {{ FrontLang::t('عرض المنيو', 'View Menu') }}
        </a>

        @foreach($categories as $cat)
            <a
                class="chip {{ ($catSlug === $cat->slug) ? 'chip--active' : '' }} btn dark-chip"
                href="{{ route('home', ['cat' => $cat->slug]) }}#home-products"
            >
                {{ FrontLang::db($cat->name, $cat->name_en) }}
            </a>
        @endforeach

    </div>
</div>

{{-- منتجات --}}
<div id="home-products" style="margin-top:14px;" class="dark-section">
    <h2 class="page-title dark-text" style="margin-bottom:6px;">
        {{ FrontLang::t('أشهر الأطباق', 'Popular Dishes') }}
    </h2>

    <p class="page-subtitle dark-text" style="margin-top:0;">
        @if(!empty($q))
            {{ FrontLang::t('نتائج البحث عن:', 'Search results for:') }} "{{ $q }}"
        @elseif(!empty($catSlug))
            {{ FrontLang::t('منتجات التصنيف المحدد', 'Selected category products') }}
        @else
            {{ FrontLang::t('اختيارات سريعة من المنيو.', 'Quick menu selections.') }}
        @endif
    </p>

    @if($products->count() === 0)
        <p class="page-subtitle dark-text">
            {{ FrontLang::t('لا توجد منتجات مطابقة حاليا.', 'No matching products found.') }}
        </p>
    @else
        <div class="grid" style="margin-top:12px;">
            @foreach($products as $p)
                <div class="card dark-card">
                    @if(!empty($p->image_path))
                        <img
                            src="{{ asset($p->image_path) }}"
                            alt="{{ $p->name }}"
                            style="width:100%; height:160px; object-fit:cover; border-radius:14px; margin-bottom:10px;"
                            loading="lazy"
                        >
                    @endif

                    <h4 class="card__title dark-text" style="margin:0 0 6px 0;">
                        {{ FrontLang::db($p->name, $p->name_en) }}
                    </h4>

                    

                  <div class="card__price dark-text">

                    @if($p->price > 0)
                    
                        {{ number_format((float)$p->price, 0) }} {{ FrontLang::t('ريال', 'SAR') }}
                    
                    @elseif(!$p->has_special_message && $p->optionGroups->count() && $p->optionGroups->first()->options->count())
                    
                        {{ number_format((float)$p->optionGroups->first()->options->first()->price_delta, 0) }}
                        {{ FrontLang::t('ريال', 'SAR') }}
                    
                    @else
                    
                        {{ FrontLang::t('السعر عند الطلب', 'Ask cashier') }}
                    
                    @endif
                    
                </div>

                    <div style="margin-top:12px; display:flex; gap:10px; flex-wrap:wrap;">
                        <a class="btn btn--small dark-btn" href="{{ route('product.show', $p) }}" style="min-width:auto;">
                            {{ FrontLang::t('عرض التفاصيل', 'View Details') }}
                        </a>

                        <a class="btn btn--small btn--ghost dark-btn-ghost" href="{{ route('menu.index') }}" style="min-width:auto;">
                            {{ FrontLang::t('المزيد', 'More') }}
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>


@endsection

