# توثيق تفعيل الدفع الإلكتروني عبر مويسر

هذا الملف يشرح كل ما أضفته في هذه المهمة، حتى تستطيع:
- فهم التدفق الجديد.
- إعادة تنفيذه بنفسك.
- التراجع عنه بسهولة إذا احتجت.

## الفكرة العامة

اخترت مسار "الطلب من الموقع ثم التحويل إلى صفحة مويسر" بدل بناء نموذج بطاقة مخصص داخل صفحة checkout.

هذا يعني:
- المستخدم يختار `الدفع الإلكتروني` من صفحة إتمام الطلب.
- النظام ينشئ الطلب محليًا.
- النظام ينشئ فاتورة مويسر من السيرفر.
- يتم تحويل المستخدم إلى صفحة مويسر الرسمية لإكمال الدفع.
- بعد الدفع، يعود المستخدم إلى صفحة النتائج، ويتم تحديث حالة الدفع محليًا من جديد عبر API مويسر.

## ملاحظة مهمة

الملف القديم `app/Services/Payments/MyFatoorahGateway.php` تركته كما هو لأغراض الرجوع السريع، لكن التدفق الحالي لم يعد يستخدمه.

## 1) `config/services.php`

أضفت بلوك `moyasar`:

- `publishable_key`:
  - يقرأ `MOYASAR_PUBLIC_KEY` من `.env`.
  - خزّنت هذا المفتاح لأنك وضعته فعلًا، ولأنك قد تحتاجه لاحقًا إذا أردت واجهة مويسر الأمامية داخل الصفحة.
  - في التدفق الحالي لا أستخدمه مباشرةً لأننا نعتمد على فاتورة مويسر من السيرفر.
- `secret_key`:
  - يقرأ `MOYASAR_SECRET_KEY`.
  - هذا هو المفتاح الذي يستخدمه السيرفر عند إنشاء الفاتورة وعند جلب حالتها.
- `base_url`:
  - يقرأ `MOYASAR_BASE_URL`.
  - وضعت له قيمة افتراضية `https://api.moyasar.com/v1`.
- `currency`:
  - يقرأ `MOYASAR_CURRENCY`.
  - القيمة الافتراضية `SAR`.

## 2) `.env.example`

أضفت المتغيرات التالية:

- `MOYASAR_PUBLIC_KEY`
- `MOYASAR_SECRET_KEY`
- `MOYASAR_BASE_URL=https://api.moyasar.com/v1`
- `MOYASAR_CURRENCY=SAR`

السبب:
- حتى يكون ملف المثال مطابقًا للإعدادات الجديدة.
- حتى لا ينسى أي شخص أسماء المتغيرات المطلوبة.

## 3) `database/migrations/2026_07_08_000001_create_payments_table.php`

أضفت الترحيل الخاص بجدول `payments`.

### لماذا؟

لأن صفحة checkout كانت تتحقق من وجود جدول `payments` قبل تفعيل خيار الدفع الإلكتروني. بدون هذا الجدول كان الخيار يبقى معطّلًا.

### ما الذي أضفته؟

- `if (Schema::hasTable('payments')) { return; }`
  - هذا يجعل الترحيل آمنًا إذا كان الجدول موجودًا أصلًا.
  - في قاعدة البيانات الحالية كان الجدول موجودًا بالفعل، لذلك هذا السطر منع الفشل.
- `Schema::create('payments', function (Blueprint $table) { ... })`
  - ينشئ الجدول إذا لم يكن موجودًا.
- `order_id`
  - علاقة الطلب المحلي بالدفع المحلي.
- `reference`
  - المرجع المحلي الفريد الذي يستخدمه Laravel في مسار `payments/{payment}`.
- `provider`
  - اسم المزود، ويصبح الآن `moyasar`.
- `method`
  - طريقة الدفع، مثل `online`.
- `status`
  - الحالة الحالية للدفع، وتبدأ غالبًا بـ `initiated`.
- `amount`
  - مبلغ الدفع المحلي.
- `currency`
  - العملة، والقيمة الافتراضية `SAR`.
- `remote_invoice_id`
  - رقم الفاتورة في مويسر.
- `remote_payment_id`
  - رقم محاولة الدفع من مويسر.
- `transaction_id`
  - رقم العملية من مويسر إذا توفر.
- `payload`
  - بيانات محلية مساعدة.
- `response_payload`
  - الرد الخام من مويسر للاحتفاظ به للتشخيص.
- `paid_at`
  - وقت نجاح الدفع.
- `failed_at`
  - وقت فشل أو إلغاء الدفع.
