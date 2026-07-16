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



@endif
@endsection
