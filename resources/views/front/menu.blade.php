@extends('layouts.app')

@php
    use App\Support\FrontLang;
@endphp

@section('content')

<h2 class="page-title dark-text">
    {{ FrontLang::t('المنيو', 'Menu') }}
</h2>
<h1>تجربة الرفع التلقائي</h1>

{{-- 
    المنطق:
    - إذا يوجد q => عرض نتائج البحث (products)
    - إذا لا يوجد q => عرض شريط التصنيفات + منتجات التصنيف المحدد
--}}

{{-- شريط البحث (دائما ظاهر) --}}
<div class="card dark-card" style="margin-top:12px;">
    <form action="{{ route('menu.index') }}" method="GET" style="display:flex; gap:10px; flex-wrap:wrap;">
        {{-- q: كلمة البحث --}}
        <input
            class="input dark-input"
            type="text"
            name="q"
            value="{{ $q ?? '' }}"
            placeholder="{{ FrontLang::t('ابحث عن طبق... (رز، دجاج، مشروبات)', 'Search for a dish... (rice, chicken, drinks)') }}"
            style="flex:1; min-width:220px;"
        >
        <button class="btn dark-btn" type="submit" style="min-width:auto;">
            {{ FrontLang::t('بحث', 'Search') }}
        </button>

        {{-- إذا المستخدم يبحث: زر مسح البحث --}}
        @if(isset($q) && $q !== '')
            <a class="btn btn--ghost dark-btn-ghost" href="{{ route('menu.index') }}" style="min-width:auto;">
                {{ FrontLang::t('مسح', 'Clear') }}
            </a>
        @endif
    </form>
</div>

{{-- إذا يوجد بحث --}}
@if(isset($q) && $q !== '')
    <div class="card dark-card" style="margin-top:14px;">
        <h3 class="card__title dark-text">
            {{ FrontLang::t('نتائج البحث عن:', 'Search results for:') }} "{{ $q }}"
        </h3>

        @if(!isset($products) || $products->count() === 0)
            <p class="page-subtitle dark-text">
                {{ FrontLang::t('لا توجد نتائج مطابقة.', 'No matching results.') }}
            </p>
        @else
            <div class="grid" style="margin-top:12px;">
                @foreach($products as $p)
                    <a class="card dark-card" href="{{ route('product.show', $p) }}" style="text-decoration:none; color:inherit;">

                        {{-- صورة المنتج إن وجدت --}}
                        @if(!empty($p->image_path))
                            <img
                                src="{{ asset($p->image_path) }}"
                                alt="{{ $p->name }}"
                                style="width:100%; height:160px; object-fit:cover; border-radius:14px; margin-bottom:10px;"
                                loading="lazy"
                            >
                        @endif

                        <h4 class="card__title dark-text">
                            {{ FrontLang::db($p->name, $p->name_en ?? null) }}
                        </h4>

                        @if($p->description)
                            <p class="card__desc dark-text">
                                {{ FrontLang::db($p->description, $p->description_en ?? null) }}
                            </p>
                        @endif

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
                            <span class="btn btn--small dark-btn" style="min-width:auto;">
                                {{ FrontLang::t('عرض التفاصيل', 'View Details') }}
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

@else
    {{-- شريط التصنيفات الأفقي --}}
    @if(!isset($categories) || $categories->count() === 0)
        <p class="page-subtitle dark-text">
            {{ FrontLang::t('لا توجد تصنيفات حاليا.', 'No categories available.') }}
        </p>
    @else
        <div class="cats-scroll dark-section" style="margin-top:14px;">
            @foreach($categories as $cat)

                {{-- catBtn: زر التصنيف (نستخدم a لأنه ينقل) --}}
                <a style="margin:3px;"
                    class="btn cat-chip dark-chip {{ ($activeCategorySlug ?? '') === $cat->slug ? 'is-active' : '' }}"
                    href="{{ route('menu.category', $cat) }}"
                >
                    {{ FrontLang::db($cat->name, $cat->name_en ?? null) }}
                </a>

            @endforeach
        </div>

        {{-- المنتجات تحت الشريط --}}
        <div style="margin-top:14px;">
            @if(!isset($products) || $products->count() === 0)
                <p class="page-subtitle dark-text">
                    {{ FrontLang::t('لا توجد منتجات في هذا التصنيف حاليا.', 'No products in this category.') }}
                </p>
            @else
                <div class="grid">
                    @foreach($products as $p)
                        <a class="card dark-card" href="{{ route('product.show', $p) }}" style="text-decoration:none; color:inherit;">

                            {{-- صورة المنتج إن وجدت --}}
                            @if(!empty($p->image_path))
                                <img
                                    src="{{ asset($p->image_path) }}"
                                    alt="{{ $p->name }}"
                                    style="width:100%; height:160px; object-fit:cover; border-radius:14px; margin-bottom:10px;"
                                    loading="lazy"
                                >
                            @endif

                            <h3 class="card__title dark-text">
                                {{ FrontLang::db($p->name, $p->name_en ?? null) }}
                            </h3>

                           

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
                                <span class="btn btn--small dark-btn" style="min-width:auto;">
                                    {{ FrontLang::t('عرض التفاصيل', 'View Details') }}
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
@endif

@endsection