- `failure_reason`
  - سبب الفشل.
- `timestamps`
  - `created_at` و `updated_at`.

### لماذا جعلت الترحيل idempotent؟

لأن قاعدة البيانات عندك كانت تحتوي فعلًا على جدول `payments`، لكن بدون سجل في migrations.

بدل محاولة إنشائه مرة ثانية وفشل الترحيل، جعلت الترحيل يتجاوز الإنشاء إذا كان الجدول موجودًا.

## 4) `app/Services/Payments/MoyasarGateway.php`

هذه هي خدمة الدفع الجديدة، وهي بديل البوابة القديمة.

### `isConfigured()`

- تتحقق من وجود `services.moyasar.secret_key`.
- إذا كانت موجودة، فالبوابة تعتبر جاهزة للإرسال والاستقبال.

### `createPayment(Order $order, Payment $payment)`

هذه الدالة تنشئ فاتورة مويسر.

#### السطور المهمة فيها

- `$currency = strtoupper(...)`
  - يضمن أن العملة تكون بحروف كبيرة مثل `SAR`.
- `$payload = [...]`
  - جسم الطلب الذي يُرسل إلى مويسر.
- `'amount' => $this->toMinorUnits(...)`
  - يحول المبلغ من ريال إلى أصغر وحدة رقمية كما تتوقعها مويسر.
- `'description' => 'Order #' . $order->code`
  - وصف الفاتورة يظهر في مويسر.
- `'callback_url' => route('payments.webhook')`
  - هذا هو endpoint الخلفي الذي يستقبل إشعار مويسر عندما تتم عملية الدفع.
- `'success_url' => route('payments.callback', $payment)`
  - هذا هو الرابط الذي يعود له المستخدم بعد نجاح الدفع.
  - استخدمت مسار `payments.callback` الموجود مسبقًا ليحافظ على منطق إعادة التحقق في السيرفر.
- `'back_url' => route('payments.result', $payment)`
  - إذا رجع المستخدم من صفحة مويسر يدويًا، سيصل إلى صفحة نتيجة الدفع بدل العودة إلى checkout من جديد.
- `'metadata' => [...]`
  - أضفت:
    - `order_code`
    - `payment_reference`
    - `branch_id`
    - `fulfillment_method`
  - الهدف هو تسهيل التتبع والتشخيص.
- `post('/invoices', $payload)`
  - هذا هو طلب إنشاء الفاتورة الفعلي.
- `return [...]`
  - أعيد:
    - `invoice_id`
    - `payment_id`
    - `payment_url`
    - `raw`

### `fetchPaymentStatus(string|int $key, string $keyType = 'InvoiceId')`

- إذا كان النوع `PaymentId` يجلب `/payments/{id}`.
- إذا كان النوع `InvoiceId` يجلب `/invoices/{id}`.
- هذا أبقى الخدمة مرنة لو أردت استخدام `payment_id` أو `invoice_id`.

### `resolveRemoteKey(array $payload, Payment $payment)`

- تبحث أولًا عن `id` أو `invoice_id` أو `invoiceId`.
- إذا لم تجدها، تعود إلى `remote_invoice_id` المحلي.
- إذا لم تجدها، تبحث عن `payment_id` أو `paymentId`.
- إذا لم تجد شيئًا، ترمي خطأ.

### `post()` و `get()`

- كلاهما يستخدمان `Http::baseUrl(...)`.
- كلاهما يستخدم `withBasicAuth(secret_key, '')` لأن مويسر تعتمد Basic Auth للمفاتيح السرية.

### `toMinorUnits()`

- يحول الرقم إلى وحدة الدفع الصغيرة.
- وضعت خريطة صغيرة لبعض العملات ذات 3 منازل عشرية.
- العملة الافتراضية هنا تبقى `SAR` بمرتين عشريتين.

## 5) `app/Http/Controllers/Front/CheckoutController.php`

### التبديل العام

- غيّرت الاستيراد من `MyFatoorahGateway` إلى `MoyasarGateway`.
- غيّرت دوال `index()` و `store()` لتستقبل `MoyasarGateway`.

### في `index()`

- يبقى نفس منطق الصفحة:
  - جلب السلة.
  - جلب الفروع.
  - حساب subtotal.
  - تحديد إذا كان الدفع الإلكتروني متاحًا.
- الفرق الوحيد أن `onlinePaymentAvailable` الآن يعتمد على `MoyasarGateway::isConfigured()`.

### في `store()`

#### عند إنشاء الطلب

