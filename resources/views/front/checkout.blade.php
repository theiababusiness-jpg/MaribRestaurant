@extends('layouts.app')

@php
    use App\Support\FrontLang;

    $selectedBranch = $branches->firstWhere('id', (int) old('branch_id'))
        ?? $branches->firstWhere('delivery_enabled', true)
        ?? $branches->first();
    $selectedBranchId = old('branch_id', $selectedBranch?->id);
    $selectedFulfillmentMethod = old('fulfillment_method', $defaultFulfillmentMethod);
    $selectedPaymentMethod = old('payment_method', 'cash');

    $branchMapData = $branches->map(fn ($branch) => [
        'id' => $branch->id,
        'name' => FrontLang::db($branch->name, $branch->name_en) ?: $branch->name,
        'address' => FrontLang::db($branch->address, $branch->address_en) ?: '',
        'google_maps_url' => $branch->google_maps_url ?: '',
        'lat' => $branch->lat,
        'lng' => $branch->lng,
        'pickup_enabled' => (bool) $branch->pickup_enabled,
        'delivery_enabled' => (bool) $branch->delivery_enabled,
    ])->values();

    $texts = [
        'currency' => FrontLang::t('ريال', 'SAR'),
        'km' => FrontLang::t('كم', 'km'),
        'na' => FrontLang::t('غير متوفر', 'Not available'),
        'select_branch' => FrontLang::t('اختر الفرع أولًا.', 'Choose a branch first.'),
        'branch_ready' => FrontLang::t('تم تحديد موقع الفرع. اختر موقع العميل على الخريطة.', 'Branch location is ready. Choose the customer location on the map.'),
        'pickup_hint' => FrontLang::t('تم اختيار الاستلام من الفرع، والخريطة للمعاينة فقط.', 'Pickup is selected, so the map is only for preview.'),
        'location_pending' => FrontLang::t('جاري جلب وصف الموقع وحساب المسافة...', 'Fetching location details and distance...'),
        'delivery_ok' => FrontLang::t('الموقع داخل نطاق التوصيل.', 'The location is inside the delivery range.'),
        'delivery_bad' => FrontLang::t('الموقع خارج نطاق التوصيل.', 'The location is outside the delivery range.'),
        'delivery_rule' => FrontLang::t('التوصيل حتى 4 كم فقط وبسعر ثابت 5 ريال.', 'Delivery is available up to 4 km only with a fixed 5 SAR fee.'),
        'location_required' => FrontLang::t('حدد موقع العميل على الخريطة لإكمال طلب التوصيل.', 'Select the customer location on the map to complete the delivery order.'),
        'map_key_missing' => FrontLang::t('إعدادات الخريطة غير مكتملة.', 'Map settings are incomplete.'),
        'map_load_failed' => FrontLang::t('تعذر تحميل الخريطة.', 'The map could not be loaded.'),
        'search_empty' => FrontLang::t('اكتب اسم منطقة أو شارع ثم اضغط بحث.', 'Enter an area or street name, then search.'),
        'search_failed' => FrontLang::t('لم يتم العثور على نتيجة مطابقة.', 'No matching result was found.'),
        'my_location_failed' => FrontLang::t('تعذر الوصول إلى موقعك الحالي.', 'Unable to access your current location.'),
        'pickup_disabled' => FrontLang::t('الفرع المختار لا يدعم الاستلام.', 'The selected branch does not support pickup.'),
        'delivery_disabled' => FrontLang::t('الفرع المختار لا يدعم التوصيل.', 'The selected branch does not support delivery.'),
        'branch_coords_missing' => FrontLang::t('هذا الفرع لا يحتوي على إحداثيات صالحة بعد.', 'This branch does not have valid coordinates yet.'),
        'checkout_not_ready' => FrontLang::t('يلزم تشغيل ترحيلات قاعدة البيانات الأخيرة أولًا.', 'Run the latest database migrations first.'),
        'submit_ready' => FrontLang::t('يمكنك الآن إتمام الطلب.', 'You can now complete the order.'),
    ];
@endphp

