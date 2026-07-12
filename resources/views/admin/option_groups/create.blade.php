<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة مجموعة تخصيص - مأرب</title>
    <link rel="stylesheet" href="/css/variables.css">
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>

<div class="container" style="max-width:780px; margin-top:20px;">
    <div class="card">
        <h2 class="page-title" style="margin:0;">إضافة مجموعة تخصيص</h2>
        <p class="page-subtitle" style="margin:6px 0 0;">مثال: نوع الأرز، إضافات...</p>

        @if($errors->any())
            <div class="alert alert--danger" style="margin-top:12px;">
                {{ $errors->first() }}
            </div>
        @endif
    </div>

    <div class="card" style="margin-top:14px;">
        <form action="{{ route('admin.option_groups.store') }}" method="POST" style="display:grid; gap:12px;">
            @csrf

            <!-- name: اسم المجموعة -->
            <div>
                <label class="label">اسم المجموعة</label>
                <input class="input" type="text" name="name" id="name" value="{{ old('name') }}" required>
            </div>

            <div>
                <label class="label">اسم المجموعة (EN)</label>
                <div style="display:flex; gap:8px;">
                    <input class="input" type="text" name="name_en" id="name_en" value="{{ old('name_en') }}">
                    <button type="button" class="btn translate-btn" data-from="name" data-to="name_en">
                        ترجمة تلقائية
                    </button>
                </div>
            </div>
              <!-- admin_note: وصف داخلي للمدير -->
            <div>
                <label class="label">وصف داخلي (للإدارة فقط)</label>
                <input 
                    class="input" 
                    type="text" 
                    name="admin_note" 
                    value="{{ old('admin_note') }}" 
                    placeholder="مثال: 20-40 أو دجاج مشوي خاص"
                >
                <small style="opacity:.7;">
                    هذا الوصف يظهر فقط في لوحة التحكم لمساعدة المدير على التمييز بين التخصيصات.
                </small>
            </div>



            <div style="display:flex; gap:12px; flex-wrap:wrap;">
                <!-- is_required: هل المجموعة إجبارية؟ -->
                <label style="display:flex; align-items:center; gap:8px;">
                    <input type="checkbox" name="is_required" value="1" {{ old('is_required') ? 'checked' : '' }}>
                    <strong>إجباري (لازم يختار)</strong>
                </label>

                <!-- is_multiple: هل يسمح بأكثر من خيار؟ -->
                <label style="display:flex; align-items:center; gap:8px;">
                    <input type="checkbox" name="is_multiple" value="1" {{ old('is_multiple') ? 'checked' : '' }}>
                    <strong>متعدد (أكثر من اختيار)</strong>
                </label>
            </div>

            <!-- sort_order: ترتيب ظهور المجموعة -->
            <div>
                <label class="label">ترتيب الظهور</label>
                <input class="input" type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0">
            </div>

            <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:6px;">
                <button class="btn" type="submit">حفظ</button>
                <a class="btn btn--ghost" href="{{ route('admin.option_groups.index') }}">رجوع</a>
            </div>
        </form>
    </div>
</div>
<script src="{{ asset('js/auto-translate.js') }}"></script>
</body>
</html>
