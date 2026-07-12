<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        // data: بيانات الدخول بعد التحقق
        $data = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // attempt: محاولة تسجيل الدخول عبر guard admin (بدون remember)
        if (auth('admin')->attempt(['email' => $data['email'], 'password' => $data['password']])) {
            // regenerate: تجديد السيشن للحماية بعد تسجيل الدخول
            $request->session()->regenerate();

            return redirect()->route('admin.dashboard');
        }

        // withErrors: لعرض الخطأ بشكل صحيح في Blade مع @error('email')
        return back()
            ->withErrors(['email' => 'بيانات الدخول غير صحيحة'])
            ->withInput();
    }

    public function logout(Request $request)
    {
        auth('admin')->logout();

        // invalidate: إلغاء السيشن الحالي
        $request->session()->invalidate();

        // regenerateToken: توليد CSRF token جديد لتجنب 419
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