@section('content')
<div class="card" style="max-width:1200px; margin:0 auto;">
    <h2 class="page-title">{{ FrontLang::t('إتمام الطلب', 'Checkout') }}</h2>
    <p class="page-subtitle">{{ FrontLang::t('اختر الفرع، شاهد موقع المطعم على الخريطة، ثم حدد موقع العميل ليتم حساب المسافة ورسوم التوصيل تلقائيًا.', 'Choose a branch, view the restaurant on the map, then set the customer location so distance and delivery fee are calculated automatically.') }}</p>

    @if($errors->any())
        <div class="alert alert--danger" style="margin-top:14px;">
            <ul style="margin:0; padding:0 18px;">
                @foreach($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(! $checkoutSetupReady)
        <div class="alert alert--danger" style="margin-top:14px;">{{ $texts['checkout_not_ready'] }}</div>
    @endif

    @if($branches->isEmpty())
        <div class="alert alert--danger" style="margin-top:14px;">{{ FrontLang::t('لا توجد فروع نشطة متاحة حاليًا.', 'There are no active branches available right now.') }}</div>
    @endif

    <form id="checkoutForm" action="{{ route('checkout.store') }}" method="POST" style="margin-top:14px;">
        @csrf

        <div class="checkout-grid">
            <div class="checkout-main">
                <div class="card checkout-block">
                    <div class="checkout-two">
                        <div>
                            <label class="checkout-label">{{ FrontLang::t('اختر الفرع', 'Choose branch') }}</label>
                            <select class="input" id="branchSelect" name="branch_id" required>
                                <option value="">{{ FrontLang::t('اختر فرعًا', 'Select a branch') }}</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ (string) $selectedBranchId === (string) $branch->id ? 'selected' : '' }}>{{ FrontLang::db($branch->name, $branch->name_en) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="checkout-branch-box">
                            <strong id="branchNamePreview">{{ $selectedBranch ? FrontLang::db($selectedBranch->name, $selectedBranch->name_en) : $texts['na'] }}</strong>
                            <div id="branchAddressPreview" class="checkout-note">{{ $selectedBranch ? (FrontLang::db($selectedBranch->address, $selectedBranch->address_en) ?: $texts['na']) : $texts['na'] }}</div>
                            <!-- <a id="branchMapLink" href="{{ $selectedBranch?->google_maps_url ?: '#' }}" target="_blank" rel="noopener" class="{{ $selectedBranch && $selectedBranch->google_maps_url ? '' : 'checkout-hidden' }}">{{ FrontLang::t('فتح موقع الفرع', 'Open branch location') }}</a> -->
                        </div>
                    </div>

                    <div class="checkout-two" style="margin-top:12px;">
                        <div>
                            <label class="checkout-label">{{ FrontLang::t('طريقة الاستلام', 'Fulfillment') }}</label>
                            <div class="checkout-choice-row">
                                <label class="checkout-choice"><input id="fulfillmentDelivery" type="radio" name="fulfillment_method" value="delivery" {{ $selectedFulfillmentMethod === 'delivery' ? 'checked' : '' }}> {{ FrontLang::t('توصيل', 'Delivery') }}</label>
                                <label class="checkout-choice"><input id="fulfillmentPickup" type="radio" name="fulfillment_method" value="pickup" {{ $selectedFulfillmentMethod === 'pickup' ? 'checked' : '' }}> {{ FrontLang::t('استلام', 'Pickup') }}</label>
                            </div>
                        </div>
                        <div>
                            <label class="checkout-label">{{ FrontLang::t('طريقة الدفع', 'Payment') }}</label>
                            <div class="checkout-choice-row">
                                <label class="checkout-choice"><input type="radio" name="payment_method" value="cash" {{ $selectedPaymentMethod === 'cash' ? 'checked' : '' }}> {{ FrontLang::t('كاش', 'Cash') }}</label>
                                <label class="checkout-choice {{ $onlinePaymentAvailable ? '' : 'checkout-disabled' }}"><input type="radio" name="payment_method" value="online" {{ $selectedPaymentMethod === 'online' ? 'checked' : '' }} {{ $onlinePaymentAvailable ? '' : 'disabled' }}> {{ FrontLang::t('دفع إلكتروني', 'Online') }}</label>
                            </div>
                            @if($onlinePaymentAvailable)
                                <div class="checkout-note" style="margin-top:8px;">{{ FrontLang::t('عند اختيار الدفع الإلكتروني سيتم تحويلك إلى صفحة مويسر لإتمام العملية بشكل آمن.', 'When you choose online payment, you will be redirected to Moyasar to finish the transaction securely.') }}</div>
                            @endif
                        </div>
                    </div>

                    <div class="checkout-two" style="margin-top:12px;">
                        <div>
                            <label class="checkout-label">{{ FrontLang::t('الاسم الكامل', 'Full name') }}</label>
                            <input class="input" name="full_name" value="{{ old('full_name') }}" required>
                        </div>
                        <div>
                            <label class="checkout-label">{{ FrontLang::t('رقم الجوال', 'Phone number') }}</label>
                            <input class="input" name="phone" value="{{ old('phone') }}" required>
                        </div>
                    </div>

                    <div style="margin-top:12px;">
                        <label class="checkout-label">{{ FrontLang::t('تفاصيل إضافية للعنوان', 'Additional address details') }}</label>
                        <input class="input" name="address" value="{{ old('address') }}" placeholder="{{ FrontLang::t('مثال: رقم الشقة، اسم المبنى، أقرب معلم...', 'Example: apartment number, building name, nearby landmark...') }}">
                    </div>

                    <div style="margin-top:12px;">
                        <label class="checkout-label">{{ FrontLang::t('ملاحظات الطلب', 'Order notes') }}</label>
                        <textarea class="input" name="notes" rows="4">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="card checkout-block">
                    <div class="checkout-toolbar">
                        <input id="searchQuery" class="input" type="text" placeholder="{{ FrontLang::t('ابحث عن حي أو شارع أو موقع...', 'Search for an area, street, or place...') }}">
                        <button id="btnSearch" type="button" class="btn">{{ FrontLang::t('بحث', 'Search') }}</button>
                        <button id="btnMyLocation" type="button" class="btn" style="background:var(--secondary-color);">{{ FrontLang::t('موقعي', 'My location') }}</button>
                    </div>

                    <div id="mapStatus" class="checkout-status">{{ $selectedBranch ? $texts['branch_ready'] : $texts['select_branch'] }}</div>
                    <div id="googleMap" class="checkout-map"></div>

                    <div class="checkout-meta">
                        <div><span>{{ FrontLang::t('وصف الموقع', 'Location description') }}</span><strong id="metaAddress">{{ old('map_address', $texts['na']) }}</strong></div>
                        <div><span>{{ FrontLang::t('الإحداثيات', 'Coordinates') }}</span><strong id="metaCoordinates">{{ old('lat') && old('lng') ? old('lat').', '.old('lng') : $texts['na'] }}</strong></div>
                        <div><span>{{ FrontLang::t('المسافة من الفرع', 'Distance from branch') }}</span><strong id="metaDistance">{{ $texts['na'] }}</strong></div>
                        <div><span>{{ FrontLang::t('رسوم التوصيل', 'Delivery fee') }}</span><strong id="metaDeliveryFee">{{ number_format((float) $deliveryRules['fee'], 2) }} {{ $texts['currency'] }}</strong></div>
                        <div><span>{{ FrontLang::t('الحالة', 'Status') }}</span><strong id="metaDeliveryState">{{ $texts['delivery_rule'] }}</strong></div>
                    </div>

                    <input type="hidden" name="lat" id="latInput" value="{{ old('lat') }}">
                    <input type="hidden" name="lng" id="lngInput" value="{{ old('lng') }}">
                    <input type="hidden" name="map_address" id="mapAddressInput" value="{{ old('map_address') }}">
                </div>
            </div>

            <div class="checkout-side">
                <div class="card checkout-block">
                    <h3 class="card__title">{{ FrontLang::t('ملخص الطلب', 'Order summary') }}</h3>
                    <div class="checkout-summary">
                        @foreach($cart as $it)
                            @php $qty = max(1, (int) ($it['qty'] ?? 1)); $lineTotal = (float) ($it['final_price'] ?? 0); @endphp
                            <div class="checkout-summary-item">
                                <strong>{{ FrontLang::t($it['name'] ?? 'منتج', $it['name_en'] ?? ($it['name'] ?? 'Product')) }}</strong>
                                <span>{{ $qty }} x {{ number_format($lineTotal, 2) }} {{ $texts['currency'] }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="checkout-total">
                        <div><span>{{ FrontLang::t('المجموع الفرعي', 'Subtotal') }}</span><strong>{{ number_format((float) $itemsSubtotal, 2) }} {{ $texts['currency'] }}</strong></div>
                        <div><span>{{ FrontLang::t('رسوم التوصيل', 'Delivery fee') }}</span><strong id="deliveryFeeValue">0.00 {{ $texts['currency'] }}</strong></div>
                        <div class="checkout-total-grand"><span>{{ FrontLang::t('الإجمالي', 'Total') }}</span><strong id="grandTotalValue">{{ number_format((float) $itemsSubtotal, 2) }} {{ $texts['currency'] }}</strong></div>
                    </div>

                    <button id="checkoutSubmit" type="submit" class="btn" style="width:100%; margin-top:12px;">{{ FrontLang::t('تأكيد الطلب', 'Confirm order') }}</button>
                    <div id="submitHelp" class="checkout-note" style="margin-top:10px;">{{ $texts['submit_ready'] }}</div>
                    <a href="{{ route('cart.index') }}" class="btn" style="width:100%; margin-top:10px; background:var(--secondary-color);">{{ FrontLang::t('الرجوع إلى السلة', 'Back to cart') }}</a>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
.checkout-grid{display:grid;grid-template-columns:minmax(0,1.45fr) minmax(320px,.85fr);gap:14px}.checkout-main,.checkout-side{display:grid;gap:14px}.checkout-block{padding:18px}.checkout-two{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.checkout-label{display:block;margin-bottom:6px;font-weight:700}.checkout-branch-box,.checkout-meta>div,.checkout-summary-item,.checkout-total{border:1px solid rgba(0,0,0,.08);border-radius:14px;padding:12px;background:rgba(0,0,0,.02)}.checkout-note{opacity:.75;font-size:13px}.checkout-choice-row{display:flex;gap:10px;flex-wrap:wrap}.checkout-choice{display:inline-flex;align-items:center;gap:8px;padding:10px 12px;border:1px solid rgba(0,0,0,.08);border-radius:12px}.checkout-disabled{opacity:.45}.checkout-hidden{display:none}.checkout-toolbar{display:grid;grid-template-columns:minmax(0,1fr) auto auto;gap:10px}.checkout-status{margin-top:12px;padding:12px 14px;border-radius:14px;background:#f7f4eb;color:#6a4b13;font-weight:600}.checkout-status.is-success{background:rgba(18,115,67,.1);color:#127343}.checkout-status.is-error{background:rgba(176,0,32,.08);color:#b00020}.checkout-map{margin-top:12px;width:100%;height:400px;border-radius:18px;overflow:hidden;border:1px solid rgba(0,0,0,.08);background:#d7dde3}.checkout-map .leaflet-tile,.checkout-map .leaflet-marker-icon,.checkout-map .leaflet-marker-shadow{max-width:none!important;max-height:none!important}.checkout-map img{max-width:none!important}.checkout-meta,.checkout-summary,.checkout-total{display:grid;gap:10px;margin-top:12px}.checkout-meta span,.checkout-summary-item span,.checkout-total span{opacity:.72;font-size:13px;display:block}.checkout-total-grand{padding-top:10px;border-top:1px solid rgba(0,0,0,.08);font-size:18px}@media(max-width:1100px){.checkout-grid{grid-template-columns:1fr}}@media(max-width:768px){.checkout-two,.checkout-toolbar{grid-template-columns:1fr}.checkout-map{height:340px}}
</style>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script>
window.checkoutConfig = {
    branches: @json($branchMapData),
    quoteUrl: @json(route('checkout.delivery_quote')),
    refreshCsrfUrl: @json(route('checkout.csrf_token')),
    csrfToken: @json(csrf_token()),
    deliveryFee: Number(@json((float) $deliveryRules['fee'])),
    maxDistanceKm: Number(@json((float) $deliveryRules['max_distance_km'])),
    itemsSubtotal: Number(@json((float) $itemsSubtotal)),
    checkoutReady: @json((bool) $checkoutSetupReady),
    initialBranchId: @json($selectedBranchId ? (string) $selectedBranchId : ''),
    oldLocation: { lat: @json(old('lat')), lng: @json(old('lng')), mapAddress: @json(old('map_address')) },
    texts: @json($texts),
};
</script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="{{ asset('js/checkout-map.js') }}?v={{ @filemtime(public_path('js/checkout-map.js')) }}"></script>
@endsection
