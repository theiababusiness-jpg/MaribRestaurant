<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>دخول المدير</title>

  <link rel="stylesheet" href="/css/variables.css">
  <link rel="stylesheet" href="/css/admin.css">
</head>
<body
  style="
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    background: var(--background-color);
  "
>

  <div
    class="container"
    style="
      width:100%;
      max-width:520px;
      padding:14px;
    "
  >
    <div class="card">

      <h2 class="page-title" style="text-align:center;">دخول المدير</h2>

      {{-- رسالة خطأ عامة --}}
      @if(session('error'))
        <div class="alert alert--danger" style="margin-top:12px;">
          {{ session('error') }}
        </div>
      @endif

      {{-- أخطاء التحقق --}}
      @if($errors->any())
        <div class="alert alert--danger" style="margin-top:12px;">
          <ul style="margin:0; padding:0 18px;">
            @foreach($errors->all() as $msg)
              <li>{{ $msg }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form
        method="POST"
        action="{{ route('admin.login.post') }}"
        style="
          margin-top:16px;
          display:grid;
          gap:14px;
        "
      >
        @csrf

        <div>
          <label class="label">البريد الإلكتروني</label>
          <input
            class="input"
            name="email"
            type="email"
            value="{{ old('email') }}"
            required
            autocomplete="email"
          >
        </div>

        <div>
          <label class="label">كلمة المرور</label>
          <input
            class="input"
            name="password"
            type="password"
            required
            autocomplete="current-password"
          >
        </div>

        <button class="btn" type="submit" style="width:100%;">
          دخول
        </button>

      </form>

    </div>
  </div>

</body>
</html>
