@extends('layouts.app')

@php
    use App\Support\FrontLang;

    $paymentStatusLabels = [
        'initiated' => FrontLang::t('تم إنشاء عملية الدفع', 'Payment initiated'),
        'processing' => FrontLang::t('قيد المعالجة', 'Processing'),
        'paid' => FrontLang::t('مدفوع', 'Paid'),
        'failed' => FrontLang::t('فشل الدفع', 'Payment failed'),
        'cancelled' => FrontLang::t('أُلغي الدفع', 'Payment cancelled'),
    ];

    $fulfillmentLabels = [
        'pickup' => FrontLang::t('استلام من المطعم', 'Pickup from branch'),
        'delivery' => FrontLang::t('توصيل', 'Delivery'),
    ];

    $orderStatusLabels = [
        'pending' => FrontLang::t('بانتظار المعالجة', 'Pending'),
        'paid' => FrontLang::t('مدفوع', 'Paid'),
        'preparing' => FrontLang::t('قيد التجهيز', 'Preparing'),
        'ready_for_pickup' => FrontLang::t('جاهز للاستلام', 'Ready for pickup'),
        'out_for_delivery' => FrontLang::t('خرج للتوصيل', 'Out for delivery'),
        'completed' => FrontLang::t('مكتمل', 'Completed'),
        'cancelled' => FrontLang::t('ملغي', 'Cancelled'),
        'failed_payment' => FrontLang::t('فشل الدفع', 'Payment failed'),
    ];
@endphp

@section('content')
<div class="card dark-card" style="max-width:900px; margin:0 auto;">
    <h2 class="page-title dark-text">{{ FrontLang::t('نتيجة الدفع', 'Payment Result') }}</h2>
    <p class="page-subtitle dark-text">
        {{ FrontLang::t('رقم الطلب', 'Order Number') }}:
        <strong>{{ $payment->order->code }}</strong>
    </p>

    <div class="card dark-card" style="margin-top:14px;">
        <div class="dark-text" style="display:grid; gap:10px;">
            <div><strong>{{ FrontLang::t('حالة الدفع', 'Payment Status') }}:</strong> {{ $paymentStatusLabels[$payment->status] ?? $payment->status }}</div>
            <div><strong>{{ FrontLang::t('الفرع', 'Branch') }}:</strong> {{ $payment->order->branch?->name ?? FrontLang::t('غير محدد', 'Not set') }}</div>
            <div><strong>{{ FrontLang::t('طريقة الاستلام', 'Fulfillment') }}:</strong> {{ $fulfillmentLabels[$payment->order->fulfillment_method] ?? $payment->order->fulfillment_method }}</div>
            <div><strong>{{ FrontLang::t('المبلغ', 'Amount') }}:</strong> {{ number_format((float) $payment->amount, 2) }} {{ $payment->currency }}</div>
            <div><strong>{{ FrontLang::t('حالة الطلب', 'Order Status') }}:</strong> {{ $orderStatusLabels[$payment->order->status] ?? $payment->order->status }}</div>
            @if($payment->failure_reason)
                <div><strong>{{ FrontLang::t('سبب التعثر', 'Failure Reason') }}:</strong> {{ $payment->failure_reason }}</div>
            @endif
        </div>
    </div>

    <div style="margin-top:14px; display:flex; gap:10px; flex-wrap:wrap;">
        @if(in_array($payment->status, ['failed', 'cancelled', 'processing', 'initiated']))
            <form method="POST" action="{{ route('payments.retry', $payment) }}">
                @csrf
                <button class="btn dark-btn" type="submit">{{ FrontLang::t('إعادة المحاولة', 'Retry Payment') }}</button>
            </form>
        @endif

        <a class="btn dark-btn-ghost" href="{{ route('order.success', $payment->order->code) }}">{{ FrontLang::t('عرض الطلب', 'View Order') }}</a>
    </div>
</div>
@endsection
