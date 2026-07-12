<?php

namespace App\Http\Middleware;

use App\Models\Order;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InjectAdminOrderNotifications
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \Symfony\Component\HttpFoundation\Response $response */
        $response = $next($request);

        if (! auth('admin')->check()) {
            return $response;
        }

        if (! $this->shouldInjectInto($response)) {
            return $response;
        }

        $content = $response->getContent();

        if (! is_string($content) || stripos($content, '</body>') === false || str_contains($content, 'data-admin-order-notifications')) {
            return $response;
        }

        $notificationsMarkup = view('admin.partials.order_notifications', [
            'adminCheckNewUrl' => route('admin.orders.checkNew'),
            'adminOrdersUrl' => route('admin.orders.index'),
            'adminLatestOrderId' => Order::max('id') ?? 0,
        ])->render();

        $updatedContent = preg_replace('/<\/body>/i', $notificationsMarkup.'</body>', $content, 1);

        if (is_string($updatedContent)) {
            $response->setContent($updatedContent);
        }

        return $response;
    }

    protected function shouldInjectInto(Response $response): bool
    {
        if (! method_exists($response, 'getContent')) {
            return false;
        }

        $contentType = strtolower((string) $response->headers->get('Content-Type', 'text/html'));

        return $response->isSuccessful() && str_contains($contentType, 'text/html');
    }
}