- غيّرت `payment_provider` من `myfatoorah` إلى `moyasar`.
- غيّرت `Payment::create()` حتى يسجل:
  - `provider => 'moyasar'`
  - `currency => config('services.moyasar.currency', 'SAR')`

#### payload المحلي

أضفت داخل `payload`:

- `order_code`
- `payment_reference`

#### لماذا؟

- حتى يصبح كل سجل دفع قابلًا للتتبع بسهولة من قاعدة البيانات.

#### بعد إنشاء الدفع الإلكتروني

- أترك الطلب داخل DB.
- أنشئ فاتورة مويسر.
- أحفظ:
  - `remote_invoice_id`
  - `remote_payment_id`
  - `response_payload`
- ثم أوجّه المستخدم مباشرة إلى `payment_url`.

#### في حالة الفشل

- إذا فشل إنشاء الفاتورة، يتم:
  - تعليم الدفع `failed`
  - تعليم الطلب `failed_payment`
  - إعادة المستخدم لصفحة checkout مع رسالة واضحة

## 6) `app/Http/Controllers/Front/PaymentController.php`

هذا الملف كان يحتاج أكبر تعديل لأنه هو الذي يستقبل الرجوع من مويسر ويحدّث الحالات.

### `callback()`

- صار يستخدم `MoyasarGateway`.
- يحاول تحديث الدفع من مويسر.
- إذا فشلت عملية الجلب مؤقتًا، لا يكسر التحويل.
- بعدها يذهب إلى `payments.result`.

### `error()`

- يحاول أولًا جلب الحالة من مويسر.
- إذا فشل ذلك، يعلّم الدفع `cancelled`.
- يحدّث الطلب إلى `failed_payment` و `payment_status = cancelled`.

### `result()`

- إذا كانت الحالة `initiated` أو `processing`:
  - أحاول تحديثها من مويسر.
- ثم أعيد تحميل السجل من قاعدة البيانات.
- بعدها أعرض صفحة النتيجة.

### `retry()`

- إذا كانت الحالة `paid` لا يعيد الدفع.
- إذا كانت البوابة غير مهيأة، يرجع برسالة خطأ.
- إذا كانت جاهزة:
  - ينشئ فاتورة جديدة.
  - يعيد ضبط:
    - `status`
    - `remote_invoice_id`
    - `remote_payment_id`
    - `response_payload`
    - `failed_at`
    - `failure_reason`
    - `paid_at`
  - ويعيد الطلب إلى `pending`.

### `webhook()`

- يستقبل POST من مويسر.
- يحاول استخراج:
  - `id`
  - `invoice_id`
  - `payment_id`
  - `metadata.payment_reference`
- ثم يبحث عن سجل الدفع المحلي المناسب.
- إذا وجده، يعيد جلب الحالة من مويسر ويزامنها محليًا.

### `refreshPayment()`

- دالة مساعدة صغيرة لتجنب تكرار نفس الكود في callback/result/webhook.

### `syncPayment()`

- تقرأ الرد الخام من مويسر.
- تستخرج أحدث transaction/payment.
- تحدد الحالة النهائية:
  - `paid`
  - `processing`
  - `cancelled`
  - `failed`
- ثم تحدّث:
  - جدول `payments`
  - جدول `orders`

### `extractLatestTransaction()`

- تعطيك آخر محاولة دفع من `payments` أو `InvoiceTransactions`.

### `normalizeStatus()`

- تعتبر أي من هذه الحالات ناجحة:
  - `paid`
  - `success`
  - `succeeded`
  - `completed`
  - `captured`
  - `verified`
- وتعتبر هذه الحالات ملغاة:
  - `cancelled`
  - `canceled`
  - `voided`
  - `expired`
- وتعتبر هذه الحالات فاشلة:
  - `failed`
  - `declined`
  - `error`
  - `refunded`

## 7) `routes/web.php`

أضفت/عدّلت مسارات الدفع كما يلي:

- `/payments/webhook/moyasar`
  - هذا هو endpoint الرسمي الجديد الذي سيستقبله مويسر.
  - اسمه `payments.webhook`.
- `/payments/webhook/myfatoorah`
  - تركته كمسار legacy.
  - يذهب لنفس controller حتى لا تكسر أي رابط قديم.

المسارات الأخرى بقيت كما هي:
- `payments.callback`
- `payments.error`
- `payments.result`
- `payments.retry`

## 8) `resources/views/front/checkout.blade.php`

أضفت فقط سطر توضيحي تحت خيار الدفع الإلكتروني:

- إذا كان الدفع الإلكتروني متاحًا:
  - يظهر تنبيه يقول إن المستخدم سيُحوّل إلى صفحة مويسر لإكمال الدفع.

