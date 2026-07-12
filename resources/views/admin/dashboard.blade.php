<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - مأرب</title>
    <link rel="stylesheet" href="/css/variables.css">
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>
<div class="dashboard-wrap">
    <div class="card admin-card" style="display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap; align-items:flex-start;">
        <h2 class="page-title" style="margin:0;">لوحة التحكم</h2>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button class="btn" type="submit" style="background:#b00020;">تسجيل الخروج</button>
        </form>
    </div>

    <div class="dashboard-grid">
        <a class="card dashboard-card admin-card" href="{{ route('admin.settings.index') }}">
            <div class="stat-title">إعدادات المدير</div>
            <div class="stat-value">⚙️</div>
        </a>

        <a class="card dashboard-card admin-card" href="{{ route('admin.products.index') }}">
            <div class="stat-title">المنتجات</div>
            <div class="stat-value">{{ $productsCount ?? 0 }}</div>
        </a>

        <a class="card dashboard-card admin-card" href="{{ route('admin.orders.index') }}">
            <div class="stat-title">الطلبات</div>
            <div class="stat-value">{{ $ordersCount ?? 0 }}</div>
        </a>

        <a class="card dashboard-card admin-card" href="{{ route('admin.categories.index') }}">
            <div class="stat-title">التصنيفات</div>
            <div class="stat-value">{{ $categoriesCount ?? 0 }}</div>
        </a>

        <a class="card dashboard-card admin-card" href="{{ route('admin.option_groups.index') }}">
            <div class="stat-title">التخصيصات</div>
            <div class="stat-value">{{ $groupsCount ?? 0 }}</div>
        </a>

        <a class="card dashboard-card admin-card" href="{{ route('admin.banners.index') }}">
            <div class="stat-title">العروض</div>
            <div class="stat-value">{{ $bannersCount ?? 0 }}</div>
        </a>

        <a class="card dashboard-card admin-card" href="{{ route('admin.contact.index') }}">
            <div class="stat-title">رسائل التواصل</div>
            <div class="stat-value">{{ $unreadMessagesCount }}</div>
        </a>

        <a class="card dashboard-card admin-card" href="{{ route('admin.branches.index') }}">
            <div class="stat-title">الفروع</div>
            <div class="stat-value">{{ $branchesCount ?? 0 }}</div>
        </a>
    </div>

    <div class="card admin-card" style="margin-top:14px;">
        <h3 class="card__title" style="margin:0 0 10px;">آخر الطلبات</h3>

        @if($latestOrders->isEmpty())
            <p style="margin:0;">لا توجد طلبات بعد.</p>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>العميل</th>
                            <th>الفرع</th>
                            <th>الإجمالي</th>
                            <th>الحالة</th>
                            <th>عرض</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($latestOrders as $order)
                            <tr>
                                <td>{{ $order->id }}</td>
                                <td>{{ $order->customer_name }}</td>
                                <td>{{ $order->branch?->name ?? '—' }}</td>
                                <td>{{ number_format((float) $order->total, 2) }} ريال</td>
                                <td>{{ $order->status }}</td>
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
</body>
</html>
