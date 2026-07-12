<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>إعدادات الإدارة</title>
  <link rel="stylesheet" href="/css/variables.css">
  <link rel="stylesheet" href="/css/admin.css">
</head>
<body>

<div class="container" style="margin-top:20px;">
  <div class="card">
    <div style="display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap; align-items:flex-start;">
      <div>
        <h2 class="page-title" style="margin:0;">إعدادات الإدارة والموقع</h2>
        <p class="page-subtitle" style="margin:6px 0 0;">إدارة المشرفين، بيانات الموقع العامة، وتهيئة SEO الأساسية.</p>
      </div>

      <div style="display:flex; gap:10px; flex-wrap:wrap;">
        <a class="btn btn--ghost" href="{{ route('admin.branches.index') }}">الفروع</a>
        <a class="btn btn--ghost" href="{{ route('admin.orders.index') }}">الطلبات</a>
        <a class="btn btn--ghost" href="{{ route('admin.dashboard') }}">لوحة التحكم</a>

        <form method="POST" action="{{ route('admin.logout') }}">
          @csrf
          <button class="btn" type="submit" style="background:#b00020;">تسجيل الخروج</button>
        </form>
      </div>
    </div>

    @if(session('success'))
      <div class="alert alert--success" style="margin-top:12px;">{{ session('success') }}</div>
    @endif

    @if(session('error'))
      <div class="alert alert--danger" style="margin-top:12px;">{{ session('error') }}</div>
    @endif

    @if($errors->any())
      <div class="alert alert--danger" style="margin-top:12px;">
        <ul style="margin:0; padding:0 18px;">
          @foreach($errors->all() as $msg)
            <li>{{ $msg }}</li>
          @endforeach
        </ul>
      </div>
    @endif
  </div>

  <div class="card" style="margin-top:14px;">
    <h3 class="card__title" style="margin:0 0 10px;">إعدادات الموقع</h3>

    <form method="POST" action="{{ route('admin.settings.site') }}" style="display:grid; gap:14px;">
      @csrf

      <div class="admin-grid" style="grid-template-columns: repeat(2, minmax(0, 1fr));">
        <div>
          <label class="label">اسم الموقع بالعربي</label>
          <input class="input" name="site_name" value="{{ old('site_name', $siteConfig?->site_name) }}">
        </div>
        <div>
          <label class="label">اسم الموقع بالإنجليزي</label>
          <input class="input" name="site_name_en" value="{{ old('site_name_en', $siteConfig?->site_name_en) }}">
        </div>
      </div>

      <div class="admin-grid" style="grid-template-columns: repeat(2, minmax(0, 1fr));">
        <div>
          <label class="label">عنوان الصفحة الرئيسية بالعربي</label>
          <input class="input" name="home_meta_title" value="{{ old('home_meta_title', $siteConfig?->home_meta_title) }}">
        </div>
        <div>
          <label class="label">عنوان الصفحة الرئيسية بالإنجليزي</label>
          <input class="input" name="home_meta_title_en" value="{{ old('home_meta_title_en', $siteConfig?->home_meta_title_en) }}">
        </div>
      </div>

      <div class="admin-grid" style="grid-template-columns: repeat(2, minmax(0, 1fr));">
        <div>
          <label class="label">الوصف الافتراضي للعربي</label>
          <textarea class="input" name="default_meta_description" rows="3">{{ old('default_meta_description', $siteConfig?->default_meta_description) }}</textarea>
        </div>
        <div>
          <label class="label">الوصف الافتراضي للإنجليزي</label>
          <textarea class="input" name="default_meta_description_en" rows="3">{{ old('default_meta_description_en', $siteConfig?->default_meta_description_en) }}</textarea>
        </div>
      </div>

      <div class="admin-grid" style="grid-template-columns: repeat(2, minmax(0, 1fr));">
        <div>
          <label class="label">وصف الصفحة الرئيسية بالعربي</label>
          <textarea class="input" name="home_meta_description" rows="3">{{ old('home_meta_description', $siteConfig?->home_meta_description) }}</textarea>
        </div>
        <div>
          <label class="label">وصف الصفحة الرئيسية بالإنجليزي</label>
          <textarea class="input" name="home_meta_description_en" rows="3">{{ old('home_meta_description_en', $siteConfig?->home_meta_description_en) }}</textarea>
        </div>
      </div>

      <div class="admin-grid" style="grid-template-columns: repeat(3, minmax(0, 1fr));">
        <div>
          <label class="label">رقم الدعم</label>
          <input class="input" name="support_phone" value="{{ old('support_phone', $siteConfig?->support_phone) }}">
        </div>
        <div>
          <label class="label">رقم إضافي</label>
          <input class="input" name="secondary_phone" value="{{ old('secondary_phone', $siteConfig?->secondary_phone) }}">
        </div>
        <div>
          <label class="label">واتساب</label>
          <input class="input" name="whatsapp_number" value="{{ old('whatsapp_number', $siteConfig?->whatsapp_number) }}">
        </div>
      </div>

      <div class="admin-grid" style="grid-template-columns: repeat(2, minmax(0, 1fr));">
        <div>
          <label class="label">العنوان بالعربي</label>
          <input class="input" name="primary_address" value="{{ old('primary_address', $siteConfig?->primary_address) }}">
        </div>
        <div>
          <label class="label">العنوان بالإنجليزي</label>
          <input class="input" name="primary_address_en" value="{{ old('primary_address_en', $siteConfig?->primary_address_en) }}">
        </div>
      </div>

      <div class="admin-grid" style="grid-template-columns: repeat(2, minmax(0, 1fr));">
        <div>
          <label class="label">ساعات العمل بالعربي</label>
          <input class="input" name="working_hours" value="{{ old('working_hours', $siteConfig?->working_hours) }}">
        </div>
        <div>
          <label class="label">ساعات العمل بالإنجليزي</label>
          <input class="input" name="working_hours_en" value="{{ old('working_hours_en', $siteConfig?->working_hours_en) }}">
        </div>
      </div>

      <div class="admin-grid" style="grid-template-columns: repeat(2, minmax(0, 1fr));">
        <div>
          <label class="label">رابط الخريطة</label>
          <input class="input" name="google_maps_url" value="{{ old('google_maps_url', $siteConfig?->google_maps_url) }}">
        </div>
        <div>
          <label class="label">رابط تضمين الخريطة</label>
          <input class="input" name="google_maps_embed_url" value="{{ old('google_maps_embed_url', $siteConfig?->google_maps_embed_url) }}">
        </div>
      </div>

      <div class="admin-grid" style="grid-template-columns: repeat(2, minmax(0, 1fr));">
        <div>
          <label class="label">Canonical Base URL</label>
          <input class="input" name="canonical_base_url" value="{{ old('canonical_base_url', $siteConfig?->canonical_base_url) }}">
        </div>
        <div>
          <label class="label">Google Analytics Measurement ID</label>
          <input class="input" name="ga_measurement_id" value="{{ old('ga_measurement_id', $siteConfig?->ga_measurement_id) }}">
        </div>
      </div>

      <div>
        <label class="label">Google Search Console Verification</label>
        <input class="input" name="search_console_verification" value="{{ old('search_console_verification', $siteConfig?->search_console_verification) }}">
      </div>

      <div class="admin-grid" style="grid-template-columns: repeat(4, minmax(0, 1fr));">
        <div>
          <label class="label">Facebook</label>
          <input class="input" name="facebook_url" value="{{ old('facebook_url', $siteConfig?->facebook_url) }}">
        </div>
        <div>
          <label class="label">Instagram</label>
          <input class="input" name="instagram_url" value="{{ old('instagram_url', $siteConfig?->instagram_url) }}">
        </div>
        <div>
          <label class="label">X</label>
          <input class="input" name="x_url" value="{{ old('x_url', $siteConfig?->x_url) }}">
        </div>
        <div>
          <label class="label">TikTok</label>
          <input class="input" name="tiktok_url" value="{{ old('tiktok_url', $siteConfig?->tiktok_url) }}">
        </div>
      </div>

      <div>
        <button class="btn" type="submit">حفظ إعدادات الموقع</button>
      </div>
    </form>
  </div>

  <div class="admin-grid" style="margin-top:14px; grid-template-columns: repeat(2, minmax(0, 1fr));">
    <div class="card">
      <h3 class="card__title" style="margin:0 0 10px;">إضافة مدير جديد</h3>

      <form method="POST" action="{{ route('admin.settings.admins.store') }}" style="display:grid; gap:12px;">
        @csrf

        <div>
          <label class="label">الاسم</label>
          <input class="input" name="name" type="text" value="{{ old('name') }}" required>
        </div>

        <div>
          <label class="label">البريد</label>
          <input class="input" name="email" type="email" value="{{ old('email') }}" required>
        </div>

        <div>
          <label class="label">كلمة المرور</label>
          <input class="input" name="password" type="password" required>
        </div>

        <div>
          <label class="label">تأكيد كلمة المرور</label>
          <input class="input" name="password_confirmation" type="password" required>
        </div>

        <button class="btn" type="submit">إضافة</button>
      </form>
    </div>

    <div class="card">
      <h3 class="card__title" style="margin:0 0 10px;">تغيير كلمة المرور</h3>

      <form method="POST" action="{{ route('admin.settings.password') }}" style="display:grid; gap:12px;">
        @csrf

        <div>
          <label class="label">كلمة المرور الحالية</label>
          <input class="input" name="current_password" type="password" required>
        </div>

        <div>
          <label class="label">كلمة المرور الجديدة</label>
          <input class="input" name="new_password" type="password" required>
        </div>

        <div>
          <label class="label">تأكيد كلمة المرور الجديدة</label>
          <input class="input" name="new_password_confirmation" type="password" required>
        </div>

        <button class="btn" type="submit">حفظ</button>
      </form>
    </div>
  </div>

  <div class="card" style="margin-top:14px;">
    <div style="display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap; align-items:center;">
      <h3 class="card__title" style="margin:0;">المدراء</h3>
      <small style="opacity:.75;">لا يمكن حذف حسابك الحالي.</small>
    </div>

    <div class="table-wrap" style="margin-top:10px;">
      <table class="table">
        <thead>
          <tr>
            <th>#</th>
            <th>الاسم</th>
            <th>البريد</th>
            <th style="min-width:140px;">إجراء</th>
          </tr>
        </thead>
        <tbody>
          @foreach($admins as $a)
            <tr>
              <td>{{ $a->id }}</td>
              <td>{{ $a->name }}</td>
              <td>{{ $a->email }}</td>
              <td>
                @if((int)$a->id === (int)$currentAdminId)
                  <span class="badge">أنت</span>
                @else
                  <form method="POST"
                        action="{{ route('admin.settings.admins.destroy', $a) }}"
                        onsubmit="return confirm('تأكيد حذف هذا المدير؟');">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn--small" type="submit" style="background:#b00020;">حذف</button>
                  </form>
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

<style>
  @media (max-width: 900px){
    .admin-grid{ grid-template-columns: 1fr !important; }
  }
</style>

</body>
</html>
