@extends('layouts.app')

@php
    use App\Support\FrontLang;

    $hasMap = !empty($order->lat) && !empty($order->lng);
    $mapUrl = $hasMap ? ("https://www.google.com/maps?q={$order->lat},{$order->lng}") : null;
    $fulfillmentLabels = [
        'pickup' => FrontLang::t('استلام من المطعم', 'Pickup from branch'),
        'delivery' => FrontLang::t('توصيل', 'Delivery'),
    ];
    $paymentMethodLabels = [
        'cash' => FrontLang::t('كاش', 'Cash'),
        'online' => FrontLang::t('دفع إلكتروني', 'Online payment'),
    ];
@endphp

@section('content')
<div class="card dark-card" style="max-width:900px; margin:0 auto;">
    <h2 class="page-title dark-text">{{ FrontLang::t('تم استلام طلبك بنجاح', 'Your order has been received') }}</h2>
    <p class="page-subtitle dark-text">
        {{ FrontLang::t('رقم الطلب', 'Order Number') }}:
        <strong>{{ $order->code }}</strong>
    </p>

    <div class="card dark-card" style="margin-top:14px;">
        <h3 class="card__title dark-text">{{ FrontLang::t('ملخص الطلب', 'Order Summary') }}</h3>
        <div style="display:grid; gap:8px; margin-top:10px;" class="dark-text">
            <div><strong>{{ FrontLang::t('الاسم', 'Name') }}:</strong> {{ $order->customer_name }}</div>
            <div><strong>{{ FrontLang::t('الهاتف', 'Phone') }}:</strong> {{ $order->customer_phone }}</div>
            <div><strong>{{ FrontLang::t('الفرع', 'Branch') }}:</strong> {{ $order->branch?->name ?? FrontLang::t('غير محدد', 'Not set') }}</div>
            <div><strong>{{ FrontLang::t('طريقة الاستلام', 'Fulfillment') }}:</strong> {{ $fulfillmentLabels[$order->fulfillment_method] ?? $order->fulfillment_method }}</div>
            <div><strong>{{ FrontLang::t('طريقة الدفع', 'Payment Method') }}:</strong> {{ $paymentMethodLabels[$order->payment_method] ?? $order->payment_method }}</div>
            <div><strong>{{ FrontLang::t('حالة الدفع', 'Payment Status') }}:</strong> {{ $order->payment_status }}</div>
            <div><strong>{{ FrontLang::t('إجمالي المنتجات', 'Items Subtotal') }}:</strong> {{ number_format((float) $order->items_subtotal, 2) }} {{ FrontLang::t('ريال', 'SAR') }}</div>
            <div><strong>{{ FrontLang::t('رسوم التوصيل', 'Delivery Fee') }}:</strong> {{ number_format((float) $order->delivery_fee, 2) }} {{ FrontLang::t('ريال', 'SAR') }}</div>
            <div><strong>{{ FrontLang::t('الإجمالي النهائي', 'Final Total') }}:</strong> {{ number_format((float) $order->total, 2) }} {{ FrontLang::t('ريال', 'SAR') }}</div>
            @if($order->notes)
                <div><strong>{{ FrontLang::t('الملاحظات', 'Notes') }}:</strong> {{ $order->notes }}</div>
            @endif
        </div>
    </div>

    <div class="card dark-card" style="margin-top:14px;">
        <h3 class="card__title dark-text">{{ FrontLang::t('التسليم أو الاستلام', 'Pickup or Delivery') }}</h3>

        @if($order->fulfillment_method === 'delivery')
            <div style="display:grid; gap:8px; margin-top:10px;" class="dark-text">
                <div><strong>{{ FrontLang::t('العنوان', 'Address') }}:</strong> {{ $order->customer_address ?: FrontLang::t('غير متوفر', 'Not available') }}</div>
                @if($order->map_address)
                    <div><strong>{{ FrontLang::t('وصف الموقع', 'Map Address') }}:</strong> {{ $order->map_address }}</div>
                @endif
                @if($order->delivery_distance_km !== null)
                    <div><strong>{{ FrontLang::t('المسافة التقريبية', 'Estimated Distance') }}:</strong> {{ number_format((float) $order->delivery_distance_km, 2) }} {{ FrontLang::t('كم', 'km') }}</div>
                @endif
            </div>

            @if($hasMap)
                <div style="margin-top:12px; display:flex; gap:10px; flex-wrap:wrap;">
                    <a class="btn dark-btn" href="{{ $mapUrl }}" target="_blank" rel="noopener">
                        {{ FrontLang::t('فتح الموقع على الخريطة', 'Open location on map') }}
                    </a>
                </div>

                <div style="margin-top:12px; border-radius:14px; overflow:hidden; border:1px solid rgba(0,0,0,.08);">
                    <iframe
                        width="100%"
                        height="260"
                        style="border:0;"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        src="https://www.google.com/maps?q={{ $order->lat }},{{ $order->lng }}&z=16&output=embed">
                    </iframe>
                </div>
            @endif
        @else
            <div style="display:grid; gap:8px; margin-top:10px;" class="dark-text">
                <div><strong>{{ FrontLang::t('عنوان الفرع', 'Branch Address') }}:</strong> {{ $order->branch?->address ?? FrontLang::t('غير متوفر', 'Not available') }}</div>
                @if($order->branch?->phone)
                    <div><strong>{{ FrontLang::t('هاتف الفرع', 'Branch Phone') }}:</strong> {{ $order->branch->phone }}</div>
                @endif
            </div>
        @endif
    </div>

    <div class="card dark-card" style="margin-top:14px;">
        <h3 class="card__title dark-text">{{ FrontLang::t('تفاصيل العناصر', 'Items') }}</h3>
        <div style="display:grid; gap:10px; margin-top:10px;">
            @foreach($order->items as $item)
                @php
                    $options = is_string($item->options_json) ? json_decode($item->options_json, true) : ($item->options_json ?? []);
                    $options = is_array($options) ? $options : [];
                @endphp
                <div class="card dark-card" style="padding:12px;">
                    <div style="display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap;" class="dark-text">
                        <strong>{{ $item->product_name }}</strong>
                        <span>{{ $item->qty }} × {{ number_format((float) $item->unit_price, 2) }} = {{ number_format((float) $item->line_total, 2) }} {{ FrontLang::t('ريال', 'SAR') }}</span>
                    </div>
                    @if($options)
                        <ul style="margin:8px 0 0; padding:0 18px;" class="dark-text">
                            @foreach($options as $option)
                                <li>
                                    {{ is_array($option) ? ($option['name'] ?? 'Option') : $option }}
                                    @if(is_array($option) && isset($option['price']))
                                        (+{{ number_format((float) $option['price'], 2) }})
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <div style="margin-top:14px; display:flex; gap:10px; flex-wrap:wrap;">
        <a class="btn dark-btn" href="{{ route('menu.index') }}">{{ FrontLang::t('رجوع للمنيو', 'Back to menu') }}</a>
        @if($order->latestPayment && in_array($order->latestPayment->status, ['failed', 'cancelled', 'processing', 'initiated']))
            <a class="btn dark-btn-ghost" href="{{ route('payments.result', $order->latestPayment) }}">{{ FrontLang::t('متابعة الدفع', 'Review payment') }}</a>
        @endif
    </div>
</div>
@endsection
