@csrf
@if(isset($branch))
    @method('PUT')
@endif

<div style="display:grid; gap:12px;">
    <div style="display:grid; gap:10px; grid-template-columns: repeat(2, minmax(0, 1fr));">
        <div>
            <label class="label">اسم الفرع</label>
            <input class="input" name="name" id="name" value="{{ old('name', $branch->name ?? '') }}" required>
        </div>
        <div>
            <label class="label">الاسم بالإنجليزية</label>
            <div style="display:flex; gap:8px;">
                <input class="input" name="name_en" id="name_en" value="{{ old('name_en', $branch->name_en ?? '') }}">
                <button type="button" class="btn translate-btn" data-from="name" data-to="name_en">ترجمة تلقائية</button>
            </div>
        </div>
    </div>

    <div style="display:grid; gap:10px; grid-template-columns: repeat(2, minmax(0, 1fr));">
        <div>
            <label class="label">العنوان</label>
            <input class="input" name="address" id="address" value="{{ old('address', $branch->address ?? '') }}">
        </div>
        <div>
            <label class="label">العنوان بالإنجليزية</label>
            <div style="display:flex; gap:8px;">
                <input class="input" name="address_en" id="address_en" value="{{ old('address_en', $branch->address_en ?? '') }}">
                <button type="button" class="btn translate-btn" data-from="address" data-to="address_en">ترجمة تلقائية</button>
            </div>
        </div>
    </div>

    <div style="display:grid; gap:10px; grid-template-columns: repeat(3, minmax(0, 1fr));">
        <div>
            <label class="label">هاتف الفرع</label>
            <input class="input" name="phone" value="{{ old('phone', $branch->phone ?? '') }}">
        </div>
        <div>
            <label class="label">واتساب الفرع</label>
            <input class="input" name="whatsapp_number" value="{{ old('whatsapp_number', $branch->whatsapp_number ?? '') }}">
        </div>
        <div>
            <label class="label">الترتيب</label>
            <input class="input" type="number" min="0" name="sort_order" value="{{ old('sort_order', $branch->sort_order ?? 0) }}">
        </div>
    </div>

    <div>
        <label class="label">رابط Google Maps للفرع</label>
        <input class="input" dir="ltr" name="google_maps_url" placeholder="https://maps.app.goo.gl/..." value="{{ old('google_maps_url', $branch->google_maps_url ?? '') }}">
        <small style="display:block; margin-top:6px; opacity:.78;">
            ألصق رابط Google Maps فقط ليتم استخراج إحداثيات الفرع داخليًا واستخدامها في التوصيل.
        </small>
    </div>

    <div style="display:grid; gap:10px; grid-template-columns: repeat(3, minmax(0, 1fr));">
        <div>
            <label class="label">الإحداثيات الحالية</label>
            <div class="input" style="background:#f8f8f8; min-height:44px; display:flex; align-items:center;">
                @if(isset($branch) && $branch->lat !== null && $branch->lng !== null)
                    <span dir="ltr">{{ number_format((float) $branch->lat, 7) }}, {{ number_format((float) $branch->lng, 7) }}</span>
                @else
                    <span style="opacity:.7;">سيتم استخراجها بعد الحفظ</span>
                @endif
            </div>
        </div>
        <div>
            <label class="label">سياسة التوصيل</label>
            <div class="input" style="background:#f8f8f8; min-height:44px; display:flex; align-items:center;">
                <span style="font-weight:600;">5.00 ريال ثابت حتى 4 كم</span>
            </div>
        </div>
        <div>
            <label class="label">جاهزية التوصيل</label>
            <div class="input" style="background:#f8f8f8; min-height:44px; display:flex; align-items:center;">
                @php
                    $deliveryReady = isset($branch) && $branch->delivery_enabled && $branch->lat !== null && $branch->lng !== null;
                @endphp
                <span style="font-weight:600; color:{{ $deliveryReady ? '#0a7a2f' : '#9a6700' }};">
                    {{ $deliveryReady ? 'جاهز للتوصيل' : 'بحاجة إلى إحداثيات أو تفعيل' }}
                </span>
            </div>
        </div>
    </div>

    <div>
        <label class="label">حالة الفرع</label>
        <div style="display:flex; gap:16px; flex-wrap:wrap; margin-top:8px;">
            <label><input type="checkbox" name="is_active" value="1" {{ old('is_active', $branch->is_active ?? true) ? 'checked' : '' }}> فرع نشط</label>
            <label><input type="checkbox" name="pickup_enabled" value="1" {{ old('pickup_enabled', $branch->pickup_enabled ?? true) ? 'checked' : '' }}> يدعم الاستلام</label>
            <label><input type="checkbox" name="delivery_enabled" value="1" {{ old('delivery_enabled', $branch->delivery_enabled ?? false) ? 'checked' : '' }}> يدعم التوصيل</label>
        </div>
    </div>

    <div style="display:flex; gap:10px; flex-wrap:wrap;">
        <button class="btn" type="submit">{{ isset($branch) ? 'حفظ التعديلات' : 'إضافة الفرع' }}</button>
        <a class="btn btn--ghost" href="{{ route('admin.branches.index') }}">رجوع</a>
    </div>
</div>

<style>
@media (max-width: 900px) {
    form [style*="grid-template-columns"] {
        grid-template-columns: 1fr !important;
    }
}
</style>
