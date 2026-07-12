<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة منتج - مأرب</title>
    <link rel="stylesheet" href="/css/variables.css">
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>

<div class="container" style="max-width:780px; margin-top:20px;">
    <div class="card">
        <h2 class="page-title" style="margin:0;">إضافة منتج</h2>
        <p class="page-subtitle" style="margin:6px 0 0;">املأ البيانات ثم احفظ.</p>

        @if($errors->any())
            <div class="alert alert--danger" style="margin-top:12px;">
                {{ $errors->first() }}
            </div>
        @endif
    </div>

    <div class="card" style="margin-top:14px;">
        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" style="display:grid; gap:12px;">
            @csrf

            <!-- التصنيف -->
            <div>
                <label class="label">التصنيف *</label>
                <select class="input" name="category_id" required>
                    <option value="">-- اختر التصنيف --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
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
                    value="{{ old('name') }}"
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
                        value="{{ old('name_en') }}"
                    >
                    <button type="button" class="btn translate-btn" data-from="name" data-to="name_en">
                        ترجمة تلقائية
                    </button>
                </div>
            </div>

            <!-- الوصف -->
            <div>
                <label class="label">الوصف</label>
                <textarea class="input" name="description" id="description" rows="4">{{ old('description') }}</textarea>
            </div>

            <div>
                <label class="label">الوصف (EN)</label>
                <button type="button" class="btn translate-btn" data-from="description" data-to="description_en" style="margin-bottom:6px;">
                    ترجمة تلقائية
                </button>
                <textarea class="input" name="description_en" id="description_en" rows="4">{{ old('description_en') }}</textarea>
            </div>

            <!-- السعر -->
            <div>
                <label class="label">السعر (ريال) *</label>
                <input class="input" type="number" name="price" value="{{ old('price', 0) }}" min="0" step="0.01" required>
            </div>
                
                            <!-- تفعيل الرسالة الخاصة -->
<div>
    <label>
        <input type="checkbox" name="has_special_message" value="1">
        تفعيل رسالة خاصة لهذا المنتج
    </label>
</div>

<!-- الرسالة العربية -->
<div style="margin-top:10px;">
    <label class="label">الرسالة للعميل</label>
    <textarea
        class="input"
        name="special_message"
        id="special_message"
        placeholder="اكتب الرسالة التي ستظهر للعميل"
    >{{ old('special_message') }}</textarea>
</div>

<!-- الرسالة الإنجليزية -->
<div>
    <label class="label">الرسالة (EN)</label>
    <div style="display:flex; gap:8px;">
        <textarea
            class="input"
            name="special_message_en"
            id="special_message_en"
        >{{ old('special_message_en') }}</textarea>

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
                <label class="label">Slug (اختياري)</label>
                <input class="input" type="text" name="slug" value="{{ old('slug') }}" placeholder="chicken-rice">
                <small style="opacity:.7;">إذا تركته فارغ سيتم توليده تلقائيا.</small>
            </div>

            <!-- الصورة -->
            <div>
                <label class="label">صورة المنتج (اختياري)</label>
                <input class="input" type="file" name="image" accept="image/*">
                <small style="opacity:.7;">jpg/png/webp حتى 2MB.</small>
            </div>

            <!-- مفعل + ترتيب -->
            <div style="display:flex; gap:12px; flex-wrap:wrap;">
                <label style="display:flex; align-items:center; gap:8px;">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active',1) ? 'checked' : '' }}>
                    <strong>مفعل</strong>
                </label>

                <div style="flex:1; min-width:180px;">
                    <label class="label">ترتيب العرض</label>
                    <input class="input" type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0">
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

                            <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:10px; border:1px solid rgba(0,0,0,.08); padding:12px; border-radius:14px;">
                                <label style="display:flex; align-items:center; gap:10px; margin:0; flex:1;">
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
                <button class="btn" type="submit">حفظ</button>
                <a class="btn btn--ghost" href="{{ route('admin.products.index') }}">رجوع</a>
            </div>

        </form>
    </div>
</div>

<script src="{{ asset('js/auto-translate.js') }}"></script>

</body>
</html>
