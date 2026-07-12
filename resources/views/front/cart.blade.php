@extends('layouts.app')

@php
    use App\Support\FrontLang;
@endphp

@section('content')

<h2 class="page-title dark-text">
    {{ FrontLang::t('السلة', 'Cart') }}
</h2>

@php
    // total: إجمالي السلة
    $total = 0;
@endphp

@if(empty($cart))
    <p class="page-subtitle dark-text">
        {{ FrontLang::t('السلة فارغة حاليا.', 'Your cart is currently empty.') }}
    </p>
    <a class="btn dark-btn" href="{{ route('menu.index') }}">
        {{ FrontLang::t('اذهب للمنيو', 'Go to Menu') }}
    </a>
@else

    {{-- عناصر السلة --}}
    <div class="grid">
        @foreach($cart as $index => $item)
            @php
                // itemTotal: إجمالي هذا السطر
                $itemTotal = (float) ($item['final_price'] ?? 0);
                $total += $itemTotal;
            @endphp

            <div class="card dark-card">
                {{-- اسم المنتج --}}
                <h3 class="card__title dark-text">
                    {{ FrontLang::db($item['name'] ?? 'منتج', $item['name_en'] ?? null) }}
                </h3>

                {{-- السعر الإجمالي للسطر --}}
                <div class="card__price dark-text">
                    {{ FrontLang::t('الإجمالي:', 'Total:') }}
                    {{ number_format($itemTotal, 0) }}
                    {{ FrontLang::t('ريال', 'SAR') }}
                </div>

                {{-- الكمية --}}
                <p style="margin:6px 0 0; opacity:.85;" class="dark-text">
                    {{ FrontLang::t('الكمية:', 'Quantity:') }}
                    {{ (int)($item['qty'] ?? 1) }}
                </p>

                {{-- عرض الخيارات المختارة --}}
                @if(!empty($item['options']) && is_array($item['options']))
                    <div style="margin-top:10px; opacity:.9;" class="dark-text">
                        <strong>{{ FrontLang::t('التخصيصات:', 'Options:') }}</strong>
                        <ul style="margin:8px 0 0; padding:0 18px;">
                            @foreach($item['options'] as $opt)
                                <li>
                                    {{ FrontLang::db($opt['name'] ?? '', $opt['name_en'] ?? null) }}
                                    @if(isset($opt['price']))
                                        ( +{{ number_format((float)$opt['price'], 0) }} )
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <p style="margin:10px 0 0; opacity:.8;" class="dark-text">
                        {{ FrontLang::t('بدون تخصيصات', 'No options') }}
                    </p>
                @endif

                {{-- التحكم بالكمية (+ / -) --}}
                <div style="display:flex; gap:8px; align-items:center; margin-top:12px; flex-wrap:wrap;">
                    {{-- زر إنقاص --}}
                    <form action="{{ route('cart.dec', $index) }}" method="POST" style="margin:0;">
                        @csrf
                        <button type="submit" class="btn dark-btn" style="min-width:auto; padding:8px 12px; background:var(--secondary-color);">
                            -
                        </button>
                    </form>

                    {{-- عرض الكمية --}}
                    <strong style="min-width:40px; text-align:center;" class="dark-text">
                        {{ (int)($item['qty'] ?? 1) }}
                    </strong>

                    {{-- زر زيادة --}}
                    <form action="{{ route('cart.inc', $index) }}" method="POST" style="margin:0;">
                        @csrf
                        <button type="submit" class="btn dark-btn" style="min-width:auto; padding:8px 12px;">
                            +
                        </button>
                    </form>
                </div>

                {{-- حذف من السلة --}}
                <form action="{{ route('cart.remove', $index) }}" method="POST" style="margin-top:12px;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn dark-btn" style="background:var(--secondary-color); min-width:auto;">
                        {{ FrontLang::t('حذف من السلة', 'Remove from cart') }}
                    </button>
                </form>
            </div>
        @endforeach
    </div>

    {{-- ملخص السلة --}}
    <div class="card dark-card" style="margin-top:14px;">
        <h3 class="card__title dark-text">
            {{ FrontLang::t('ملخص السلة', 'Cart Summary') }}
        </h3>
        <div class="card__price dark-text">
            {{ FrontLang::t('الإجمالي:', 'Total:') }}
            {{ number_format($total, 0) }}
            {{ FrontLang::t('ريال', 'SAR') }}
        </div>

        <div style="margin-top:12px; display:flex; gap:10px; flex-wrap:wrap;">
            <a class="btn dark-btn" href="{{ route('menu.index') }}">
                {{ FrontLang::t('إضافة منتجات', 'Add Products') }}
            </a>
            <a class="btn dark-btn" href="{{ route('checkout.index') }}">
                {{ FrontLang::t('إتمام الطلب', 'Checkout') }}
            </a>
        </div>
    </div>

    {{-- اقتراحات (قد يعجبك أيضا) --}}
    @if(isset($suggested) && $suggested->count() > 0)
        <div class="card dark-card" style="margin-top:14px;">
            <h3 class="card__title dark-text">
                {{ FrontLang::t('قد يعجبك أيضا', 'You May Also Like') }}
            </h3>

            <div class="grid" style="margin-top:12px;">
                @foreach($suggested as $p)
                    <div class="card dark-card" style="padding:14px;">
                        <h4 class="card__title dark-text" style="margin:0 0 6px 0;">
                            {{ FrontLang::db($p->name, $p->name_en ?? null) }}
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
                            

                        {{-- إضافة سريعة بدون تخصيصات --}}
                        <form action="{{ route('cart.add') }}" method="POST" style="margin-top:10px;">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $p->id }}">
                            <input type="hidden" name="final_price" value="{{ $p->price }}">
                            <input type="hidden" name="qty" value="1">

                            <button type="submit" class="btn dark-btn" style="min-width:auto; padding:10px 14px;">
                                {{ FrontLang::t('إضافة سريع', 'Quick Add') }}
                            </button>
                        </form>

                        <div style="margin-top:10px;">
                            <a class="btn btn--ghost dark-btn-ghost" href="{{ route('product.show', $p) }}">
                                {{ FrontLang::t('عرض التفاصيل', 'View Details') }}
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

@endif
@endsection
