<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Branch;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\OptionGroup;
use App\Models\Order;
use App\Models\Product;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'ordersCount' => Order::count(),
            'productsCount' => Product::count(),
            'categoriesCount' => Category::count(),
            'groupsCount' => OptionGroup::count(),
            'bannersCount' => Banner::count(),
            'branchesCount' => Branch::count(),
            'latestOrders' => Order::with('branch')->orderByDesc('id')->limit(5)->get(),
            'unreadMessagesCount' => ContactMessage::where('is_read', false)->count(),
        ]);
    }
}
