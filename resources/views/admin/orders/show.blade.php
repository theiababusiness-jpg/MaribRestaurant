<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تفاصيل الطلب</title>
    <link rel="stylesheet" href="/css/variables.css">
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>
@php
    $statusLabels = [
        'pending' => 'قيد الانتظار',
        'paid' => 'مدفوع',
        'preparing' => 'قيد التجهيز',
        'ready_for_pickup' => 'جاهز للاستلام',
        'out_for_delivery' => 'خرج للتوصيل',
        'completed' => 'مكتمل',
        'cancelled' => 'ملغي',
        'failed_payment' => 'فشل الدفع',
    ];

    $fulfillmentLabels = [
        'pickup' => 'استلام من الفرع',
        'delivery' => 'توصيل',
    ];

    $paymentMethodLabels = [
        'cash' => 'كاش',
        'online' => 'أونلاين',
    ];

    $paymentStatusLabels = [
        'unpaid' => 'غير مدفوع',
        'pending' => 'بانتظار الدفع',
        'paid' => 'مدفوع',
        'failed' => 'فشل',
        'cancelled' => 'ملغي',
    ];

    $customerPhoneRaw = trim((string) $order->customer_phone);
    $customerPhoneDigits = preg_replace('/\D+/', '', $customerPhoneRaw);
    if (str_starts_with($customerPhoneDigits, '00')) {
        $customerPhoneDigits = substr($customerPhoneDigits, 2);
    }
    $customerPhoneTel = str_starts_with($customerPhoneRaw, '+')
        ? ('+' . $customerPhoneDigits)
        : $customerPhoneDigits;
    $customerWhatsappUrl = $customerPhoneDigits ? 'https://wa.me/' . $customerPhoneDigits : null;
    $customerCallUrl = $customerPhoneTel ? 'tel:' . $customerPhoneTel : null;
@endphp

