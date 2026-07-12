<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة فرع</title>
    <link rel="stylesheet" href="/css/variables.css">
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>
<div class="container" style="margin-top:20px;">
    <div class="card">
        <h2 class="page-title" style="margin:0 0 6px;">إضافة فرع جديد</h2>
        <p class="page-subtitle" style="margin:0;">أدخل بيانات الفرع وإحداثياته لتفعيل الاستلام والتوصيل.</p>
    </div>

    @if($errors->any())
        <div class="alert alert--danger" style="margin-top:14px;">
            <ul style="margin:0; padding:0 18px;">
                @foreach($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card" style="margin-top:14px;">
        <form method="POST" action="{{ route('admin.branches.store') }}">
            @include('admin.branches._form')
        </form>
    </div>
</div>
<script src="{{ asset('js/auto-translate.js') }}"></script>
</body>
</html>
