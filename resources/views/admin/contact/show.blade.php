{{-- resources/views/admin/contact/show.blade.php --}}
@extends('admin.layout')

@section('content')
<div class="card">
    <h2 class="page-title">تفاصيل الرسالة</h2>
</div>

<div class="card" style="margin-top:14px;">
    <p><strong>الاسم:</strong> {{ $contactMessage->name }}</p>
    <p><strong>الهاتف:</strong> {{ $contactMessage->phone ?? '—' }}</p>
    <p><strong>الرسالة:</strong></p>

    <div class="card" style="margin-top:10px;">
        {{ $contactMessage->message }}
    </div>

    <div style="margin-top:14px; display:flex; gap:10px;">
        @if($contactMessage->phone)
            <a class="btn"
               href="https://wa.me/{{ preg_replace('/\D/', '', $contactMessage->phone) }}"
               target="_blank">
                تواصل واتساب
            </a>
        @endif

        <a class="btn btn--ghost" href="{{ route('admin.contact.index') }}">
            رجوع
        </a>
    </div>
</div>
@endsection
