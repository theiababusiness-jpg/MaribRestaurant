@extends('layouts.app')

@section('content')
@php
    use App\Support\FrontLang;
@endphp

<div class="card dark-card">

    <!-- عنوان المنتج -->
    <h2 class="page-title dark-text">
        {{ FrontLang::t($product->name, $product->name_en ?? $product->name) }}
    </h2>


    <!-- صورة المنتج -->
    @if(!empty($product->image_path))
        <img
            src="{{ asset($product->image_path) }}"
            alt="{{ FrontLang::t($product->name, $product->name_en ?? $product->name) }}"
            style="width:100%; max-height:280px; object-fit:cover; border-radius:18px; margin:12px 0;"
            loading="lazy"
        >
    @endif

    <!-- السعر -->
    <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
       
        
        @if(!($product->price == 0 && $product->has_special_message))

            <div class="card__price dark-text">
                {{ FrontLang::t('السعر', 'Price') }}:
                <span id="totalPrice">{{ $product->price }}</span>
                {{ FrontLang::t('ريال', 'SAR') }}
            </div>

        @endif
    </div>

    @if(!empty($product->description))

        <div class="card dark-card" style="margin-top:16px;">

            <h3 class="card__title dark-text">
                {{ FrontLang::t('عن الطبق', 'About this dish') }}
            </h3>

            <p class="dark-text" style="
                margin-top:10px;
                line-height:1.9;
                opacity:.92;
            ">
                {{ FrontLang::t($product->description, $product->description_en ?? $product->description) }}
            </p>

        </div>

    @endif

    @if($product->category && $product->category->slug == 'الأسماك')

        <hr style="margin:16px 0; opacity:.2;">

<div class="alert alert-info" style="margin-top:12px;">
{{ FrontLang::t('يمكن طلب السمك بالكيلو حسب الكمية المطلوبة.', 'Fish can be ordered by kilogram depending on the required quantity.') }}
</div>

<div style="margin-top:10px; display:flex; gap:10px; flex-wrap:wrap;">

<a href="https://wa.me/966558111372?text={{ urlencode('مرحبا اريد طلب سمك بالكيلو من مطعم مأرب') }}"
class="btn btn-success">
{{ FrontLang::t('تواصل واتساب', 'WhatsApp') }}
</a>
    <!-- اتصال -->
        <a 
        href="tel:+966138092388"
        class="btn dark-btn">
        {{ FrontLang::t('اتصال', 'Call') }}
        </a>

</div>

@endif  
    

            @if($product->has_special_message)
        <hr style="margin:16px 0; opacity:.2;">


<div class="card dark-card" style="border:1px solid #ffc107; background:rgba(255,193,7,0.08);">

    <h3 class="dark-text">
        {{ FrontLang::t('تنبيه', 'Notice') }}
    </h3>

    <p class="dark-text" style="margin-top:6px;">
        {{ $product->special_message }}
    </p>

    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:12px;">

        <!-- واتساب -->
        <a 
        href="https://wa.me/966530226751?text={{ urlencode(FrontLang::t('مرحبا اريد الاستفسار عن وجبة', 'Hello I want to ask about meal')) }} {{ $product->name }}"
        class="btn dark-btn">
        {{ FrontLang::t('تواصل واتساب', 'WhatsApp') }}
        </a>

        <!-- اتصال -->
        <a 
        href="tel:+966138092388"
        class="btn dark-btn">
        {{ FrontLang::t('اتصال', 'Call') }}
        </a>

        <!-- اختيار وجبة اخرى -->
        <a 
        href="{{ url('/menu') }}"
        class="btn dark-btn-ghost">
        {{ FrontLang::t('اختيار وجبة أخرى', 'Choose another meal') }}
        </a>

    </div>

</div>

@endif
            
                <hr style="margin:16px 0; opacity:.2;">

            
