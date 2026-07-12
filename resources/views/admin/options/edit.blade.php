<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تعديل خيار - مأرب</title>
    <link rel="stylesheet" href="/css/variables.css">
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>

<div class="container" style="max-width:700px; margin-top:20px;">
    <div class="card">
        <h2 class="page-title">تعديل خيار</h2>
        <p class="page-subtitle">تابع لمجموعة: <strong>{{ $option->optionGroup->name }}

</strong></p>

        @if($errors->any())
            <div class="alert alert--danger">{{ $errors->first() }}</div>
        @endif
    </div>

    <div class="card" style="margin-top:14px;">
        <form action="{{ route('admin.options.update', $option) }}" method="POST" style="display:grid; gap:12px;">
            @csrf
            @method('PUT')

            <!-- اسم الخيار -->
            <div>
                <label class="label">اسم الخيار</label>
                <input class="input" type="text" name="name" id="name"
                    value="{{ old('name', $option->name) }}" required>
            </div>

            <div>
                <label class="label">اسم الخيار (EN)</label>
                <div style="display:flex; gap:8px;">
                    <input class="input" type="text" name="name_en" id="name_en"
                        value="{{ old('name_en', $option->name_en) }}">
                    <button type="button" class="btn translate-btn" data-from="name" data-to="name_en">
                        ترجمة تلقائية
                    </button>
                </div>
            </div>

            <!-- فرق السعر -->
            <div>
                <label class="label">فرق السعر (ريال)</label>
                <input class="input" type="number" name="price_delta" value="{{ old('price_delta', $option->price_delta) }}" step="0.01" required>
            </div>

            <!-- ترتيب -->
            <div>
                <label class="label">ترتيب الظهور</label>
                <input class="input" type="number" name="sort_order" value="{{ old('sort_order', $option->sort_order) }}" min="0">
            </div>

            <div style="display:flex; gap:10px;">
                <button class="btn" type="submit">حفظ التعديل</button>
                <a class="btn btn--ghost" href="{{ route('admin.options.index', $option->optionGroup) }}">رجوع</a>
            </div>
        </form>
    </div>
</div>
<script src="{{ asset('js/auto-translate.js') }}"></script>
</body>
</html>
