<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رسائل التواصل</title>
    <link rel="stylesheet" href="/css/variables.css">
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>

<div class="container" style="max-width:1100px; margin-top:20px;">

    <div class="card">
        <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
            <div>
                <h2 class="page-title">رسائل التواصل</h2>
                <p class="page-subtitle">جميع رسائل العملاء</p>
            </div>
            <a class="btn" href="{{ route('admin.dashboard') }}">رجوع</a>
        </div>

        @if(session('success'))
            <div class="alert alert--success" style="margin-top:12px;">
                {{ session('success') }}
            </div>
        @endif
    </div>

    <div class="card" style="margin-top:14px;">
        @if($messages->count() === 0)
            <p>لا توجد رسائل.</p>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>الاسم</th>
                        <th>الهاتف</th>
                        <th>الرسالة</th>
                        <th>الحالة</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($messages as $m)
                        <tr>
                            <td>{{ $m->id }}</td>
                            <td>{{ $m->name }}</td>
                            <td>{{ $m->phone }}</td>
                            <td style="max-width:300px; white-space:normal;">{{ $m->message }}</td>
                            <td>
                                @if(!$m->is_read)
                                    <form method="POST" action="{{ route('admin.contact.read', $m) }}">
                                        @csrf
                                        @method('PUT')
                                        <button class="btn btn--small">غير مقروءة</button>
                                    </form>
                                @else
                                    <span class="badge badge--ok">مقروءة</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>

</body>
</html>
