<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>الخيارات - مأرب</title>
  <link rel="stylesheet" href="/css/variables.css">
  <link rel="stylesheet" href="/css/admin.css">
</head>
<body>

<div class="container" style="margin-top:20px;">

  <div class="card">
    <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
      <div>
    
    
      <h2 class="page-title" style="margin:0;">خيارات المجموعة:{{ $optionGroup->name }} 
          </h2>
        <p class="page-subtitle" style="margin:6px 0 0;">أضف/عدل خيارات هذه المجموعة</p>
      </div>

      <div style="display:flex; gap:10px; flex-wrap:wrap;">
        <a class="btn" href="{{ route('admin.options.create', $optionGroup) }}">+ إضافة خيار</a>
        <a class="btn btn--ghost" href="{{ route('admin.option_groups.index') }}">رجوع للمجموعات</a>
      </div>
    </div>

    @if(session('success'))
      <div class="alert alert--success" style="margin-top:12px;">
        {{ session('success') }}
      </div>
    @endif
  </div>

  <div class="card" style="margin-top:14px;">
    @if($options->count() === 0)
      <p class="page-subtitle">لا توجد خيارات حاليا.</p>
    @else
      <div style="overflow:auto;">
        <table class="table">
          <thead>
            <tr>
              <th>#</th>
              <th>الاسم</th>
              <th>فرق السعر</th>
              <th style="min-width:160px;">إجراءات</th>
            </tr>
          </thead>
          <tbody>
            @foreach($options as $opt)
              <tr>
                <td>{{ $opt->id }}</td>
                <td><strong>{{ $opt->name }}</strong></td>
                <td>{{ number_format((float)$opt->price_delta, 0) }} ريال</td>
                <td>
                  <div style="display:flex; gap:8px; flex-wrap:wrap;">
                    <a class="btn btn--small" href="{{ route('admin.options.edit', $opt) }}">تعديل</a>

                    <form action="{{ route('admin.options.destroy', $opt) }}" method="POST"
                          onsubmit="return confirm('تأكيد حذف الخيار؟');">
                      @csrf
                      @method('DELETE')
                      <button class="btn btn--small" type="submit" style="background:var(--secondary-color);">
                        حذف
                      </button>
                    </form>
                  </div>
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
