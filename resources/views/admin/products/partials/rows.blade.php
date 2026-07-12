@foreach($products as $p)
<tr>
    <td>{{ $p->id }}</td>

    <td>
        @if($p->image_path)
            <img src="/{{ $p->image_path }}"
                 loading="lazy"
                 style="width:46px;height:46px;object-fit:cover;border-radius:10px;">
        @else
            <span style="opacity:.6;">-</span>
        @endif
    </td>

    <td><strong>{{ $p->name }}</strong></td>
    <td>{{ $p->category?->name ?? '-' }}</td>
<td>
    @if((float)$p->price == 0)
        حسب التخصيص
    @else
        {{ number_format((float)$p->price, 0) }} ريال
    @endif
</td>    <td>{{ $p->slug }}</td>

    <td>
        @if($p->is_active)
            <span class="badge badge--ok">نعم</span>
        @else
            <span class="badge badge--off">لا</span>
        @endif
    </td>

    <td>{{ $p->sort_order }}</td>

    <td>
        <div style="display:flex; gap:8px; flex-wrap:wrap;">
            <a class="btn btn--small"
               href="{{ route('admin.products.edit', $p) }}">
                تعديل
            </a>

            <form action="{{ route('admin.products.destroy', $p) }}"
                  method="POST"
                  onsubmit="return confirm('تأكيد حذف المنتج؟');">
                @csrf
                @method('DELETE')
                <button class="btn btn--small"
                        type="submit"
                        style="background:var(--secondary-color);">
                    حذف
                </button>
            </form>
        </div>
    </td>
</tr>
@endforeach