### لماذا هذا السطر؟

- حتى لا يظن المستخدم أن الدفع سيبقى داخل صفحة checkout نفسها.
- يوضح أن الخطوة التالية بعد تأكيد الطلب هي صفحة مويسر.

## 9) كيف اختبرت التغييرات

نفذت الخطوات التالية:

1. `php artisan migrate:status`
   - وتأكدت أن ترحيل `payments` كان Pending.
2. `php artisan migrate --force`
   - فشل أول مرة لأن جدول `payments` كان موجودًا في قاعدة البيانات.
3. فحصت أعمدة جدول `payments`
   - وتبين أنه موجود فعلًا وبنفس البنية المطلوبة.
4. عدلت الترحيل ليصبح آمنًا إذا كان الجدول موجودًا.
5. أعدت تشغيل `php artisan migrate --force`
   - ونجح.
6. فحصت `MoyasarGateway::isConfigured()`
   - وكانت النتيجة `true`.
7. فحصت الـ routes
   - وظهر `payments.webhook/moyasar` و `payments.callback` و `payments.result`.
8. شغّلت `php -l` على الملفات PHP الأساسية
   - ولم تظهر أخطاء نحوية.

## 10) ماذا تفعل أنت الآن للتجربة

1. افتح صفحة السلة ثم اذهب إلى checkout.
2. اختر `الدفع الإلكتروني`.
3. أكمل بيانات الطلب.
4. بعد التأكيد يجب أن يتحولك الموقع إلى صفحة مويسر.
5. استخدم بطاقة اختبار من مويسر.
6. بعد النجاح سيعود بك النظام إلى صفحة نتيجة الدفع.

## 10.1) إصلاح خطأ `reference on null`

إذا ظهر لك الخطأ:

- `Attempt to read property "reference" on null`

فالسبب كان أنني استخدمت `$payment->reference` داخل `Payment::create()` قبل أن يتم إنشاء كائن `Payment` نفسه.

### الإصلاح

- أنشأت متغيرًا مستقلًا:
  - `$paymentReference = (string) Str::ulid();`
- استخدمت هذا المتغير في:
  - `reference`
  - `payload.payment_reference`

### لماذا هذا مهم؟

- لأن `Payment::create()` لا يعيد كائن الدفع إلا بعد انتهاء الإنشاء.
- لذلك لا يجوز قراءة خصائص `$payment` داخل نفس المصفوفة التي تُنشئه.

## 11) التراجع السريع

إذا أردت الرجوع:

- احذف أو عطّل الاستيراد إلى `MoyasarGateway` في:
  - `app/Http/Controllers/Front/CheckoutController.php`
  - `app/Http/Controllers/Front/PaymentController.php`
- أعد `payment_provider` و `provider` إلى أي قيمة تريدها أو أزلها إن لزم.
- احذف/عطّل:
  - `app/Services/Payments/MoyasarGateway.php`
  - `database/migrations/2026_07_08_000001_create_payments_table.php`
- احذف/عطّل مسار webhook الجديد:
  - `Route::post('/payments/webhook/moyasar', ...)`
- احذف السطر التوضيحي في checkout إذا لم تعد تريده.

## 12) ملاحظة أخيرة


الترحيل الحالي صار آمنًا مع قاعدة البيانات الموجودة لأن جدول `payments` كان موجودًا أصلًا.

هذا يعني أن:
- الكود جاهز الآن.
- خيار الدفع الإلكتروني ينبغي أن يظهر في checkout.
- مسار الدفع قادر على التحويل إلى مويسر ثم الرجوع للنتيجة.

[docs/moyasar-payment-log.md](/P:/ما بعد التخرج/مطاعم مأرب/public_html/docs/moyasar-payment-log.md)
ما الذي فعلته بالتفصيل الممل؟
وضعت متغيرات مويسر في config/services.php و .env.example
أنشأت/فعّلت جدول payments
أنشأت خدمة MoyasarGateway
عدلت CheckoutController لإنشاء طلب محلي ثم إنشاء فاتورة مويسر
عدلت PaymentController لمعالجة الرجوع والنجاح والفشل وwebhook
عدلت routes/web.php
أضفت توضيحًا بسيطًا في صفحة checkout
وثقت كل ذلك في الملف الخارجي
إذا أردت، أستطيع الآن أن أكتب لك "نسخة مختصرة جدًا" من نفس التوثيق في 10 خطوات فقط، بحيث تنفذها يدويًا بدون قراءة التفاصيل الطويلة.