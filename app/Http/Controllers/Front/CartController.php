<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Option;
use App\Models\Product;
use App\Support\FrontLang;
use App\Support\SeoData;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('home');
        }

        $total = collect($cart)->sum(fn ($item) => (float) ($item['final_price'] ?? 0));

        $seo = SeoData::make([
            'title' => FrontLang::t('السلة | مطاعم مأرب', 'Cart | Marib Restaurant'),
            'description' => FrontLang::t('راجع عناصر السلة قبل إتمام الطلب.', 'Review your cart before checkout.'),
            'robots' => 'noindex,nofollow',
        ]);

        return view('front.cart', compact('cart', 'total', 'seo'));
    }

    public function add(Request $request)
    {
        $product = Product::query()
            ->with(['optionGroups.options'])
            ->findOrFail($request->input('product_id'));

        $qty = max(1, (int) $request->input('qty', 1));
        $selectedOptionIds = collect($request->input('options', []))
            ->merge(
                collect($request->all())->filter(
                    fn ($value, $key) => is_string($key) && str_starts_with($key, 'group_') && filled($value)
                )->values()
            )
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->values();

        $selectedOptions = Option::query()
            ->whereIn('id', $selectedOptionIds)
            ->get()
            ->groupBy('option_group_id');

        foreach ($product->optionGroups as $group) {
            $selectedCount = $selectedOptions->get($group->id, collect())->count();
            $minSelect = max($group->is_required ? 1 : 0, (int) ($group->min_select ?? 0));
            $maxSelect = $group->max_select;

            if ($selectedCount < $minSelect) {
                return back()->withErrors([
                    'options' => FrontLang::t(
                        'بعض التخصيصات المطلوبة لم تُحدد بعد.',
                        'Some required customization options are still missing.'
                    ),
                ])->withInput();
            }

            if ($maxSelect !== null && $selectedCount > (int) $maxSelect) {
                return back()->withErrors([
                    'options' => FrontLang::t(
                        'تم اختيار عدد خيارات أكبر من المسموح به.',
                        'You selected more options than allowed for one of the groups.'
                    ),
                ])->withInput();
            }
        }

        $flatOptions = $selectedOptions->flatten(1);
        $unitPrice = (float) $product->price + (float) $flatOptions->sum('price_delta');

        $item = [
            'product_id' => $product->id,
            'name' => $product->name,
            'name_en' => $product->name_en,
            'base_price' => (float) $product->price,
            'unit_price' => $unitPrice,
            'final_price' => $unitPrice * $qty,
            'qty' => $qty,
            'options' => $flatOptions->map(fn ($option) => [
                'id' => $option->id,
                'name' => $option->name,
                'name_en' => $option->name_en,
                'price' => (float) $option->price_delta,
            ])->values()->all(),
        ];

        $cart = session()->get('cart', []);
        $cart[] = $item;

        session()->put('cart', $cart);

        return back()->with('success', FrontLang::t('تمت الإضافة إلى السلة', 'Added to cart successfully.'));
    }

    public function remove(int $index)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$index])) {
            unset($cart[$index]);
            session()->put('cart', array_values($cart));
        }

        return back()->with('success', FrontLang::t('تم حذف العنصر من السلة', 'Item removed from cart.'));
    }

    public function inc(int $index)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$index])) {
            $cart[$index]['qty'] = max(1, (int) ($cart[$index]['qty'] ?? 1) + 1);
            $cart[$index]['final_price'] = $this->getUnitPrice($cart[$index]) * $cart[$index]['qty'];
            session()->put('cart', $cart);
        }

        return back();
    }

    public function dec(int $index)
    {
        $cart = session()->get('cart', []);

        if (! isset($cart[$index])) {
            return back();
        }

        $currentQty = max(1, (int) ($cart[$index]['qty'] ?? 1));

        if ($currentQty === 1) {
            unset($cart[$index]);
            session()->put('cart', array_values($cart));

            return back();
        }

        $cart[$index]['qty'] = $currentQty - 1;
        $cart[$index]['final_price'] = $this->getUnitPrice($cart[$index]) * $cart[$index]['qty'];
        session()->put('cart', $cart);

        return back();
    }

    protected function getUnitPrice(array $item): float
    {
        if (isset($item['unit_price'])) {
            return (float) $item['unit_price'];
        }

        $base = (float) ($item['base_price'] ?? 0);
        $optionsTotal = collect($item['options'] ?? [])->sum(fn ($option) => (float) ($option['price'] ?? 0));

        if ($base + $optionsTotal > 0) {
            return $base + $optionsTotal;
        }

        $qty = max(1, (int) ($item['qty'] ?? 1));

        return (float) ($item['final_price'] ?? 0) / $qty;
    }
}
