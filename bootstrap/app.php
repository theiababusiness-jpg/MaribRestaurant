<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))

    /*
    |--------------------------------------------------------------------------
    | Routing
    |--------------------------------------------------------------------------
    | ربط ملفات routes (web / console) + health check
    */
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    | هنا يتم تسجيل Middleware في Laravel 10
    | بدل Kernel.php (غير موجود في Laravel 10)
    
    ->withMiddleware(function (Middleware $middleware): void {

        // alias = اسم مختصر نستخدمه في routes
        $middleware->alias([
            'admin.auth' => \App\Http\Middleware\AdminAuth::class,
        ]);

    })
	*/
    
    ->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'admin.auth' => \App\Http\Middleware\AdminAuth::class,
        'admin.notifications' => \App\Http\Middleware\InjectAdminOrderNotifications::class,
    ]);

    // أضف هذا السطر هنا لمنع خطأ 419 في صفحة الدخول
    $middleware->validateCsrfTokens(except: [
        'login', 
        'admin/login', // أضف هذا إذا كان رابط الدخول يبدأ بـ admin
    ]);
})
    /*
    |--------------------------------------------------------------------------
    | Exceptions
    |--------------------------------------------------------------------------
    */
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })

    ->create();
