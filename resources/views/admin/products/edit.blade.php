<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل منتج - مطعم مأرب</title>

    <!-- متغيرات الألوان -->
    <link rel="stylesheet" href="/css/variables.css">
    <!-- تنسيق لوحة الإدارة -->
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>

<div class="container" style="max-width:780px; margin-top:20px;">

    <!-- العنوان -->
    <div class="card">
        <h2 class="page-title" style="margin:0;">تعديل منتج</h2>
        <p class="page-subtitle" style="margin:6px 0 0;">
            حدّث بيانات المنتج ثم احفظ التعديلات.
        </p>

        <!-- عرض أول خطأ إن وجد -->
        @if($errors->any())
            <div class="alert alert--danger" style="margin-top:12px;">
                {{ $errors->first() }}
            </div>
        @endif
    </div>

    <!-- فورم التعديل -->
    <div class="card" style="margin-top:14px;">
        <!-- enctype ضروري لرفع الصور -->
        <form 
            action="{{ route('admin.products.update', $product) }}" 
            method="POST" 
            enctype="multipart/form-data"
            style="display:grid; gap:12px;"
        >
            @csrf
            @method('PUT')

            <!-- اختيار التصنيف -->
            <div>
                <label class="label">التصنيف *</label>
                <select class="input" name="category_id" required>
                    <option value="">-- اختر التصنيف --</option>
                    @foreach($categories as $cat)
                        <option 
                            value="{{ $cat->id }}"
                            {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}
                        >
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- اسم المنتج -->
             <div>
                <label class="label">اسم المنتج *</label>
                <input
                    class="input"
                    type="text"
                    name="name"
                    id="name"
                    value="{{ old('name', $product->name ?? '') }}"
                    required
                >
            </div>

            <div>
                <label class="label">اسم المنتج (EN)</label>

                <div style="display:flex; gap:8px;">
                    <input
                        class="input"
                        type="text"
                        name="name_en"
                        id="name_en"
                        value="{{ old('name_en', $product->name_en ?? '') }}"
                    >

                    <button
                        type="button"
                        class="btn translate-btn"
                        data-from="name"
                        data-to="name_en">
                        ترجمة تلقائية
                    </button>
                </div>
            </div>


            <div>
                <label class="label">الوصف</label>
                            <textarea
                                class="input"
                                name="description"
                                id="description"
                                rows="4"
                            >{{ old('description', $product->description ?? '') }}</textarea>
                        </div>

                        <div>
                            <label class="label">الوصف (EN)</label>

                            <button
                                type="button"
                                class="btn translate-btn"
                                data-from="description"
                                data-to="description_en"
                                style="margin-bottom:6px;">
                                ترجمة تلقائية
                            </button>

                            <textarea
                                class="input"
                                name="description_en"
                                id="description_en"
                                rows="4"
                            >{{ old('description_en', $product->description_en ?? '') }}</textarea>
                        </div>


            <!-- السعر -->
            <div>
                <label class="label">السعر (ريال) *</label>
                <input 
                    class="input" 
                    type="number" 
                    name="price" 
                    value="{{ old('price', $product->price) }}" 
                    min="0" 
                    step="0.01" 
                    required
                >
            </div>
                            
   <!-- تفعيل الرسالة الخاصة -->
<div>
    <div style="margin-top:12px;">

<label class="label">
تفعيل الرسالة الخاصة
</label>

<label class="toggle-switch">

<input type="hidden" name="has_special_message" value="0">

<input type="checkbox" name="has_special_message" value="1"
{{ old('has_special_message', $product->has_special_message) ? 'checked' : '' }}>

<span class="toggle-slider"></span>

</label>

<span class="dark-text" style="margin-right:8px;">
عند التفعيل لن يستطيع العميل طلب المنتج
</span>

</div>
</div>

<!-- الرسالة العربية -->
<div style="margin-top:10px;">
    <label class="label">الرسالة للعميل</label>
    <textarea
        class="input"
        name="special_message"
        id="special_message"
        placeholder="اكتب الرسالة التي ستظهر للعميل"
    >{{ old('special_message', $product->special_message) }}</textarea>
</div>

<!-- الرسالة الإنجليزية -->
<div>
    <label class="label">الرسالة (EN)</label>

    <div style="display:flex; gap:8px;">
        <textarea
            class="input"
            name="special_message_en"
            id="special_message_en"
        >{{ old('special_message_en', $product->special_message_en) }}</textarea>

        <button 
            type="button" 
            class="btn translate-btn"
            data-from="special_message"
            data-to="special_message_en"
        >
            ترجمة تلقائية
        </button>
    </div>
