<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MoyasarGateway
{
    public function isConfigured(): bool
    {
        $secretKey = (string) config('services.moyasar.secret_key');

        return filled($secretKey) && ! str_contains($secretKey, '*');
    }

    public function createPayment(Order $order, Payment $payment): array
    {
        if (! filled(config('services.moyasar.secret_key'))) {
            throw new RuntimeException('Moyasar secret key is missing.');
        }

        if (str_contains((string) config('services.moyasar.secret_key'), '*')) {
            throw new RuntimeException('Moyasar secret key is masked or incomplete. Paste the full sk_test_ or sk_live_ value into .env.');
        }

        $currency = strtoupper((string) config('services.moyasar.currency', 'SAR'));

        $payload = [
            'amount' => $this->toMinorUnits((float) $order->total, $currency),
            'currency' => $currency,
            'description' => 'Order #' . $order->code,
            'callback_url' => route('payments.webhook'),
            'success_url' => route('payments.callback', $payment),
            'back_url' => route('payments.result', $payment),
            'metadata' => [
                'order_code' => $order->code,
                'payment_reference' => $payment->reference,
                'branch_id' => (string) $order->branch_id,
                'fulfillment_method' => (string) $order->fulfillment_method,
            ],
        ];

        $response = $this->post('/invoices', $payload);
        $payments = Arr::wrap($response['payments'] ?? []);
        $latestPayment = collect($payments)->last() ?? [];

        return [
            'invoice_id' => Arr::get($response, 'id'),
            'payment_id' => Arr::get($latestPayment, 'id'),
            'payment_url' => Arr::get($response, 'url'),
            'raw' => $response,
        ];
    }

    public function fetchPaymentStatus(string|int $key, string $keyType = 'InvoiceId'): array
    {
        $endpoint = strtolower((string) $keyType) === 'paymentid'
            ? '/payments/' . $key
            : '/invoices/' . $key;

        return $this->get($endpoint);
    }

    public function resolveRemoteKey(array $payload, Payment $payment): array
    {
        $invoiceId = Arr::get($payload, 'id')
            ?? Arr::get($payload, 'invoice_id')
            ?? Arr::get($payload, 'invoiceId')
            ?? Arr::get($payload, 'Data.InvoiceId')
            ?? $payment->remote_invoice_id;

        if ($invoiceId) {
            return ['key' => $invoiceId, 'type' => 'InvoiceId'];
        }

        $paymentId = Arr::get($payload, 'payment_id')
            ?? Arr::get($payload, 'paymentId')
            ?? Arr::get($payload, 'PaymentId')
            ?? Arr::get($payload, 'Data.PaymentId')
            ?? $payment->remote_payment_id;

        if ($paymentId) {
            return ['key' => $paymentId, 'type' => 'PaymentId'];
        }

        throw new RuntimeException('Unable to resolve remote payment identifier.');
    }

    protected function post(string $endpoint, array $payload): array
    {
        $response = Http::baseUrl(rtrim((string) config('services.moyasar.base_url'), '/'))
            ->withBasicAuth((string) config('services.moyasar.secret_key'), '')
            ->acceptJson()
            ->asJson()
            ->timeout(20)
            ->post($endpoint, $payload)
            ->throw();

        return $response->json();
    }

    protected function get(string $endpoint): array
    {
        $response = Http::baseUrl(rtrim((string) config('services.moyasar.base_url'), '/'))
            ->withBasicAuth((string) config('services.moyasar.secret_key'), '')
            ->acceptJson()
            ->timeout(20)
            ->get($endpoint)
            ->throw();

        return $response->json();
    }

    protected function toMinorUnits(float $amount, string $currency): int
    {
        $fractionDigits = [
            'BHD' => 3,
            'JOD' => 3,
            'KWD' => 3,
            'LYD' => 3,
            'OMR' => 3,
            'TND' => 3,
        ][$currency] ?? 2;

        return (int) round($amount * (10 ** $fractionDigits));
    }
}
