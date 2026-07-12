<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $branches = Branch::query()->active()->orderBy('sort_order')->orderBy('id')->get();
        $query = Order::query()->with('branch')->orderByDesc('id');

        if ($request->filled('q')) {
            $q = trim((string) $request->query('q'));
            $query->where(function ($nested) use ($q) {
                $nested->where('id', $q)
                    ->orWhere('code', 'like', "%{$q}%")
                    ->orWhere('customer_name', 'like', "%{$q}%")
                    ->orWhere('customer_phone', 'like', "%{$q}%");
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->query('date'));
        }

        foreach (['branch_id', 'fulfillment_method', 'payment_method', 'payment_status', 'status'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->query($filter));
            }
        }

        $orders = $query->get();

        return view('admin.orders.index', [
            'orders' => $orders,
            'branches' => $branches,
            'todayOrdersCount' => Order::whereDate('created_at', Carbon::today())->count(),
            'unseenOrdersCount' => Order::where('is_seen', false)->count(),
        ]);
    }

    public function show(Order $order)
    {
        $order->load(['items', 'branch', 'payments']);
        $order->update(['is_seen' => true]);

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,paid,preparing,ready_for_pickup,out_for_delivery,completed,cancelled,failed_payment'],
        ]);

        $order->update([
            'status' => $data['status'],
        ]);

        return back()->with('success', 'تم تحديث حالة الطلب');
    }

    public function checkNew(Request $request)
    {
        $lastId = (int) $request->query('last_id', 0);
        $newOrdersCount = Order::where('id', '>', $lastId)->count();

        return response()->json([
            'has_new' => $newOrdersCount > 0,
            'count' => $newOrdersCount,
            'last_id' => Order::max('id') ?? $lastId,
        ]);
    }
}
