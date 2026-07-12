<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\SiteSetting;
use App\Support\SiteSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminSettingsController extends Controller
{
    public function index()
    {
        $admins = AdminUser::orderByDesc('id')->get();
        $currentAdminId = auth('admin')->id();
        $siteConfig = SiteSettings::current();

        return view('admin.settings.index', compact('admins', 'currentAdminId', 'siteConfig'));
    }

    public function storeAdmin(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'unique:admin_users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        AdminUser::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        return back()->with('success', 'تم إضافة مدير جديد');
    }

    public function changePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $admin = auth('admin')->user();

        if (! $admin || ! Hash::check($data['current_password'], $admin->password)) {
            return back()->with('error', 'كلمة المرور الحالية غير صحيحة');
        }

        $admin->password = Hash::make($data['new_password']);
        $admin->save();

        return back()->with('success', 'تم تغيير كلمة المرور بنجاح');
    }

    public function destroyAdmin(AdminUser $admin)
    {
        $currentId = auth('admin')->id();

        if ($currentId && (int) $admin->id === (int) $currentId) {
            return back()->with('error', 'لا يمكن حذف حسابك الحالي');
        }

        $admin->delete();

        return back()->with('success', 'تم حذف المدير');
    }

    public function updateSite(Request $request)
    {
        $data = $request->validate([
            'site_name' => ['nullable', 'string', 'max:255'],
            'site_name_en' => ['nullable', 'string', 'max:255'],
            'default_meta_description' => ['nullable', 'string'],
            'default_meta_description_en' => ['nullable', 'string'],
            'home_meta_title' => ['nullable', 'string', 'max:255'],
            'home_meta_title_en' => ['nullable', 'string', 'max:255'],
            'home_meta_description' => ['nullable', 'string'],
            'home_meta_description_en' => ['nullable', 'string'],
            'support_phone' => ['nullable', 'string', 'max:50'],
            'secondary_phone' => ['nullable', 'string', 'max:50'],
            'whatsapp_number' => ['nullable', 'string', 'max:50'],
            'primary_address' => ['nullable', 'string', 'max:255'],
            'primary_address_en' => ['nullable', 'string', 'max:255'],
            'working_hours' => ['nullable', 'string', 'max:255'],
            'working_hours_en' => ['nullable', 'string', 'max:255'],
            'google_maps_url' => ['nullable', 'url'],
            'google_maps_embed_url' => ['nullable', 'string'],
            'facebook_url' => ['nullable', 'url'],
            'instagram_url' => ['nullable', 'url'],
            'x_url' => ['nullable', 'url'],
            'tiktok_url' => ['nullable', 'url'],
            'canonical_base_url' => ['nullable', 'url'],
            'ga_measurement_id' => ['nullable', 'string', 'max:100'],
            'search_console_verification' => ['nullable', 'string', 'max:255'],
        ]);

        $settings = SiteSettings::current() ?? SiteSetting::create(SiteSetting::defaults());
        $settings->update($data);
        SiteSettings::forget();

        return back()->with('success', 'تم تحديث إعدادات الموقع');
    }
}
