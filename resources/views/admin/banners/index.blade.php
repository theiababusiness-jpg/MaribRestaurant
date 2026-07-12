<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة العروض - مطعم مأرب</title>

    <link rel="stylesheet" href="/css/variables.css">
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>

<div class="container" style="max-width:1100px; margin-top:20px;">

    <!-- كرت عنوان الصفحة -->
    <div class="card">
        <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
            <div>
                <h2 class="page-title" style="margin:0;">إدارة العروض</h2>
                <p class="page-subtitle" style="margin:6px 0 0;">
                    إضافة وتعديل وتنظيم العروض الظاهرة في الموقع.
                </p>
            </div>

            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <a class="btn" href="{{ route('admin.banners.create') }}">+ إضافة عرض</a>
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

    <!-- جدول العروض -->
    <div class="card" style="margin-top:14px;">

        @if($banners->count() === 0)
            <p class="page-subtitle">لا توجد عروض حالياً.</p>
        @else
            <div style="overflow:auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الصورة</th>
                            <th>العنوان</th>
                            <th>نوع الرابط</th>
                            <th>الحالة</th>
                            <th>الترتيب</th>
                            <th>المدة</th>
                            <th style="min-width:180px;">إجراءات</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($banners as $banner)
                            <tr>
                                <td>{{ $banner->id }}</td>

                                <!-- الصورة -->
                                <td>
                                    @if(!empty($banner->image_path))
                                        <img
    src="{{ asset($banner->image_path) }}"
    style="width:80px; border-radius:6px; object-fit:cover;">



                                    @else
                                        —
                                    @endif
                                </td>

                                <!-- العنوان -->
                                <td>
                                    <strong>{{ $banner->title }}</strong>
                                    @if($banner->subtitle)
                                        <br>
                                        <small style="opacity:.7;">{{ $banner->subtitle }}</small>
                                    @endif
                                </td>

                                <!-- نوع الرابط -->
                                <td>
                                    @switch($banner->link_type)
                                        @case('product')
                                            <span class="badge badge--info">منتج</span>
                                            @break
                                        @case('menu')
                                            <span class="badge badge--primary">منيو</span>
                                            @break
                                        @default
                                            <span class="badge badge--off">بدون زر</span>
                                    @endswitch
                                </td>

                                <!-- الحالة -->
                                <td>
                                    @if($banner->is_active)
                                        <span class="badge badge--ok">مفعل</span>
                                    @else
                                        <span class="badge badge--off">معطل</span>
                                    @endif
                                </td>

                                <!-- الترتيب -->
                                <td>{{ $banner->sort_order }}</td>

                                <!-- المدة -->
                                <td style="font-size:13px; opacity:.85;">
                                    من:
                                    {{ $banner->start_at ? $banner->start_at->format('Y-m-d H:i') : '—' }}
                                    <br>
                                    إلى:
                                    {{ $banner->end_at ? $banner->end_at->format('Y-m-d H:i') : '—' }}
                                </td>

                                <!-- الإجراءات -->
                                <td>
                                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                                        <a class="btn btn--small"
                                           href="{{ route('admin.banners.edit', $banner->id) }}">
                                            تعديل
                                        </a>

                                        <form
                                            action="{{ route('admin.banners.destroy', $banner->id) }}"
                                            method="POST"
                                            onsubmit="return confirm('هل أنت متأكد من حذف العرض؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn--small"
                                                    type="submit"
                                                    style="background:red;">
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
