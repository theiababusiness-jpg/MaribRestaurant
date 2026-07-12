<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل تصنيف - مطعم مأرب</title>
    <link rel="stylesheet" href="/css/variables.css">
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>

<div class="container" style="max-width:780px; margin-top:20px;">

    <!-- عنوان الصفحة -->
    <div class="card">
        <h2 class="page-title" style="margin:0;">تعديل تصنيف</h2>
        <p class="page-subtitle" style="margin:6px 0 0;">حدّث البيانات ثم احفظ.</p>

        <!-- عرض أول خطأ -->
        @if($errors->any())
            <div class="alert alert--danger" style="margin-top:12px;">
                {{ $errors->first() }}
            </div>
        @endif
    </div>

    <!-- فورم التعديل -->
    <div class="card" style="margin-top:14px;">
        <form action="{{ route('admin.categories.update', $category) }}" method="POST" style="display:grid; gap:12px;">
            @csrf
            @method('PUT')

            <!-- name: اسم التصنيف -->
            <div>
                <label class="label">اسم التصنيف *</label>
                <input class="input" type="text" name="name" id="name"
                    value="{{ old('name', $category->name) }}" required>
            </div>

            <div>
                <label class="label">اسم التصنيف (EN)</label>

                <div style="display:flex; gap:8px;">
                    <input class="input" type="text" name="name_en" id="name_en"
                        value="{{ old('name_en', $category->name_en) }}">

                    <button type="button"
                            class="btn translate-btn"
                            data-from="name"
                            data-to="name_en">
                        ترجمة تلقائية
                    </button>
                </div>
            </div>

            <div>
                <label class="label">الوصف</label>
                <textarea class="input" name="description" id="description" rows="4">
            {{ old('description', $category->description) }}</textarea>
            </div>

            <div>
            <label class="label">الوصف (EN)</label>

            <button type="button"
                    class="btn translate-btn"
                    data-from="description"
                    data-to="description_en"
                    style="margin-bottom:6px;">
                ترجمة تلقائية
            </button>

            <textarea class="input" name="description_en" id="description_en" rows="4">{{ old('description_en') }}</textarea>
        </div>



            <!-- slug: رابط التصنيف -->
            <div>
                <label class="label">Slug</label>
                <input class="input" type="text" name="slug" value="{{ old('slug', $category->slug) }}">
            </div>

            <div style="display:flex; gap:12px; flex-wrap:wrap;">
                <!-- is_active: مفعل/غير مفعل -->
                <label style="display:flex; align-items:center; gap:8px;">
                    <input type="checkbox" name="is_active" value="1" {{ $category->is_active ? 'checked' : '' }}>
                    <strong>مفعل</strong>
                </label>

                <!-- sort_order: ترتيب العرض -->
                <div style="flex:1; min-width:180px;">
                    <label class="label">ترتيب العرض</label>
                    <input class="input" type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order) }}" min="0">
                </div>
            </div>

            <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:6px;">
                <button class="btn" type="submit">حفظ التعديل</button>
                <a class="btn btn--ghost" href="{{ route('admin.categories.index') }}">رجوع</a>
            </div>
        </form>
    </div>

</div>
<script src="{{ asset('js/auto-translate.js') }}"></script>

</body>
</html>