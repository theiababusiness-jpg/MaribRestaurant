<?php

use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ContactController as AdminContactController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OptionController;
use App\Http\Controllers\Admin\OptionGroupController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Front\CartController;
use App\Http\Controllers\Front\CheckoutController;
use App\Http\Controllers\Front\ContactController;
use App\Http\Controllers\Front\HomeController;
use App\Http\Controllers\Front\MenuController;
use App\Http\Controllers\Front\OrderController;
use App\Http\Controllers\Front\PageController;
use App\Http\Controllers\Front\PaymentController;
use App\Http\Controllers\Front\ProductController;
use App\Http\Controllers\Front\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/menu', [MenuController::class, 'index'])->name('menu.index');
Route::get('/menu/{category:slug}', [MenuController::class, 'show'])->name('menu.category');
Route::get('/product/{product}', [ProductController::class, 'show'])->name('product.show');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::delete('/cart/remove/{index}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/inc/{index}', [CartController::class, 'inc'])->name('cart.inc');
Route::post('/cart/dec/{index}', [CartController::class, 'dec'])->name('cart.dec');
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::get('/checkout/csrf-token', [CheckoutController::class, 'refreshCsrfToken'])->name('checkout.csrf_token');
Route::post('/checkout/delivery-quote', [CheckoutController::class, 'quoteDelivery'])->name('checkout.delivery_quote');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/order/success/{code}', [OrderController::class, 'success'])->name('order.success');
Route::get('/payments/{payment}/callback', [PaymentController::class, 'callback'])->name('payments.callback');
Route::get('/payments/{payment}/error', [PaymentController::class, 'error'])->name('payments.error');
Route::get('/payments/{payment}/result', [PaymentController::class, 'result'])->name('payments.result');
Route::post('/payments/{payment}/retry', [PaymentController::class, 'retry'])->name('payments.retry');
Route::post('/payments/webhook/moyasar', [PaymentController::class, 'webhook'])->name('payments.webhook');
Route::post('/payments/webhook/myfatoorah', [PaymentController::class, 'webhook'])->name('payments.webhook.legacy');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::get('/set-lang/{lang}', function ($lang) {

    abort_unless(in_array($lang, ['ar', 'en'], true), 404);

    session([
        'front_lang' => $lang,
    ]);

    $previousUrl = url()->previous();
    /*
    |--------------------------------------------------------------------------
    | Remove old lang query if exists
    |--------------------------------------------------------------------------
    */
    $previousUrl = preg_replace('/([?&])lang=(ar|en)(&)?/', '$1', $previousUrl);
    /*
    |--------------------------------------------------------------------------
    | Clean duplicated ? or &
    |--------------------------------------------------------------------------
    */
    $previousUrl = rtrim($previousUrl, '?&');
    /*
    |--------------------------------------------------------------------------
    | Append current language query
    |--------------------------------------------------------------------------
    */
    $separator = str_contains($previousUrl, '?') ? '&' : '?';

    return redirect($previousUrl . $separator . 'lang=' . $lang);

})->name('front.lang');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware(['admin.auth', 'admin.notifications'])->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings/admins', [AdminSettingsController::class, 'storeAdmin'])->name('settings.admins.store');
        Route::post('/settings/password', [AdminSettingsController::class, 'changePassword'])->name('settings.password');
        Route::post('/settings/site', [AdminSettingsController::class, 'updateSite'])->name('settings.site');
        Route::delete('/settings/admins/{admin}', [AdminSettingsController::class, 'destroyAdmin'])->name('settings.admins.destroy');

        Route::get('/branches', [BranchController::class, 'index'])->name('branches.index');
        Route::get('/branches/create', [BranchController::class, 'create'])->name('branches.create');
        Route::post('/branches', [BranchController::class, 'store'])->name('branches.store');
        Route::get('/branches/{branch}/edit', [BranchController::class, 'edit'])->name('branches.edit');
        Route::put('/branches/{branch}', [BranchController::class, 'update'])->name('branches.update');
        Route::delete('/branches/{branch}', [BranchController::class, 'destroy'])->name('branches.destroy');

        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
        Route::get('/products/create', [AdminProductController::class, 'create'])->name('products.create');
        Route::post('/products', [AdminProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [AdminProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [AdminProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [AdminProductController::class, 'destroy'])->name('products.destroy');

        Route::get('/option-groups', [OptionGroupController::class, 'index'])->name('option_groups.index');
        Route::get('/option-groups/create', [OptionGroupController::class, 'create'])->name('option_groups.create');
        Route::post('/option-groups', [OptionGroupController::class, 'store'])->name('option_groups.store');
        Route::get('/option-groups/{optionGroup}/edit', [OptionGroupController::class, 'edit'])->name('option_groups.edit');
        Route::put('/option-groups/{optionGroup}', [OptionGroupController::class, 'update'])->name('option_groups.update');
        Route::delete('/option-groups/{optionGroup}', [OptionGroupController::class, 'destroy'])->name('option_groups.destroy');

        Route::get('/option-groups/{optionGroup}/options', [OptionController::class, 'index'])->name('options.index');
        Route::get('/option-groups/{optionGroup}/options/create', [OptionController::class, 'create'])->name('options.create');
        Route::post('/option-groups/{optionGroup}/options', [OptionController::class, 'store'])->name('options.store');
        Route::get('/options/{option}/edit', [OptionController::class, 'edit'])->name('options.edit');
        Route::put('/options/{option}', [OptionController::class, 'update'])->name('options.update');
        Route::delete('/options/{option}', [OptionController::class, 'destroy'])->name('options.destroy');

        Route::get('/contact', [AdminContactController::class, 'index'])->name('contact.index');
        Route::put('/contact/{contact}/read', [AdminContactController::class, 'markRead'])->name('contact.read');

        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/check-new', [AdminOrderController::class, 'checkNew'])->name('orders.checkNew');
        Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::put('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');

        Route::prefix('banners')->name('banners.')->group(function () {
            Route::get('/', [BannerController::class, 'index'])->name('index');
            Route::get('/create', [BannerController::class, 'create'])->name('create');
            Route::post('/', [BannerController::class, 'store'])->name('store');
            Route::get('/{banner}/edit', [BannerController::class, 'edit'])->name('edit');
            Route::put('/{banner}', [BannerController::class, 'update'])->name('update');
            Route::delete('/{banner}', [BannerController::class, 'destroy'])->name('destroy');
        });
    });
});
