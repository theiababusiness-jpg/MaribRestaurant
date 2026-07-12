<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\Payment;
use App\Support\FrontLang;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MyFatoorahGateway
{
    public function isConfigured(): bool
    {
        return filled(config('services.myfatoorah.base_url'))
            && filled(config('services.myfatoorah.api_key'))
            && filled(config('services.myfatoorah.payment_method_id'));
    }

    public function createPayment(Order $order, Payment $payment): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('MyFatoorah configuration is incomplete.');
        }

        $payload = [
            'PaymentMethodId' => (int) config('services.myfatoorah.payment_method_id'),
            'InvoiceValue' => (float) $order->total,
            'DisplayCurrencyIso' => config('services.myfatoorah.currency', 'SAR'),
            'Language' => FrontLang::get() === 'en' ? 'en' : 'ar',
            'CustomerName' => $order->customer_name,
            'CustomerReference' => $order->code,
            'CustomerMobile' => $order->customer_phone,
            'MobileCountryCode' => config('services.myfatoorah.mobile_country_code', '+966'),
            'CallBackUrl' => route('payments.callback', $payment),
            'ErrorUrl' => route('payments.error', $payment),
            'InvoiceItems' => $this->formatItems($order),
        ];

        $response = $this->post('/v2/ExecutePayment', $payload);
        $data = $response['Data'] ?? [];

        return [
            'invoice_id' => Arr::get($data, 'InvoiceId'),
            'payment_id' => Arr::get($data, 'PaymentId'),
            'payment_url' => Arr::get($data, 'PaymentURL') ?? Arr::get($data, 'InvoiceURL'),
            'raw' => $response,
        ];
    }

    public function fetchPaymentStatus(string|int $key, string $keyType = 'InvoiceId'): array
    {
        return $this->post('/v2/getPaymentStatus', [
            'Key' => (string) $key,
            'KeyType' => $keyType,
        ]);
    }

    public function resolveRemoteKey(array $payload, Payment $payment): array
    {
        $paymentId = $payload['paymentId']
            ?? $payload['PaymentId']
            ?? Arr::get($payload, 'Data.PaymentId')
            ?? $payment->remote_payment_id;

        if ($paymentId) {
            return ['key' => $paymentId, 'type' => 'PaymentId'];
        }

        $invoiceId = $payload['invoiceId']
            ?? $payload['InvoiceId']
            ?? Arr::get($payload, 'Data.InvoiceId')
            ?? $payment->remote_invoice_id;

        if ($invoiceId) {
            return ['key' => $invoiceId, 'type' => 'InvoiceId'];
        }

        throw new RuntimeException('Unable to resolve remote payment identifier.');
    }

    protected function post(string $endpoint, array $payload): array
    {
        $response = Http::baseUrl(rtrim((string) config('services.myfatoorah.base_url'), '/'))
            ->withHeaders([
                'Authorization' => 'Bearer ' . config('services.myfatoorah.api_key'),
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->timeout(20)
            ->post($endpoint, $payload)
            ->throw();

        return $response->json();
    }

    protected function formatItems(Order $order): array
    {
        $items = $order->items->map(function ($item) {
            return [
                'ItemName' => $item->product_name,
                'Quantity' => (int) $item->qty,
                'UnitPrice' => (float) $item->unit_price,
            ];
        });

        if ((float) $order->delivery_fee > 0) {
            $items->push([
                'ItemName' => 'Delivery Fee',
                'Quantity' => 1,
                'UnitPrice' => (float) $order->delivery_fee,
            ]);
        }

        return $items->values()->all();
    }
}
