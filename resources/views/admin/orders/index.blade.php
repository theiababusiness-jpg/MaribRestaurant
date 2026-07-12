<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الطلبات</title>
    <link rel="stylesheet" href="/css/variables.css">
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>
@php
    $statusLabels = [
        'pending' => 'قيد الانتظار',
        'paid' => 'مدفوع',
        'preparing' => 'قيد التجهيز',
        'ready_for_pickup' => 'جاهز للاستلام',
        'out_for_delivery' => 'خرج للتوصيل',
        'completed' => 'مكتمل',
        'cancelled' => 'ملغي',
        'failed_payment' => 'فشل الدفع',
    ];

    $fulfillmentLabels = [
        'pickup' => 'استلام من المطعم',
        'delivery' => 'توصيل',
    ];

    $paymentMethodLabels = [
        'cash' => 'كاش',
        'online' => 'أونلاين',
    ];

    $paymentStatusLabels = [
        'unpaid' => 'غير مدفوع',
        'pending' => 'بانتظار الدفع',
        'paid' => 'مدفوع',
        'failed' => 'فشل',
        'cancelled' => 'ملغي',
    ];
@endphp

<div class="container" style="margin-top:20px;">
    <div class="card">
        <div style="display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap; align-items:flex-start;">
            <div>
                <h2 class="page-title" style="margin:0;">الطلبات</h2>
                <p class="page-subtitle" style="margin:6px 0 0;">إدارة الطلبات مع الفروع وطريقة الاستلام والدفع.</p>
            </div>
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <a class="btn btn--ghost" href="{{ route('admin.branches.index') }}">الفروع</a>
                <a class="btn btn--ghost" href="{{ route('admin.dashboard') }}">لوحة التحكم</a>
            </div>
        </div>
    </div>

    <div id="newOrdersAlert" class="card" style="margin-top:14px; display:none; border-inline-start:4px solid #0a8a5b;">
        <div style="display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap; align-items:center;">
            <div>
                <strong>وصلت طلبات جديدة</strong>
                <div id="newOrdersAlertText" style="opacity:.8; margin-top:4px;">يوجد طلب جديد يحتاج المراجعة.</div>
            </div>
            <button class="btn" type="button" onclick="window.location.reload()">تحديث القائمة</button>
        </div>
    </div>

    <div class="admin-grid" style="margin-top:14px; grid-template-columns: repeat(3, minmax(0, 1fr));">
        <div class="card">
            <div class="card__title" style="margin:0 0 6px;">طلبات اليوم</div>
            <div style="font-size:28px; font-weight:800;">{{ $todayOrdersCount }}</div>
        </div>
        <div class="card">
            <div class="card__title" style="margin:0 0 6px;">طلبات غير مقروءة</div>
            <div style="font-size:28px; font-weight:800;">{{ $unseenOrdersCount }}</div>
        </div>
        <div class="card">
            <div class="card__title" style="margin:0 0 6px;">إجمالي النتائج</div>
            <div style="font-size:28px; font-weight:800;">{{ $orders->count() }}</div>
        </div>
    </div>

    <div class="card" style="margin-top:14px;">
        <form method="GET" style="display:grid; gap:12px;">
            <div style="display:grid; gap:10px; grid-template-columns: repeat(3, minmax(0, 1fr));">
                <input class="input" name="q" value="{{ request('q') }}" placeholder="رقم الطلب أو الكود أو الهاتف أو الاسم">
                <input class="input" type="date" name="date" value="{{ request('date') }}">
                <select class="input" name="branch_id">
                    <option value="">كل الفروع</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) request('branch_id') === (string) $branch->id)>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="display:grid; gap:10px; grid-template-columns: repeat(4, minmax(0, 1fr));">
                <select class="input" name="fulfillment_method">
                    <option value="">كل طرق الاستلام</option>
                    @foreach($fulfillmentLabels as $value => $label)
                        <option value="{{ $value }}" @selected(request('fulfillment_method') === $value)>{{ $label }}</option>
                    @endforeach
                </select>

                <select class="input" name="payment_method">
                    <option value="">كل طرق الدفع</option>
                    @foreach($paymentMethodLabels as $value => $label)
                        <option value="{{ $value }}" @selected(request('payment_method') === $value)>{{ $label }}</option>
                    @endforeach
                </select>

                <select class="input" name="payment_status">
                    <option value="">كل حالات الدفع</option>
                    @foreach($paymentStatusLabels as $value => $label)
                        <option value="{{ $value }}" @selected(request('payment_status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>

                <select class="input" name="status">
                    <option value="">كل حالات التشغيل</option>
                    @foreach($statusLabels as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <button class="btn" type="submit">تطبيق الفلاتر</button>
                <a class="btn btn--ghost" href="{{ route('admin.orders.index') }}">إعادة التعيين</a>
            </div>
        </form>
    </div>

    <div class="card" style="margin-top:14px;">
        <h3 class="card__title" style="margin:0 0 10px;">قائمة الطلبات</h3>

        @if($orders->isEmpty())
            <p class="page-subtitle" style="margin:0;">لا توجد طلبات مطابقة للفلاتر الحالية.</p>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الكود</th>
                            <th>العميل</th>
                            <th>الفرع</th>
                            <th>طريقة الاستلام</th>
                            <th>الإجمالي</th>
                            <th>الدفع</th>
                            <th>حالة الدفع</th>
                            <th>حالة الطلب</th>
                            <th>التاريخ</th>
                            <th>إجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            <tr style="{{ !$order->is_seen ? 'background:#fff7e6;' : '' }}">
                                <td>{{ $order->id }}</td>
                                <td>{{ $order->code }}</td>
                                <td>
                                    <strong>{{ $order->customer_name }}</strong>
                                    <div style="opacity:.7; font-size:12px;">{{ $order->customer_phone }}</div>
                                </td>
                                <td>{{ $order->branch?->name ?? 'غير محدد' }}</td>
                                <td>{{ $fulfillmentLabels[$order->fulfillment_method] ?? $order->fulfillment_method }}</td>
                                <td>{{ number_format((float) $order->total, 2) }} ريال</td>
                                <td>{{ $paymentMethodLabels[$order->payment_method] ?? $order->payment_method }}</td>
                                <td>{{ $paymentStatusLabels[$order->payment_status] ?? $order->payment_status }}</td>
                                <td>{{ $statusLabels[$order->status] ?? $order->status }}</td>
                                <td>{{ optional($order->created_at)->format('Y-m-d H:i') }}</td>
                                <td>
                                    <a class="btn btn--small" href="{{ route('admin.orders.show', $order) }}">عرض</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>


<style>
@media (max-width: 1000px) {
    .admin-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>
</body>
</html>
