<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\FrontLang;
use App\Support\SeoData;

class OrderController extends Controller
{
    public function success(string $code)
    {
        if (session()->pull('success_order_code') !== $code) {
            return redirect()->route('home')->with('success', FrontLang::t(
                'انتهت صلاحية صفحة تأكيد الطلب. يمكنك متابعة التصفح من الصفحة الرئيسية.',
                'The order confirmation page is no longer available. You can continue browsing from the home page.'
            ));
        }

        $order = Order::where('code', $code)
            ->with(['items', 'latestPayment', 'branch'])
            ->firstOrFail();

        $seo = SeoData::make([
            'title' => FrontLang::t('تم استلام الطلب | مطاعم مأرب', 'Order Received | Marib Restaurant'),
            'description' => FrontLang::t('تم تسجيل طلبك بنجاح ويمكنك متابعة تفاصيله من هذه الصفحة.', 'Your order has been recorded successfully and you can review its details on this page.'),
            'robots' => 'noindex,nofollow',
        ]);

        return view('front.order_success', compact('order', 'seo'));
    }
}