@if(!$product->has_special_message && $product->optionGroups->count() > 0)
        <form action="{{ route('cart.add') }}" method="POST" id="addToCartForm">
            @csrf

            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <input type="hidden" name="final_price" id="finalPriceInput" value="{{ $product->price }}">

            <div style="display:grid; gap:14px;">
                @foreach($product->optionGroups as $group)
                    <div class="card dark-card" style="padding:14px;">
                        <div style="display:flex; align-items:center; justify-content:space-between; gap:10px;">
                            <h3 class="card__title dark-text" style="margin:0;">
                                {{ FrontLang::t($group->name, $group->name_en ?? $group->name) }}
                            </h3>
                                

                            <small class="dark-text" style="opacity:.8;">
                                {{ $group->is_required
                                    ? FrontLang::t('إجباري', 'Required')
                                    : FrontLang::t('اختياري', 'Optional') }}
                                •
                                {{ $group->is_multiple
                                    ? FrontLang::t('متعدد', 'Multiple')
                                    : FrontLang::t('اختيار واحد', 'Single choice') }}
                            </small>
                        </div>

                        <div style="margin-top:10px; display:grid; gap:10px;">
                            @foreach($group->options as $opt)
                                <label style="display:flex; align-items:center; justify-content:space-between; gap:10px; border:1px solid rgba(0,0,0,.08); padding:10px; border-radius:12px;">
                                    <span style="display:flex; align-items:center; gap:10px;">
                                        @if($group->is_multiple)
                                            <input class="optInput" type="checkbox"
                                                   name="options[]"
                                                   value="{{ $opt->id }}"
                                                   data-delta="{{ $opt->price_delta }}">
                                        @else
                                            <input class="optInput" type="radio"
                                                   name="group_{{ $group->id }}"
                                                   value="{{ $opt->id }}"
                                                   data-delta="{{ $opt->price_delta }}"
                                                   {{ $loop->first ? 'checked' : '' }}>
                                        @endif

                                        <span class="dark-text">
                                            {{ FrontLang::t($opt->name, $opt->name_en ?? $opt->name) }}
                                        </span>
                                    </span>

                                    <strong style="color:var(--primary-color);">
                                        {{ $opt->price_delta }}
                                        {{ FrontLang::t('ريال', 'SAR') }}
                                    </strong>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div style="margin-top:14px; display:flex; gap:10px; flex-wrap:wrap;">
                <button type="submit" class="btn dark-btn">
                    {{ FrontLang::t('إضافة للسلة', 'Add to cart') }}
                </button>
            </div>
        </form>

   @elseif(!$product->has_special_message)
<form action="{{ route('cart.add') }}" method="POST" style="margin-top:14px;">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <input type="hidden" name="final_price" value="{{ $product->price }}">

            <button type="submit" class="btn dark-btn">
                {{ FrontLang::t('إضافة للسلة', 'Add to cart') }}
            </button>
        </form>
    @endif

    @if($product->category)
        <div style="margin-top:14px;">
            <a class="btn dark-btn-ghost" href="{{ url('/menu') }}">
                {{ FrontLang::t('رجوع للتصنيف', 'Back to menu') }}
            </a>
        </div>
    @endif
                
                
                
                
                
</div>


<script>
/*
    totalPrice: السعر الأساسي للمنتج كرقم
*/
const basePrice = parseFloat(document.getElementById('totalPrice')?.textContent) || 0;
/*
    totalPriceEl: العنصر الذي نعرض داخله السعر النهائي
*/
const totalPriceEl = document.getElementById('totalPrice');

/*
    optInputs: كل خيارات المنتج (radio/checkbox)
*/
const optInputs = document.querySelectorAll('.optInput');

/*
    finalPriceInput: input مخفي لإرسال السعر النهائي للسيرفر داخل الفورم
*/
const finalPriceInput = document.getElementById('finalPriceInput');

/*
    calcTotal():
    - يحسب السعر النهائي = السعر الأساسي + مجموع فروقات الخيارات المحددة
    - يحدث السعر المعروض
    - يحدث قيمة final_price المرسلة للسيرفر
*/
function calcTotal() {
    let total = basePrice;

    optInputs.forEach((input) => {
        if (input.checked) {
            const delta = parseFloat(input.dataset.delta) || 0;
            total += delta;
        }
    });

    if (totalPriceEl) totalPriceEl.textContent = total.toFixed(0);
    if (finalPriceInput) finalPriceInput.value = total.toFixed(0);
}

/*
    عند تغيير أي خيار: نعيد حساب السعر
*/
optInputs.forEach((input) => {
    input.addEventListener('change', calcTotal);
});

/*
    حساب أولي عند فتح الصفحة
*/
calcTotal();
</script>
@endsection
