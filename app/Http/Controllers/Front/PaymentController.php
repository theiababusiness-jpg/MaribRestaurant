<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Payments\MoyasarGateway;
use App\Support\FrontLang;
use App\Support\SeoData;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class PaymentController extends Controller
{
    public function callback(Request $request, Payment $payment, MoyasarGateway $gateway)
    {
        try {
            $this->refreshPayment($payment, $gateway, $request->all());
        } catch (Throwable $exception) {
            // Keep the redirect flowing even if the remote status lookup briefly fails.
        }

        return redirect()->route('payments.result', $payment);
    }

    public function error(Request $request, Payment $payment, MoyasarGateway $gateway)
    {
        try {
            $this->refreshPayment($payment, $gateway, $request->all());
        } catch (Throwable $exception) {
            $payment->update([
                'status' => 'cancelled',
                'failed_at' => now(),
                'failure_reason' => $request->query('message'),
            ]);

            $payment->order->update([
                'status' => 'failed_payment',
                'payment_status' => 'cancelled',
            ]);
        }

        return redirect()->route('payments.result', $payment);
    }

    public function result(Payment $payment, MoyasarGateway $gateway)
    {
        if (in_array($payment->status, ['initiated', 'processing'], true)) {
            try {
                $this->refreshPayment($payment, $gateway);
            } catch (Throwable $exception) {
                // If the gateway is temporarily unreachable, the page still renders the last known state.
            }
        }

        $payment->refresh()->load('order.items', 'order.branch');

        $seo = SeoData::make([
            'title' => FrontLang::t('نتيجة الدفع | مطاعم مأرب', 'Payment Result | Marib Restaurant'),
            'description' => FrontLang::t('متابعة حالة الدفع لطلبك في مطاعم مأرب.', 'Track the payment status for your Marib Restaurant order.'),
            'robots' => 'noindex,nofollow',
        ]);

        return view('front.payment_result', compact('payment', 'seo'));
    }

    public function retry(Payment $payment, MoyasarGateway $gateway)
    {
        $payment->load('order.items');

        if ($payment->status === 'paid') {
            return redirect()->route('payments.result', $payment);
        }

        if (! $gateway->isConfigured()) {
            return back()->withErrors([
                'payment' => FrontLang::t(
                    'بوابة الدفع غير مهيأة بعد. أكمل الإعدادات ثم أعد المحاولة.',
                    'The payment gateway is not configured yet. Complete the setup and try again.'
                ),
            ]);
        }

        $result = $gateway->createPayment($payment->order, $payment);

        $payment->update([
            'status' => 'processing',
            'remote_invoice_id' => $result['invoice_id'] ?? $payment->remote_invoice_id,
            'remote_payment_id' => $result['payment_id'] ?? $payment->remote_payment_id,
            'response_payload' => $result['raw'] ?? null,
            'failed_at' => null,
            'failure_reason' => null,
            'paid_at' => null,
        ]);

        $payment->order->update([
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_provider' => $payment->provider,
        ]);

        return redirect()->away($result['payment_url']);
    }

    public function webhook(Request $request, MoyasarGateway $gateway)
    {
        $payload = $request->all();
        $remoteInvoiceId = Arr::get($payload, 'id')
            ?? Arr::get($payload, 'invoice_id')
            ?? Arr::get($payload, 'invoiceId')
            ?? Arr::get($payload, 'Data.InvoiceId');

        $remotePaymentId = Arr::get($payload, 'payment_id')
            ?? Arr::get($payload, 'paymentId')
            ?? Arr::get($payload, 'PaymentId')
            ?? Arr::get($payload, 'Data.PaymentId');

        $paymentReference = Arr::get($payload, 'metadata.payment_reference')
            ?? Arr::get($payload, 'metadata.paymentReference')
            ?? Arr::get($payload, 'Data.metadata.payment_reference')
            ?? Arr::get($payload, 'Data.metadata.paymentReference');

        $payment = Payment::query()
            ->where(function ($query) use ($remotePaymentId, $remoteInvoiceId, $paymentReference) {
                if ($remotePaymentId) {
                    $query->where('remote_payment_id', $remotePaymentId);
                }

                if ($remoteInvoiceId) {
                    $query->orWhere('remote_invoice_id', $remoteInvoiceId);
                }

                if ($paymentReference) {
                    $query->orWhere('reference', $paymentReference);
                }
            })
            ->latest('id')
            ->first();

        if (! $payment) {
            return response()->json(['message' => 'Payment not found.'], 404);
        }

        $this->refreshPayment($payment, $gateway, $payload);

        return response()->json(['message' => 'ok']);
    }

    protected function refreshPayment(Payment $payment, MoyasarGateway $gateway, array $payload = []): void
    {
        $gatewayKey = $gateway->resolveRemoteKey($payload, $payment);
        $response = $gateway->fetchPaymentStatus($gatewayKey['key'], $gatewayKey['type']);
        $this->syncPayment($payment->fresh('order'), $response);
    }

    protected function syncPayment(Payment $payment, array $statusResponse): void
    {
        $payment->loadMissing('order');

        DB::transaction(function () use ($payment, $statusResponse) {
            $data = $statusResponse['Data'] ?? $statusResponse;
            $latestTransaction = $this->extractLatestTransaction($data);
            $normalizedStatus = $this->normalizeStatus(
                (string) ($data['status'] ?? $data['InvoiceStatus'] ?? ''),
                (string) ($latestTransaction['status'] ?? $latestTransaction['TransactionStatus'] ?? ''),
                (string) ($latestTransaction['message'] ?? $latestTransaction['Message'] ?? '')
            );

            $paymentUpdate = [
                'status' => $normalizedStatus,
                'remote_invoice_id' => $data['id'] ?? $data['InvoiceId'] ?? $payment->remote_invoice_id,
                'remote_payment_id' => $latestTransaction['id'] ?? $latestTransaction['PaymentId'] ?? $payment->remote_payment_id,
                'transaction_id' => $latestTransaction['transaction_id'] ?? $latestTransaction['TransactionId'] ?? $payment->transaction_id,
                'response_payload' => $statusResponse,
            ];

            if ($normalizedStatus === 'paid' && ! $payment->paid_at) {
                $paymentUpdate['paid_at'] = now();
            }

            if (in_array($normalizedStatus, ['failed', 'cancelled'], true)) {
                $paymentUpdate['failed_at'] = now();
                $paymentUpdate['failure_reason'] = $latestTransaction['message'] ?? $latestTransaction['Error'] ?? $payment->failure_reason;
            }

            $payment->update(array_filter($paymentUpdate, fn ($value) => $value !== null));

            $orderUpdate = [
                'payment_method' => 'online',
                'payment_provider' => $payment->provider,
            ];

            if ($normalizedStatus === 'paid') {
                $orderUpdate['payment_status'] = 'paid';
                $orderUpdate['paid_at'] = $payment->fresh()->paid_at ?? now();

                if (in_array($payment->order->status, ['pending', 'failed_payment'], true)) {
                    $orderUpdate['status'] = 'paid';
                }
            } elseif ($normalizedStatus === 'processing') {
                $orderUpdate['payment_status'] = 'pending';

                if ($payment->order->status === 'failed_payment') {
                    $orderUpdate['status'] = 'pending';
                }
            } elseif ($normalizedStatus === 'cancelled') {
                $orderUpdate['payment_status'] = 'cancelled';
                $orderUpdate['status'] = 'cancelled';
            } else {
                $orderUpdate['payment_status'] = $normalizedStatus;
                $orderUpdate['status'] = 'failed_payment';
            }

            $payment->order->update($orderUpdate);
        });
    }

    protected function extractLatestTransaction(array $data): array
    {
        $transactions = Arr::wrap(
            $data['payments']
                ?? $data['Payments']
                ?? $data['transactions']
                ?? $data['InvoiceTransactions']
                ?? []
        );

        return collect($transactions)->last() ?? [];
    }

    protected function normalizeStatus(string ...$states): string
    {
        $haystack = Str::lower(implode(' ', array_filter($states)));

        if (Str::contains($haystack, ['paid', 'success', 'succeeded', 'completed', 'captured', 'verified'])) {
            return 'paid';
        }

        if (Str::contains($haystack, ['cancelled', 'canceled', 'voided', 'expired'])) {
            return 'cancelled';
        }

        if (Str::contains($haystack, ['failed', 'declined', 'error', 'refunded'])) {
            return 'failed';
        }

        return 'processing';
    }
}
