<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة التصنيفات - مطعم مأرب</title>
    <link rel="stylesheet" href="/css/variables.css">
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>

<div class="container" style="max-width:1100px; margin-top:20px;">

    <!-- كرت عنوان الصفحة -->
    <div class="card">
        <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
            <div>
                <h2 class="page-title" style="margin:0;">إدارة التصنيفات</h2>
                <p class="page-subtitle" style="margin:6px 0 0;">إضافة وتعديل وترتيب تصنيفات المنيو.</p>
            </div>

            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <a class="btn" href="{{ route('admin.categories.create') }}">+ إضافة تصنيف</a>
                <!-- إذا عندك داشبورد: admin.dashboard
                     إذا لم يكن موجود احذف هذا الزر -->
                <a class="btn btn--ghost" href="{{ route('admin.dashboard') }}">لوحة التحكم</a>
            </div>
        </div>

        <!-- رسالة نجاح -->
        @if(session('success'))
            <div class="alert alert--success" style="margin-top:12px;">
                {{ session('success') }}
            </div>
        @endif
    </div>

    <!-- جدول التصنيفات -->
    <div class="card" style="margin-top:14px;">
        @if($categories->count() === 0)
            <p class="page-subtitle">لا توجد تصنيفات حاليا.</p>
        @else
            <div style="overflow:auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الاسم</th>
                            <th>الوصف</th>
                            <th>Slug</th>
                            <th>مفعل</th>
                            <th>ترتيب</th>
                            <th style="min-width:180px;">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $cat)
                            <tr>
                                <td>{{ $cat->id }}</td>

                                <!-- name: اسم التصنيف -->
                                <td><strong>{{ $cat->name }}</strong></td>

                                <!-- description: وصف التصنيف -->
                                <td style="opacity:.8;">{{ $cat->description ?? '-' }}</td>

                                <td style="opacity:.8;">{{ $cat->slug }}</td>

                                <td>
                                    @if($cat->is_active)
                                        <span class="badge badge--ok">نعم</span>
                                    @else
                                        <span class="badge badge--off">لا</span>
                                    @endif
                                </td>

                                <td>{{ $cat->sort_order }}</td>

                                <td>
                                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                                        <a class="btn btn--small" href="{{ route('admin.categories.edit', $cat) }}">تعديل</a>

                                        <form action="{{ route('admin.categories.destroy', $cat) }}" method="POST" onsubmit="return confirm('تأكيد حذف التصنيف؟');">
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
