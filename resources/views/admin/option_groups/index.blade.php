<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مجموعات التخصيص - مأرب</title>
    <link rel="stylesheet" href="/css/variables.css">
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>

<div class="container" style="max-width:1100px; margin-top:20px;">
    <div class="card">
        <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
            <div>
                <h2 class="page-title" style="margin:0;">مجموعات التخصيص</h2>
                <p class="page-subtitle" style="margin:6px 0 0;">مثال: نوع الأرز، إضافات، مستوى الحدة...</p>
            </div>

            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <a class="btn" href="{{ route('admin.option_groups.create') }}">+ إضافة مجموعة</a>
                <a class="btn btn--ghost" href="{{ route('admin.products.index') }}">المنتجات</a>
                <a class="btn btn--ghost" href="{{ route('admin.dashboard') }}">لوحة التحكم</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert--success" style="margin-top:12px;">
                {{ session('success') }}
            </div>
        @endif
    </div>

    <div class="card" style="margin-top:14px;">
        @if($groups->count() === 0)
            <p class="page-subtitle">لا توجد مجموعات تخصيص حاليا.</p>
        @else
            <div style="overflow:auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الاسم</th>
                			<th>وصف التخصيص</th>
                            <th>إجباري</th>
                            <th>متعدد</th>
                            <th>ترتيب</th>
                            <th style="min-width:180px;">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($groups as $g)
                            <tr>
                                <td>{{ $g->id }}</td>
                                <td><strong>{{ $g->name }}</strong></td>		
                                <td>
                                    @if($g->admin_note)
                                        <span style="opacity:.8;">{{ $g->admin_note }}</span>
                                    @else
                                        <span style="opacity:.4;">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($g->is_required)
                                        <span class="badge badge--ok">نعم</span>
                                    @else
                                        <span class="badge badge--off">لا</span>
                                    @endif
                                </td>
                                <td>
                                    @if($g->is_multiple)
                                        <span class="badge badge--ok">نعم</span>
                                    @else
                                        <span class="badge badge--off">لا</span>
                                    @endif
                                </td>
                                <td>{{ $g->sort_order }}</td>
                                <td>
                                    
                                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                                        <a class="btn btn--small"
                                        href="{{ route('admin.options.index', $g) }}">
                                        الخيارات
                                        </a>
                                        <a class="btn btn--small" href="{{ route('admin.option_groups.edit', $g) }}">تعديل</a>

                                        <form action="{{ route('admin.option_groups.destroy', $g) }}" method="POST" onsubmit="return confirm('تأكيد حذف المجموعة؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn--small" type="submit" style="background:var(--secondary-color);">
                                                حذف
                                            </button>
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
