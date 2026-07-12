<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Orders\DeliveryQuoteService;
use App\Services\Payments\MoyasarGateway;
use App\Support\FrontLang;
use App\Support\SeoData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class CheckoutController extends Controller
{
    public function index(MoyasarGateway $gateway)
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')
                ->with('success', FrontLang::t('السلة فارغة، أضف منتجات أولًا', 'Your cart is empty. Add items first.'));
        }

        $branches = $this->branchesFeatureReady()
            ? Branch::query()
                ->active()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
            : collect();

        $itemsSubtotal = collect($this->normalizeCart($cart))->sum('line_total');
        $defaultFulfillmentMethod = $branches->contains(fn (Branch $branch) => $branch->delivery_enabled)
            ? 'delivery'
            : 'pickup';

        $seo = SeoData::make([
            'title' => FrontLang::t('إتمام الطلب | مطاعم مأرب', 'Checkout | Marib Restaurant'),
            'description' => FrontLang::t('أكمل بيانات طلبك وحدد طريقة الدفع والاستلام المناسبة.', 'Complete your order details and choose your preferred payment and fulfillment methods.'),
            'robots' => 'noindex,nofollow',
        ]);

        return response()
            ->view('front.checkout', [
            'cart' => $cart,
            'branches' => $branches,
            'itemsSubtotal' => $itemsSubtotal,
            'defaultFulfillmentMethod' => $defaultFulfillmentMethod,
            'onlinePaymentAvailable' => $gateway->isConfigured(),
            'checkoutSetupReady' => $this->checkoutSchemaReady(),
            'deliveryRules' => [
                'max_distance_km' => DeliveryQuoteService::MAX_DISTANCE_KM,
                'fee' => DeliveryQuoteService::FIXED_DELIVERY_FEE,
            ],
            'googleMapsKey' => config('services.google_maps.key'),
            'seo' => $seo,
        ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Fri, 01 Jan 1990 00:00:00 GMT');
    }

    public function refreshCsrfToken(Request $request)
    {
        $request->session()->regenerateToken();

        return response()->json([
            'token' => csrf_token(),
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    public function quoteDelivery(Request $request, DeliveryQuoteService $deliveryQuoteService)
    {
        if (! $this->branchesFeatureReady()) {
            return response()->json([
                'message' => FrontLang::t(
                    'إعدادات الفروع والتوصيل غير مكتملة بعد في قاعدة البيانات.',
                    'Branch and delivery setup is not ready in the database yet.'
                ),
            ], 503);
        }

        $data = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'lat' => ['required', 'numeric'],
            'lng' => ['required', 'numeric'],
        ]);

        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return response()->json(['message' => 'Cart is empty.'], 422);
        }

        $branch = Branch::query()->active()->findOrFail($data['branch_id']);
        $itemsSubtotal = collect($this->normalizeCart($cart))->sum('line_total');

        try {
            $quote = $deliveryQuoteService->quote($branch, (float) $data['lat'], (float) $data['lng'], (float) $itemsSubtotal);
        } catch (Throwable $exception) {
            return response()->json([
                'message' => FrontLang::t(
                    'التوصيل غير متاح لهذا الموقع من الفرع المختار.',
                    'Delivery is unavailable for this location from the selected branch.'
                ),
            ], 422);
        }

        return response()->json([
            'data' => $quote,
        ]);
    }

    public function store(Request $request, MoyasarGateway $gateway, DeliveryQuoteService $deliveryQuoteService)
    {
        if (! $this->checkoutSchemaReady()) {
            return redirect()->route('cart.index')->withErrors([
                'checkout' => FrontLang::t(
                    'نظام إتمام الطلب يحتاج ترقية قاعدة البيانات أولًا قبل استقبال الطلبات.',
                    'Checkout requires a database upgrade before orders can be accepted.'
                ),
            ]);
        }

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
            'branch_id' => ['required', 'exists:branches,id'],
            'fulfillment_method' => ['required', 'in:pickup,delivery'],
            'payment_method' => ['required', 'in:cash,online'],
            'address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'lat' => ['nullable', 'numeric'],
            'lng' => ['nullable', 'numeric'],
            'map_address' => ['nullable', 'string', 'max:255'],
        ]);

        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')
                ->with('success', FrontLang::t('السلة فارغة، أضف منتجات أولًا', 'Your cart is empty. Add items first.'));
        }

        $branch = Branch::query()->active()->findOrFail($data['branch_id']);

        if ($data['fulfillment_method'] === 'pickup' && ! $branch->pickup_enabled) {
            throw ValidationException::withMessages([
                'branch_id' => FrontLang::t('الفرع المحدد لا يدعم الاستلام من المطعم.', 'The selected branch does not support pickup.'),
            ]);
        }

        if ($data['fulfillment_method'] === 'delivery' && ! $branch->delivery_enabled) {
            throw ValidationException::withMessages([
                'branch_id' => FrontLang::t('الفرع المحدد لا يدعم التوصيل.', 'The selected branch does not support delivery.'),
            ]);
        }

        if (! filled($data['address'] ?? null) && filled($data['map_address'] ?? null)) {
            $data['address'] = $data['map_address'];
        }

        if (! filled($data['map_address'] ?? null) && filled($data['address'] ?? null)) {
            $data['map_address'] = $data['address'];
        }

        if ($data['fulfillment_method'] === 'delivery') {
            if (! filled($data['address']) || ! isset($data['lat'], $data['lng']) || ! filled($data['map_address'])) {
                throw ValidationException::withMessages([
                    'address' => FrontLang::t('بيانات عنوان التوصيل والموقع مطلوبة.', 'Delivery address and location details are required.'),
                ]);
            }
        }

        if ($data['payment_method'] === 'online' && ! $gateway->isConfigured()) {
            throw ValidationException::withMessages([
                'payment_method' => FrontLang::t('الدفع الإلكتروني غير مهيأ حاليًا. استخدم الكاش مؤقتًا.', 'Online payment is not configured yet. Please use cash for now.'),
            ]);
        }

        $normalizedCart = $this->normalizeCart($cart);
        $itemsSubtotal = collect($normalizedCart)->sum('line_total');
        $deliveryFee = 0;
        $deliveryDistanceKm = null;
        $customerAddress = $data['address'] ?? '';
        $lat = $data['lat'] ?? null;
        $lng = $data['lng'] ?? null;
        $mapAddress = $data['map_address'] ?? null;

        if ($data['fulfillment_method'] === 'delivery') {
            try {
                $quote = $deliveryQuoteService->quote(
                    $branch,
                    (float) $data['lat'],
                    (float) $data['lng'],
                    (float) $itemsSubtotal
                );
            } catch (Throwable $exception) {
                throw ValidationException::withMessages([
                    'branch_id' => FrontLang::t('التوصيل غير متاح لهذا الموقع من الفرع المختار.', 'Delivery is unavailable for this location from the selected branch.'),
                ]);
            }

            $deliveryFee = (float) $quote['delivery_fee'];
            $deliveryDistanceKm = (float) $quote['distance_km'];
        } else {
            $customerAddress = '';
            $lat = null;
            $lng = null;
            $mapAddress = null;
        }

        $total = round((float) $itemsSubtotal + $deliveryFee, 2);
        $payment = null;

        DB::beginTransaction();

        try {
            $order = Order::create([
                'code' => 'MB-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6)),
                'branch_id' => $branch->id,
                'customer_name' => $data['full_name'],
                'customer_phone' => $data['phone'],
                'customer_address' => $customerAddress,
                'notes' => $data['notes'] ?? null,
                'items_subtotal' => $itemsSubtotal,
                'delivery_fee' => $deliveryFee,
                'delivery_distance_km' => $deliveryDistanceKm,
                'total' => $total,
                'status' => 'pending',
                'lat' => $lat,
                'lng' => $lng,
                'map_address' => $mapAddress,
                'fulfillment_method' => $data['fulfillment_method'],
                'payment_method' => $data['payment_method'],
                'payment_provider' => $data['payment_method'] === 'online' ? 'moyasar' : null,
                'payment_status' => $data['payment_method'] === 'online' ? 'pending' : 'unpaid',
            ]);

            $orderItemRows = collect($normalizedCart)->map(function (array $item) use ($order) {
                return [
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'] ?? null,
                    'product_name' => $item['product_name'],
                    'qty' => $item['qty'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $item['line_total'],
                    'options_json' => json_encode($item['options'], JSON_UNESCAPED_UNICODE),
                ];
            })->all();

            $hasCreatedAt = Schema::hasColumn('order_items', 'created_at');
            $hasUpdatedAt = Schema::hasColumn('order_items', 'updated_at');

            if ($hasCreatedAt || $hasUpdatedAt) {
                $now = now();

                $orderItemRows = array_map(function (array $row) use ($now, $hasCreatedAt, $hasUpdatedAt) {
                    if ($hasCreatedAt) {
                        $row['created_at'] = $now;
                    }

                    if ($hasUpdatedAt) {
                        $row['updated_at'] = $now;
                    }

                    return $row;
                }, $orderItemRows);
            }

            DB::table('order_items')->insert($orderItemRows);

            if ($data['payment_method'] === 'online') {
                $paymentReference = (string) Str::ulid();

                $payment = Payment::create([
                    'order_id' => $order->id,
                    'reference' => $paymentReference,
                    'provider' => 'moyasar',
                    'method' => 'online',
                    'status' => 'initiated',
                    'amount' => $total,
                    'currency' => config('services.moyasar.currency', 'SAR'),
                    'payload' => [
                        'order_code' => $order->code,
                        'payment_reference' => $paymentReference,
                        'customer_name' => $data['full_name'],
                        'customer_phone' => $data['phone'],
                        'branch_id' => $branch->id,
                        'fulfillment_method' => $data['fulfillment_method'],
                    ],
                ]);
            }

            DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }

        if ($data['payment_method'] === 'cash') {
            session()->forget('cart');
            session()->put('success_order_code', $order->code);

            return redirect()->route('order.success', $order->code);
        }

        $order->load('items');

        try {
            $result = $gateway->createPayment($order, $payment);

            $payment->update([
                'status' => 'processing',
                'remote_invoice_id' => $result['invoice_id'] ?? null,
                'remote_payment_id' => $result['payment_id'] ?? null,
                'response_payload' => $result['raw'] ?? null,
            ]);

            session()->forget('cart');

            return redirect()->away($result['payment_url']);
        } catch (Throwable $exception) {
            $payment?->update([
                'status' => 'failed',
                'failed_at' => now(),
                'failure_reason' => $exception->getMessage(),
            ]);

            $order->update([
                'status' => 'failed_payment',
                'payment_status' => 'failed',
            ]);

            return redirect()->route('checkout.index')
                ->withInput()
                ->withErrors([
                    'payment_method' => FrontLang::t(
                        'تعذر بدء الدفع الإلكتروني حاليًا. يمكنك متابعة الطلب كاش أو استكمال إعداد البوابة.',
                        'Unable to start online payment right now. You can continue with cash or finish configuring the gateway.'
                    ),
                ]);
        }
    }

    protected function normalizeCart(array $cart): array
    {
        return collect($cart)->map(function (array $item) {
            $qty = max(1, (int) ($item['qty'] ?? 1));
            $unitPrice = (float) ($item['unit_price'] ?? 0);

            if ($unitPrice <= 0) {
                $unitPrice = (float) ($item['final_price'] ?? 0) / $qty;
            }

            return [
                'product_id' => $item['product_id'] ?? null,
                'product_name' => $item['name'] ?? ($item['product_name'] ?? ''),
                'qty' => $qty,
                'unit_price' => $unitPrice,
                'line_total' => round($unitPrice * $qty, 2),
                'options' => $item['options'] ?? [],
            ];
        })->all();
    }

    protected function branchesFeatureReady(): bool
    {
        return Schema::hasTable('branches');
    }

    protected function checkoutSchemaReady(): bool
    {
        if (! $this->branchesFeatureReady() || ! Schema::hasTable('payments')) {
            return false;
        }

        return Schema::hasColumn('orders', 'branch_id')
            && Schema::hasColumn('orders', 'fulfillment_method')
            && Schema::hasColumn('orders', 'items_subtotal')
            && Schema::hasColumn('orders', 'delivery_fee')
            && Schema::hasColumn('orders', 'delivery_distance_km')
            && Schema::hasColumn('orders', 'payment_status')
            && Schema::hasColumn('orders', 'payment_provider')
            && Schema::hasColumn('orders', 'paid_at')
            && Schema::hasColumn('orders', 'lat')
            && Schema::hasColumn('orders', 'lng')
            && Schema::hasColumn('orders', 'map_address');
    }
}
