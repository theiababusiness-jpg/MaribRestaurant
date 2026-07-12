<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المنتجات - مأرب</title>
    <link rel="stylesheet" href="/css/variables.css">
    <link rel="stylesheet" href="/css/admin.css">

    <style>
        .products-filter-bar{
            margin-top:14px;
            display:flex;
            gap:12px;
            flex-wrap:wrap;
            align-items:end;
        }

        .products-filter-bar input,
        .products-filter-bar select{
            padding:8px 10px;
            border-radius:8px;
            border:1px solid rgba(0,0,0,.15);
            min-width:180px;
        }

        .products-table-wrapper{
            overflow:auto;
            max-height:70vh;
        }

        .products-table-wrapper thead th{
            position: sticky;
            top: 0;
            background:#fff;
            z-index:5;
        }

        .load-trigger{
            height: 1px;
        }
    </style>
</head>
<body>

<div class="container" style="max-width:1100px; margin-top:20px;">

    <div class="card">
        <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
            <div>
                <h2 class="page-title" style="margin:0;">إدارة المنتجات</h2>
                <p class="page-subtitle" style="margin:6px 0 0;">
                    إضافة وتعديل المنتجات وربطها بالتصنيفات.
                </p>
            </div>

            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <a class="btn" href="{{ route('admin.products.create') }}">+ إضافة منتج</a>
                <a class="btn btn--ghost" href="{{ route('admin.categories.index') }}">التصنيفات</a>
                <a class="btn btn--ghost" href="{{ route('admin.dashboard') }}">لوحة التحكم</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert--success" style="margin-top:12px;">
                {{ session('success') }}
            </div>
        @endif

        <form method="GET" action="{{ route('admin.products.index') }}" class="products-filter-bar">
            <div>
                <label class="label">بحث بالاسم</label>
                <input type="text" name="search" value="{{ request('search') }}">
            </div>

            <div>
                <label class="label">التصنيف</label>
                <select name="category_id">
                    <option value="">كل الأقسام</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}"
                            {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <button class="btn" type="submit">تطبيق</button>
            </div>
        </form>
    </div>

    <div class="card" style="margin-top:14px;">
        <div class="products-table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الصورة</th>
                        <th>الاسم</th>
                        <th>التصنيف</th>
                        <th>السعر</th>
                        <th>Slug</th>
                        <th>مفعل</th>
                        <th>ترتيب</th>
                        <th style="min-width:180px;">إجراءات</th>
                    </tr>
                </thead>

                <tbody id="products-body">
                    @include('admin.products.partials.rows')
                </tbody>
            </table>

            <div id="load-trigger" class="load-trigger"></div>
        </div>
    </div>
</div>

<script>
let page = 1;
let loading = false;
let hasMore = true;

const trigger = document.getElementById('load-trigger');
const wrapper = document.querySelector('.products-table-wrapper');

const observer = new IntersectionObserver(entries => {

    if (!entries[0].isIntersecting || loading || !hasMore) return;

    loading = true;
    page++;

    let url = new URL(window.location.href);
    url.searchParams.set('page', page);

    fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {

        document
            .getElementById('products-body')
            .insertAdjacentHTML('beforeend', data.html);

        hasMore = data.hasMore;
        loading = false;
    })
    .catch(() => {
        loading = false;
    });

}, {
    root: wrapper,
    threshold: 0.1
});

observer.observe(trigger);
</script>

</body>
</html>