<div class="container" style="margin-top:20px;">
    <div class="card">
        <div style="display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap; align-items:flex-start;">
            <div>
                <h2 class="page-title" style="margin:0;">طلب #{{ $order->id }}</h2>
                <p class="page-subtitle" style="margin:6px 0 0; display:flex; flex-wrap:wrap; gap:12px; align-items:center;">
                    <span>الكود: <strong>{{ $order->code }}</strong></span>
                    <span style="opacity:.5;">|</span>
                    <span>الفرع: <strong>{{ $order->branch?->name ?? 'غير محدد' }}</strong></span>
                    <span style="opacity:.5;">|</span>
                    <span>الإجمالي: <strong style="color:var(--primary-color);">{{ number_format((float) $order->total, 2) }} ريال</strong></span>
                    <span style="opacity:.5;">|</span>
                    <span style="padding:2px 8px; border-radius:999px; font-size:12px; font-weight:700; {{ in_array($order->status, ['completed', 'paid']) ? 'background:#d4edda; color:#155724;' : (in_array($order->status, ['cancelled', 'failed_payment']) ? 'background:#f8d7da; color:#721c24;' : 'background:#fff3cd; color:#856404;') }}">
                        {{ $statusLabels[$order->status] ?? $order->status }}
                    </span>
                </p>
            </div>
            <a class="btn btn--ghost" href="{{ route('admin.orders.index') }}">الرجوع للطلبات</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert--success" style="margin-top:14px;">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert--danger" style="margin-top:14px;">
            <ul style="margin:0; padding:0 18px;">
                @foreach($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="admin-grid" style="margin-top:14px; grid-template-columns:repeat(2, minmax(0, 1fr));">
        <div class="card">
            <h3 class="card__title" style="margin:0 0 16px;">بيانات الطلب</h3>
            <div style="display:grid; gap:12px;">
                <div style="display:flex; justify-content:space-between; align-items:center; padding:8px 0; border-bottom:1px solid var(--border);">
                    <span style="font-weight:600; color:var(--text-secondary);">العميل:</span>
                    <span>{{ $order->customer_name }}</span>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; padding:8px 0; border-bottom:1px solid var(--border);">
                    <span style="font-weight:600; color:var(--text-secondary);">الهاتف:</span>
                    <span>{{ $order->customer_phone }}</span>
                </div>
                @if($customerWhatsappUrl || $customerCallUrl)
                    <div style="display:flex; gap:8px; flex-wrap:wrap; padding:4px 0 12px; border-bottom:1px solid var(--border);">
                        @if($customerWhatsappUrl)
                            <a class="btn btn--small" href="{{ $customerWhatsappUrl }}" target="_blank" rel="noopener" style="background:#25D366; color:#fff;">
                                واتساب العميل
                            </a>
                        @endif
                        @if($customerCallUrl)
                            <a class="btn btn--small" href="{{ $customerCallUrl }}" style="background:var(--secondary-color); color:#fff;">
                                اتصال بالعميل
                            </a>
                        @endif
                    </div>
                @endif
                <div style="display:flex; justify-content:space-between; align-items:center; padding:8px 0; border-bottom:1px solid var(--border);">
                    <span style="font-weight:600; color:var(--text-secondary);">طريقة الاستلام:</span>
                    <span style="font-weight:700; color:var(--primary-color);">{{ $fulfillmentLabels[$order->fulfillment_method] ?? $order->fulfillment_method }}</span>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; padding:8px 0; border-bottom:1px solid var(--border);">
                    <span style="font-weight:600; color:var(--text-secondary);">طريقة الدفع:</span>
                    <span>{{ $paymentMethodLabels[$order->payment_method] ?? $order->payment_method }}</span>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; padding:8px 0; border-bottom:1px solid var(--border);">
                    <span style="font-weight:600; color:var(--text-secondary);">حالة الدفع:</span>
                    <span style="font-weight:700; {{ $order->payment_status === 'paid' ? 'color:#28a745;' : 'color:#dc3545;' }}">{{ $paymentStatusLabels[$order->payment_status] ?? $order->payment_status }}</span>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; padding:8px 0; border-bottom:1px solid var(--border);">
                    <span style="font-weight:600; color:var(--text-secondary);">حالة الطلب:</span>
                    <span style="font-weight:700; {{ in_array($order->status, ['completed', 'paid']) ? 'color:#28a745;' : (in_array($order->status, ['cancelled', 'failed_payment']) ? 'color:#dc3545;' : 'color:#ffc107;') }}">{{ $statusLabels[$order->status] ?? $order->status }}</span>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; padding:8px 0; border-bottom:1px solid var(--border);">
                    <span style="font-weight:600; color:var(--text-secondary);">إجمالي المنتجات:</span>
                    <span>{{ number_format((float) $order->items_subtotal, 2) }} ريال</span>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; padding:8px 0; border-bottom:1px solid var(--border);">
                    <span style="font-weight:600; color:var(--text-secondary);">رسوم التوصيل:</span>
                    <span>{{ number_format((float) $order->delivery_fee, 2) }} ريال</span>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:2px solid var(--primary-color);">
                    <span style="font-weight:800; color:var(--primary-color); font-size:16px;">الإجمالي النهائي:</span>
                    <span style="font-weight:800; color:var(--primary-color); font-size:16px;">{{ number_format((float) $order->total, 2) }} ريال</span>
                </div>
                @if($order->delivery_distance_km !== null)
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:8px 0;">
                        <span style="font-weight:600; color:var(--text-secondary);">المسافة:</span>
                        <span>{{ number_format((float) $order->delivery_distance_km, 2) }} كم</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="card">
            <h3 class="card__title" style="margin:0 0 16px;">بيانات الاستلام والتوصيل</h3>
            <div style="display:grid; gap:12px;">
                <div style="display:flex; justify-content:space-between; align-items:center; padding:8px 0; border-bottom:1px solid var(--border);">
                    <span style="font-weight:600; color:var(--text-secondary);">الفرع:</span>
                    <span>{{ $order->branch?->name ?? 'غير محدد' }}</span>
                </div>
                @if($order->branch?->phone)
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:8px 0; border-bottom:1px solid var(--border);">
                        <span style="font-weight:600; color:var(--text-secondary);">هاتف الفرع:</span>
                        <span>{{ $order->branch->phone }}</span>
                    </div>
                @endif

                @if($order->fulfillment_method === 'delivery')
                    <div style="padding:8px 0; border-bottom:1px solid var(--border);">
                        <div style="font-weight:600; color:var(--text-secondary); margin-bottom:4px;">العنوان:</div>
                        <div>{{ $order->customer_address ?: 'غير متوفر' }}</div>
                    </div>
                    @if($order->map_address)
                        <div style="padding:8px 0; border-bottom:1px solid var(--border);">
                            <div style="font-weight:600; color:var(--text-secondary); margin-bottom:4px;">وصف الموقع:</div>
                            <div>{{ $order->map_address }}</div>
                        </div>
                    @endif
                    @if($order->lat && $order->lng)
                        <div style="padding:8px 0; border-bottom:1px solid var(--border);">
                            <div style="font-weight:600; color:var(--text-secondary); margin-bottom:4px;">الإحداثيات:</div>
                            <div>{{ $order->lat }}, {{ $order->lng }}</div>
                            <div style="margin-top:8px;">
                                <a class="btn btn--small" target="_blank" href="https://www.google.com/maps?q={{ $order->lat }},{{ $order->lng }}" style="background:var(--secondary-color); color:#fff;">
                                    فتح في الخرائط
                                </a>
                            </div>
                        </div>
                    @endif
                @else
                    <div style="padding:8px 0; border-bottom:1px solid var(--border);">
                        <div style="font-weight:600; color:var(--text-secondary); margin-bottom:4px;">عنوان الاستلام:</div>
                        <div>{{ $order->branch?->address ?? 'غير محدد' }}</div>
                    </div>
                @endif

                @if($order->notes)
                    <div style="padding:8px 0;">
                        <div style="font-weight:600; color:var(--text-secondary); margin-bottom:4px;">ملاحظات العميل:</div>
                        <div style="background:rgba(0,0,0,.03); padding:10px; border-radius:8px;">{{ $order->notes }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="card" style="margin-top:14px;">
        <h3 class="card__title" style="margin:0 0 16px;">العناصر المطلوبة</h3>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr style="background:var(--primary-color); color:#fff;">
                        <th>المنتج</th>
                        <th style="text-align:center;">الكمية</th>
                        <th style="text-align:center;">سعر الوحدة</th>
                        <th style="text-align:center;">الإجمالي</th>
                        <th>الخيارات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        @php
                            $options = is_string($item->options_json) ? json_decode($item->options_json, true) : ($item->options_json ?? []);
                            $options = is_array($options) ? $options : [];
                        @endphp
                        <tr>
                            <td style="font-weight:700;">{{ $item->product_name }}</td>
                            <td style="text-align:center;">{{ $item->qty }}</td>
                            <td style="text-align:center;">{{ number_format((float) $item->unit_price, 2) }} ريال</td>
                            <td style="text-align:center; font-weight:700;">{{ number_format((float) $item->line_total, 2) }} ريال</td>
                            <td>
                                @if(empty($options))
                                    <span style="opacity:.65;">—</span>
                                @else
                                    <div style="display:flex; flex-wrap:wrap; gap:4px;">
                                        @foreach($options as $option)
                                            <span style="background:rgba(0,0,0,.04); padding:4px 8px; border-radius:999px; font-size:12px;">
                                                {{ is_array($option) ? ($option['name'] ?? 'خيار') : $option }}
                                                @if(is_array($option) && isset($option['price']))
                                                    <span style="color:var(--primary-color); font-weight:700;">(+{{ number_format((float) $option['price'], 2) }})</span>
                                                @endif
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if($order->payments->isNotEmpty())
        <div class="card" style="margin-top:14px;">
            <h3 class="card__title" style="margin:0 0 16px;">سجل الدفعات</h3>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr style="background:var(--secondary-color); color:#fff;">
                            <th>المرجع</th>
                            <th>المزود</th>
                            <th>الحالة</th>
                            <th style="text-align:center;">المبلغ</th>
                            <th>وقت الدفع</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->payments as $payment)
                            <tr>
                                <td style="font-family:monospace; font-size:14px;">{{ $payment->reference }}</td>
                                <td>{{ $payment->provider }}</td>
                                <td>
                                    <span style="padding:4px 8px; border-radius:999px; font-size:12px; font-weight:700; {{ $payment->status === 'paid' ? 'background:#d4edda; color:#155724;' : 'background:#f8d7da; color:#721c24;' }}">
                                        {{ $payment->status }}
                                    </span>
                                </td>
                                <td style="text-align:center; font-weight:700;">{{ number_format((float) $payment->amount, 2) }} {{ $payment->currency }}</td>
                                <td>{{ optional($payment->paid_at)->format('Y-m-d H:i') ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="card" style="margin-top:14px;">
        <h3 class="card__title" style="margin:0 0 16px;">تحديث حالة الطلب</h3>
        <div style="background:rgba(0,0,0,.03); padding:16px; border-radius:12px; border:1px solid var(--border);">
            <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="order-status-form">
                @csrf
                @method('PUT')
                <div class="order-status-form__group order-status-form__group--current">
                    <label style="font-weight:700; color:var(--text-secondary);">الحالة الحالية:</label>
                    <span style="font-weight:700; padding:4px 10px; border-radius:999px; background:#fff; border:1px solid var(--border);">
                        {{ $statusLabels[$order->status] ?? $order->status }}
                    </span>
                </div>
                <div class="order-status-form__group order-status-form__group--select">
                    <label style="font-weight:700;">تغيير إلى:</label>
                    <div class="order-status-dropdown" data-status-dropdown>
                        <input type="hidden" name="status" value="{{ $order->status }}" data-status-input>
                        <button type="button" class="input order-status-dropdown__trigger" data-status-trigger aria-haspopup="listbox" aria-expanded="false">
                            <span data-status-label>{{ $statusLabels[$order->status] ?? $order->status }}</span>
                            <span class="order-status-dropdown__arrow" aria-hidden="true">▴</span>
                        </button>
                        <div class="order-status-dropdown__menu" data-status-menu role="listbox">
                            @foreach($statusLabels as $value => $label)
                                <button
                                    type="button"
                                    class="order-status-dropdown__option {{ $order->status === $value ? 'is-selected' : '' }}"
                                    data-status-option
                                    data-value="{{ $value }}"
                                    data-label="{{ $label }}"
                                    role="option"
                                    aria-selected="{{ $order->status === $value ? 'true' : 'false' }}"
                                >
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
                <button class="btn order-status-form__submit" type="submit">حفظ التغيير</button>
            </form>
        </div>
    </div>
</div>

<style>
.order-status-form {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    align-items: center;
}

.order-status-form__group {
    display: flex;
    align-items: center;
    gap: 8px;
}

.order-status-form__group--select {
    flex: 1 1 280px;
}

.order-status-dropdown {
    position: relative;
    min-width: 240px;
    width: 100%;
}

.order-status-dropdown__trigger {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    text-align: right;
    cursor: pointer;
}

.order-status-dropdown__arrow {
    flex: 0 0 auto;
    font-size: 14px;
    transition: transform .18s ease;
}

.order-status-dropdown.is-open .order-status-dropdown__arrow {
    transform: rotate(180deg);
}

.order-status-dropdown__menu {
    position: absolute;
    inset-inline: 0;
    bottom: calc(100% + 8px);
    display: none;
    background: #fff;
    border: 1px solid rgba(0,0,0,.12);
    border-radius: 14px;
    box-shadow: 0 12px 28px rgba(0,0,0,.12);
    padding: 8px;
    z-index: 30;
    max-height: 260px;
    overflow-y: auto;
}

.order-status-dropdown.is-open .order-status-dropdown__menu {
    display: grid;
    gap: 6px;
}

.order-status-dropdown__option {
    width: 100%;
    border: 0;
    background: rgba(0,0,0,.03);
    border-radius: 10px;
    padding: 10px 12px;
    text-align: right;
    font: inherit;
    cursor: pointer;
    color: inherit;
}

.order-status-dropdown__option:hover,
.order-status-dropdown__option.is-selected {
    background: rgba(122, 62, 120, .12);
    color: var(--primary-color);
    font-weight: 700;
}

@media (max-width: 1024px) {
    .admin-grid {
        grid-template-columns: 1fr !important;
    }

    .order-status-form {
        flex-direction: column;
        align-items: stretch;
    }

    .order-status-form__group {
        flex-direction: column;
        align-items: stretch;
    }

    .order-status-form__group--select,
    .order-status-dropdown,
    .order-status-form__submit {
        width: 100%;
        min-width: 0;
    }
}

@media (max-width: 768px) {
    .page-subtitle {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
    }

    .order-status-form {
        flex-direction: column;
        align-items: stretch;
    }

    .order-status-form__group {
        flex-direction: column;
        align-items: stretch;
    }

    .order-status-form__group--select,
    .order-status-dropdown,
    .order-status-form__submit {
        width: 100%;
        min-width: 0;
    }

    .table th,
    .table td {
        font-size: 14px;
    }
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-status-dropdown]').forEach(function (dropdown) {
        const trigger = dropdown.querySelector('[data-status-trigger]');
        const menu = dropdown.querySelector('[data-status-menu]');
        const input = dropdown.querySelector('[data-status-input]');
        const label = dropdown.querySelector('[data-status-label]');
        const options = Array.from(dropdown.querySelectorAll('[data-status-option]'));

        function closeDropdown() {
            dropdown.classList.remove('is-open');
            trigger?.setAttribute('aria-expanded', 'false');
        }

        function openDropdown() {
            dropdown.classList.add('is-open');
            trigger?.setAttribute('aria-expanded', 'true');
        }

        trigger?.addEventListener('click', function () {
            if (dropdown.classList.contains('is-open')) {
                closeDropdown();
            } else {
                openDropdown();
            }
        });

        options.forEach(function (option) {
            option.addEventListener('click', function () {
                const value = option.getAttribute('data-value') || '';
                const text = option.getAttribute('data-label') || option.textContent.trim();

                input.value = value;
                if (label) {
                    label.textContent = text;
                }

                options.forEach(function (item) {
                    item.classList.remove('is-selected');
                    item.setAttribute('aria-selected', 'false');
                });

                option.classList.add('is-selected');
                option.setAttribute('aria-selected', 'true');
                closeDropdown();
            });
        });

        document.addEventListener('click', function (event) {
            if (!dropdown.contains(event.target)) {
                closeDropdown();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeDropdown();
            }
        });
    });
});
</script>
</body>
</html>
