@extends('layouts.app')

@php
    use App\Support\FrontLang;

    $sharedSiteSettings = $siteSettings ?? null;

    $aboutMapEmbedUrl = $sharedSiteSettings?->google_maps_embed_url
        ?: 'https://www.google.com/maps?q=26.44908077907955,50.09990773653787&z=16&output=embed';
    $aboutMapOpenUrl = $sharedSiteSettings?->google_maps_url
        ?: 'https://www.google.com/maps?q=26.44908077907955,50.09990773653787';
@endphp

@section('content')

{{-- AOS Library --}}
<link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">
<style>

/* ============================= */
/* ABOUT PAGE */
/* ============================= */

.about-modern{
    padding:80px 20px;
    color:var(--text);
}

.about-section{
    margin-bottom:100px;
}

.about-title{
    font-size:32px;
    font-weight:700;
    margin-bottom:20px;
    color:var(--text);
}

.about-text{
    font-size:17px;
    line-height:1.9;
    max-width:900px;
    color:var(--text-muted);
}


/* ============================= */
/* GRID */
/* ============================= */

.about-grid-modern{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
    gap:30px;
    margin-top:40px;
}


/* ============================= */
/* VALUE CARD */
/* ============================= */

.value-card{
    background:var(--card);
    padding:30px;
    border-radius:var(--radius);
    border:1px solid var(--border);
    transition:.35s ease;
}

.value-card h3{
    color:var(--text);
    margin-bottom:12px;
    font-size:20px;
}

.value-card p{
    color:var(--text-muted);
}

.value-card:hover{
    transform:translateY(-6px);
    border-color:var(--brand);
    box-shadow:var(--shadow);
}


/* ============================= */
/* TIMELINE */
/* ============================= */

.timeline{
    border-left:3px solid var(--brand);
    padding-left:30px;
    margin-top:40px;
}

.timeline-item{
    margin-bottom:40px;
    position:relative;
}

.timeline-item::before{
    content:'';
    position:absolute;
    left:-39px;
    top:5px;
    width:16px;
    height:16px;
    background:var(--brand);
    border-radius:50%;
}


/* ============================= */
/* BUTTON GROUP */
/* ============================= */

.about-cta{
    margin-top:50px;
    display:flex;
    gap:20px;
    flex-wrap:wrap;
}


/* ============================= */
/* MAP */
/* ============================= */

.about-map{
    margin-top:60px;
}

.map-frame-responsive{
    position:relative;
    width:100%;
    padding-top:56.25%;
    border-radius:var(--radius);
    overflow:hidden;
    box-shadow:var(--shadow);
    border:1px solid var(--border);
}

.map-frame-responsive iframe{
    position:absolute;
    top:0;
    left:0;
    width:100%;
    height:100%;
    border:0;
}

.about-actions{
    margin-top:16px;
}


/* ============================= */
/* RESPONSIVE */
/* ============================= */

@media(max-width:768px){

    .about-modern{
        padding:60px 18px;
    }

    .about-title{
        font-size:24px;
    }

    .about-section{
        margin-bottom:70px;
    }

}

</style>

