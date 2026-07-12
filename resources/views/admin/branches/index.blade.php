<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الفروع</title>
    <link rel="stylesheet" href="/css/variables.css">
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>
<div class="container" style="margin-top:20px;">
    <div class="card">
        <div style="display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap; align-items:flex-start;">
            <div>
                <h2 class="page-title" style="margin:0;">الفروع</h2>
                <p class="page-subtitle" style="margin:6px 0 0;">إدارة الفروع المستخدمة في الاستلام والتوصيل.</p>
            </div>
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <a class="btn" href="{{ route('admin.branches.create') }}">إضافة فرع</a>
                <a class="btn btn--ghost" href="{{ route('admin.dashboard') }}">لوحة التحكم</a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert--success" style="margin-top:14px;">{{ session('success') }}</div>
    @endif

    <div class="card" style="margin-top:14px;">
        @if($branches->isEmpty())
            <p class="page-subtitle" style="margin:0;">لا توجد فروع بعد. ابدأ بإضافة أول فرع.</p>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الفرع</th>
                            <th>الاستلام</th>
                            <th>التوصيل</th>
                            <th>الرسم</th>
                            <th>النطاق</th>
                            <th>الإحداثيات</th>
                            <th>الجاهزية</th>
                            <th>الحالة</th>
                            <th>إجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($branches as $branch)
                            @php $deliveryReady = $branch->delivery_enabled && $branch->lat !== null && $branch->lng !== null; @endphp
                            <tr>
                                <td>{{ $branch->id }}</td>
                                <td>
                                    <strong>{{ $branch->name }}</strong>
                                    @if($branch->address)
                                        <div style="opacity:.7; font-size:12px;">{{ $branch->address }}</div>
                                    @endif
                                </td>
                                <td>{{ $branch->pickup_enabled ? 'مفعل' : 'غير مفعل' }}</td>
                                <td>{{ $branch->delivery_enabled ? 'مفعل' : 'غير مفعل' }}</td>
                                <td>5.00 ريال</td>
                                <td>4 كم</td>
                                <td>
                                    @if($branch->lat !== null && $branch->lng !== null)
                                        <span dir="ltr">{{ number_format((float) $branch->lat, 5) }}, {{ number_format((float) $branch->lng, 5) }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    <span style="font-weight:600; color:{{ $deliveryReady ? '#0a7a2f' : '#9a6700' }};">
                                        {{ $deliveryReady ? 'جاهز' : 'غير مكتمل' }}
                                    </span>
                                </td>
                                <td>{{ $branch->is_active ? 'نشط' : 'مخفي' }}</td>
                                <td>
                                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                                        <a class="btn btn--small" href="{{ route('admin.branches.edit', $branch) }}">تعديل</a>
                                        <form method="POST" action="{{ route('admin.branches.destroy', $branch) }}" onsubmit="return confirm('تأكيد حذف هذا الفرع؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn--small" type="submit" style="background:#b00020;">حذف</button>
                                        </form>
                                    </div>
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