</div>

            <!-- slug -->
            <div>
                <label class="label">Slug</label>
                <input 
                    class="input" 
                    type="text" 
                    name="slug" 
                    value="{{ old('slug', $product->slug) }}"
                >
                <small style="opacity:.7;">
                    الرابط الظاهر في المتصفح (يمكن تركه كما هو).
                </small>
            </div>

            <!-- صورة المنتج الحالية -->
            @if($product->image_path)
                <div>
                    <label class="label">الصورة الحالية</label>
                    <img 
                        src="/{{ $product->image_path }}" 
                        alt="صورة المنتج"
                        style="width:120px; height:120px; object-fit:cover; border-radius:14px; border:1px solid rgba(0,0,0,.1);"
                    >
                </div>
            @endif

            <!-- تغيير الصورة -->
            <div>
                <label class="label">تغيير الصورة (اختياري)</label>
                <input 
                    class="input" 
                    type="file" 
                    name="image" 
                    accept="image/*"
                >
                <small style="opacity:.7;">
                    jpg / png / webp – حد أقصى 2MB.
                </small>
            </div>

            <!-- مفعل + ترتيب -->
            <div style="display:flex; gap:12px; flex-wrap:wrap;">
                <!-- مفعل -->
                <label style="display:flex; align-items:center; gap:8px;">
                    <input 
                        type="checkbox" 
                        name="is_active" 
                        value="1"
                        {{ old('is_active', $product->is_active) ? 'checked' : '' }}
                    >
                    <strong>مفعل</strong>
                </label>

                <!-- ترتيب العرض -->
                <div style="flex:1; min-width:180px;">
                    <label class="label">ترتيب العرض</label>
                    <input 
                        class="input" 
                        type="number" 
                        name="sort_order" 
                        value="{{ old('sort_order', $product->sort_order) }}" 
                        min="0"
                    >
                </div>
            </div>
            <!-- ربط التخصيصات بالمنتج -->
            <div class="card" style="margin-top:14px;">
                <h3 class="page-title" style="margin:0; font-size:18px;">تخصيصات المنتج</h3>
                <p class="page-subtitle" style="margin:6px 0 0;">اختر مجموعات التخصيصات التي تظهر داخل صفحة المنتج.</p>

                @if(isset($allGroups) && $allGroups->count() > 0)
                    <div style="display:grid; gap:10px; margin-top:12px;">
                        @foreach($allGroups as $g)
                            <!-- isChecked: هل هذه المجموعة مرتبطة حاليا بالمنتج -->
                            @php
                                $isChecked = in_array($g->id, $attachedIds ?? []);
                                $sortVal = $attachedSortMap[$g->id] ?? 0;
                            @endphp

                            <div style="display:flex; align-items:center; flex-wrap:wrap; justify-content:space-between; gap:10px; border:1px solid rgba(0,0,0,.08); padding:12px; border-radius:14px;">
                                <label style="display:flex; align-items:center; gap:10px; margin:0;">
                                    <!-- option_groups[]: ids المجموعات المحددة -->
                                    <input type="checkbox" name="option_groups[]" value="{{ $g->id }}" {{ $isChecked ? 'checked' : '' }}>
                                <strong>
                                    {{ $g->name }}

                                    @if(!empty($g->admin_note))
                                        <span style="opacity:.7;">
                                            ({{ $g->admin_note }})
                                        </span>
                                    @endif
                                </strong>                                    <small style="opacity:.7;">
                                        {{ $g->is_required ? 'اجباري' : 'اختياري' }} • {{ $g->is_multiple ? 'متعدد' : 'اختيار واحد' }}
                                    </small>
                                </label>

                                <div style="display:flex; align-items:center; gap:10px;">
                                    <span style="opacity:.7;">ترتيب</span>
                                    <!-- group_sorts[ID]: رقم ترتيب ظهور المجموعة داخل صفحة المنتج -->
                                    <input class="input" style="width:110px; padding:10px 12px;" type="number" name="group_sorts[{{ $g->id }}]" value="{{ $sortVal }}" min="0">
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p style="margin-top:12px; opacity:.8;">لا توجد مجموعات تخصيصات حاليا.</p>
                @endif
            </div>

            <!-- أزرار -->
            <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:6px;">
                <button class="btn" type="submit">
                    حفظ التعديل
                </button>

                <a class="btn btn--ghost" href="{{ route('admin.products.index') }}">
                    رجوع
                </a>
            </div>

        </form>
    </div>

</div>
<script src="{{ asset('js/auto-translate.js') }}"></script>
</body>
</html>