<div class="about-modern">

    {{-- HERO --}}
    <section class="about-section" data-aos="fade-up">
        <h1 class="about-title">
            {{ FrontLang::t('مطعم مأرب', 'Marib Restaurant') }}
        </h1>

        <p class="about-text">
            {{ FrontLang::t(
                'نقدم أكلات شعبية أصيلة، مع منيو واضح وخيارات تخصيص سهلة، وتجربة طلب سريعة.',
                'We offer authentic Yemeni food, a clear menu, easy customization options, and a fast ordering experience.'
            ) }}
        </p>

        <div class="about-cta">
            <a class="btn btn--ghost dark-btn-ghost" href="{{ route('contact') }}">
                {{ FrontLang::t('تواصل معنا', 'Contact Us') }}
            </a>
            <a class="btn dark-btn" href="/menu">
                {{ FrontLang::t('عرض المنيو', 'View Menu') }}
            </a>
        </div>
    </section>


    {{-- الرؤية --}}
    <section class="about-section" data-aos="fade-up">
        <h2 class="about-title">
            {{ FrontLang::t('رؤيتنا', 'Our Vision') }}
        </h2>

        <p class="about-text">
            {{ FrontLang::t(
                'أن نكون روّاد تقديم الأكلات الشعبية في السعودية والوطن العربي، وأن نحافظ على أصالة المذاق التراثي ونقدّمه بأسلوب عصري يليق بتوقعات عملائنا، حتى يصبح مطاعم مأرب اسماً مرتبطاً بالجودة والطعم الأصيل والثقة في كل وجبة تُقدَّم.',
                'To become pioneers in serving traditional dishes in Saudi Arabia and the Arab world, preserving authentic heritage flavors while presenting them in a modern style that meets customer expectations, making Marib Restaurants a name associated with quality, authentic taste, and trust in every meal served.'
            ) }}
        </p>
    </section>


    {{-- الرسالة --}}
    <section class="about-section" data-aos="fade-up">
        <h2 class="about-title">
            {{ FrontLang::t('رسالتنا', 'Our Mission') }}
        </h2>

        <p class="about-text">
            {{ FrontLang::t(
                'تقديم أشهى الأكلات الشعبية ذات القيم الغذائية المتكاملة باستخدام أجود المكونات الطازجة، لإشباع رغبات عملائنا وتقديم تجربة طعام أصيلة تجمع بين الجودة والمذاق المميز والخدمة الراقية.',
                'To serve the most delicious traditional dishes with complete nutritional value using the finest fresh ingredients, satisfying our customers and delivering an authentic dining experience that combines quality, distinctive taste, and premium service.'
            ) }}
        </p>
    </section>


    {{-- القيم --}}
    <section class="about-section" data-aos="fade-up">
        <h2 class="about-title">
            {{ FrontLang::t('قيمنا', 'Our Values') }}
        </h2>

        <div class="about-grid-modern">

            <div class="value-card" data-aos="zoom-in">
                <h3>{{ FrontLang::t('الأصالة', 'Authenticity') }}</h3>
                <p>{{ FrontLang::t('نحافظ على نكهة الأكلات الشعبية كما عُرفت عبر الأجيال.', 'We preserve traditional flavors as known across generations.') }}</p>
            </div>

            <div class="value-card" data-aos="zoom-in" data-aos-delay="100">
                <h3>{{ FrontLang::t('الجودة', 'Quality') }}</h3>
                <p>{{ FrontLang::t('نستخدم أجود المكونات الطازجة ونلتزم بأعلى معايير التحضير.', 'We use the finest fresh ingredients and follow top preparation standards.') }}</p>
            </div>

            <div class="value-card" data-aos="zoom-in" data-aos-delay="200">
                <h3>{{ FrontLang::t('النظافة والسلامة', 'Hygiene & Safety') }}</h3>
                <p>{{ FrontLang::t('نطبق أعلى معايير النظافة وسلامة الغذاء.', 'We apply the highest food safety and hygiene standards.') }}</p>
            </div>

            <div class="value-card" data-aos="zoom-in" data-aos-delay="300">
                <h3>{{ FrontLang::t('الالتزام والصدق', 'Commitment & Integrity') }}</h3>
                <p>{{ FrontLang::t('نلتزم بالشفافية وتقديم قيمة حقيقية.', 'We maintain transparency and deliver real value.') }}</p>
            </div>

            <div class="value-card" data-aos="zoom-in" data-aos-delay="400">
                <h3>{{ FrontLang::t('رضا العميل أولاً', 'Customer First') }}</h3>
                <p>{{ FrontLang::t('نسعى لتقديم تجربة طعام تجعل عملاءنا يعودون إلينا بثقة.', 'We aim to provide a dining experience that earns lasting trust.') }}</p>
            </div>

        </div>
    </section>


    {{-- قصتنا --}}
    <section class="about-section" data-aos="fade-up">
        <h2 class="about-title">
            {{ FrontLang::t('قصتنا', 'Our Story') }}
        </h2>

        <div class="timeline">

            <div class="timeline-item">
                <p class="about-text">
                    {{ FrontLang::t(
                        'منذ عام 2017 بدأت مطاعم مأرب رحلتها باستقبال ضيوفها في أول فروعها، حاملةً شغفاً حقيقياً بتقديم الأكلات الشعبية الأصيلة.',
                        'Since 2017, Marib Restaurants began welcoming guests with a true passion for authentic traditional cuisine.'
                    ) }}
                </p>
            </div>

            <div class="timeline-item">
                <p class="about-text">
                    {{ FrontLang::t(
                        'ومع الثقة التي منحنا إياها عملاؤنا، توسعت مطاعم مأرب لتصبح سلسلة مطاعم معروفة في المملكة العربية السعودية.',
                        'With the trust of our customers, we expanded into a recognized restaurant chain in Saudi Arabia.'
                    ) }}
                </p>
            </div>

            <div class="timeline-item">
                <p class="about-text">
                    {{ FrontLang::t(
                        'واليوم نواصل رحلتنا بطموح أكبر لنكون من العلامات الرائدة في تقديم الأكلات الشعبية.',
                        'Today we continue our journey with greater ambition to be a leading traditional cuisine brand.'
                    ) }}
                </p>
            </div>

        </div>
                    
          

        <div class="about-cta">
            <a class="btn btn--ghost dark-btn-ghost" href="{{ route('contact') }}">
                {{ FrontLang::t('تواصل معنا', 'Contact Us') }}
            </a>
            <a class="btn dark-btn" href="/menu">
                {{ FrontLang::t('عرض المنيو', 'View Menu') }}
            </a>
        </div>
         
    </section>
                
    @if(false)
     <div class="about-map">

    <div class="map-frame-responsive">
        <iframe
            src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d6013.621379604875!2d50.09990773653787!3d26.44908077907955!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3e49fb0bba827ce1%3A0x492862e324acc75d!2z2LTYsdmD2Kkg2YXYt9in2LnZhSDZhdin2LHYqCDZhNmE2KfZg9mE2KfYqiDYp9mE2LTYudio2YrZhw!5e1!3m2!1sen!2s!4v1767602615535!5m2!1sen!2s"
            allowfullscreen
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade">
        </iframe>
    </div>

    <div class="about-actions">
        <a class="btn dark-btn" target="_blank" href="https://maps.app.goo.gl/Fxpy7ndtqyVQovuZ7">
            {{ FrontLang::t('افتح الموقع على الخريطة', 'Open Location on Map') }}
        </a>
    </div>

</div>


    @endif

    @include('front.partials.location_map', [
        'embedUrl' => $aboutMapEmbedUrl,
        'openUrl' => $aboutMapOpenUrl,
        'openLabel' => FrontLang::t('افتح الموقع على الخريطة', 'Open location on map'),
        'wrapperClass' => 'about-map',
    ])

</div>

<script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
    AOS.init({
        duration: 1000,
        once: true
    });
});
</script>

@endsection
