<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل عرض - مأرب</title>

    <link rel="stylesheet" href="/css/variables.css">
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>

<div class="container" style="margin-top:20px; max-width:820px;">

    {{-- رأس الصفحة --}}
    <div class="card">
        <h2 class="page-title" style="margin:0;">تعديل عرض</h2>
        <p class="page-subtitle" style="margin:6px 0 0;">
            عدّل البيانات ثم احفظ.
        </p>

        @if($errors->any())
            <div class="alert alert--danger" style="margin-top:12px;">
                {{ $errors->first() }}
            </div>
        @endif
    </div>

    {{-- الفورم --}}
    <div class="card" style="margin-top:14px;">
        <form action="{{ route('admin.banners.update', $banner->id) }}"
              method="POST"
              enctype="multipart/form-data"
              style="display:grid; gap:12px;">

            @csrf
            @method('PUT')

            {{-- الصورة الحالية --}}
            @if(!empty($banner->image_path))
                <div>
                    <label class="label">الصورة الحالية</label>
    <img src="{{ asset($banner->image_path) }}"
    alt="banner"
    style="width:100%; max-height:220px; object-fit:cover; border-radius:14px;">


                </div>
            @endif

            {{-- تغيير الصورة --}}
            <div>
                <label class="label">تغيير الصورة (اختياري)</label>
                <input class="input" type="file" name="image" accept="image/*">
            </div>

            {{-- العنوان --}}
            <div>
                <label class="label">العنوان</label>
                <input class="input"
                       type="text"
                       name="title"
                       id="title"
                       value="{{ old('title', $banner->title) }}">
            </div>

            <div>
                <label class="label">العنوان (EN)</label>
                <div style="display:flex; gap:8px;">
                    <input class="input"
                           type="text"
                           name="title_en"
                           id="title_en"
                           value="{{ old('title_en', $banner->title_en) }}">
                    <button type="button"
                            class="btn translate-btn"
                            data-from="title"
                            data-to="title_en">
                        ترجمة تلقائية
                    </button>
                </div>
            </div>

            {{-- العنوان الفرعي --}}
            <div>
                <label class="label">العنوان الفرعي</label>
                <textarea class="input"
                          name="subtitle"
                          id="subtitle">{{ old('subtitle', $banner->subtitle) }}</textarea>
            </div>

            <div>
                <label class="label">العنوان الفرعي (EN)</label>
                <button type="button"
                        class="btn translate-btn"
                        data-from="subtitle"
                        data-to="subtitle_en"
                        style="margin-bottom:6px;">
                    ترجمة تلقائية
                </button>
                <textarea class="input"
                          name="subtitle_en"
                          id="subtitle_en">{{ old('subtitle_en', $banner->subtitle_en) }}</textarea>
            </div>

            <hr style="opacity:.15;">

            {{-- مدة العرض --}}
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                <div>
                    <label class="label">تاريخ بداية العرض</label>
                    <input class="input"
                           type="datetime-local"
                           name="start_at"
                           value="{{ old('start_at', $banner->start_at ? $banner->start_at->format('Y-m-d\TH:i') : '') }}">
                </div>

                <div>
                    <label class="label">تاريخ نهاية العرض</label>
                    <input class="input"
                           type="datetime-local"
                           name="end_at"
                           value="{{ old('end_at', $banner->end_at ? $banner->end_at->format('Y-m-d\TH:i') : '') }}">
                </div>
            </div>

            <small style="opacity:.7;">
                عند انتهاء المدة سيتم إيقاف العرض تلقائيًا (برمجيًا).
            </small>

            <hr style="opacity:.15;">

            {{-- نوع زر الانتقال --}}
            @php
                $lt  = old('link_type', $banner->link_type ?? 'none');
                $pid = old('product_id', $banner->product_id);
            @endphp

            <div>
                <label class="label">زر الانتقال (اختياري)</label>
                <select class="input" name="link_type" id="linkType">
                    <option value="none" {{ $lt=='none'?'selected':'' }}>بدون زر</option>
                    <option value="menu" {{ $lt=='menu'?'selected':'' }}>يفتح المنيو</option>
                    <option value="product" {{ $lt=='product'?'selected':'' }}>يفتح وجبة معينة</option>
                </select>
            </div>

            {{-- اختيار المنتج --}}
            <div id="productBox" style="display:none;">
                <label class="label">اختر الوجبة</label>
                <select class="input" name="product_id">
                    <option value="">— اختر —</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}"
                            {{ (string)$pid === (string)$p->id ? 'selected' : '' }}>
                            {{ $p->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- نص الزر --}}
            <div id="btnTextBox">
                <label class="label">نص الزر</label>
                <input class="input"
                    type="text"
                    name="link_text"
                    id="link_text"
                    value="{{ old('link_text', $banner->link_text) }}">
            </div>

            <div>
                <label class="label">نص الزر (EN)</label>
                <div style="display:flex; gap:8px;">
                    <input class="input"
                        type="text"
                        name="link_text_en"
                        id="link_text_en"
                        value="{{ old('link_text_en', $banner->link_text_en) }}">
                    <button type="button"
                            class="btn translate-btn"
                            data-from="link_text"
                            data-to="link_text_en">
                        ترجمة تلقائية
                    </button>
                </div>
            </div>


            {{-- تفعيل --}}
            <label style="display:flex; align-items:center; gap:8px;">
                <input type="checkbox"
                       name="is_active"
                       value="1"
                       {{ old('is_active', $banner->is_active) ? 'checked' : '' }}>
                <strong>مفعل يدويًا</strong>
            </label>

            {{-- الترتيب --}}
            <div>
                <label class="label">ترتيب العرض</label>
                <input class="input"
                       type="number"
                       name="sort_order"
                       min="0"
                       value="{{ old('sort_order', $banner->sort_order ?? 0) }}">
            </div>

            {{-- أزرار --}}
            <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:6px;">
                <button class="btn" type="submit">حفظ التعديل</button>
                <a class="btn btn--ghost" href="{{ route('admin.banners.index') }}">رجوع</a>
            </div>

        </form>
    </div>
</div>

<script src="{{ asset('js/auto-translate.js') }}"></script>

<script>
const linkType   = document.getElementById('linkType');
const productBox = document.getElementById('productBox');
const btnTextBox = document.getElementById('btnTextBox');

function syncLinkUI(){
    const v = linkType.value;
    productBox.style.display = (v === 'product') ? 'block' : 'none';
    btnTextBox.style.display = (v === 'menu' || v === 'product') ? 'block' : 'none';
}

linkType.addEventListener('change', syncLinkUI);
syncLinkUI();
</script>

</body>
</html>
