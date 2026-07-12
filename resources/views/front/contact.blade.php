@extends('layouts.app')

@php
use App\Support\FrontLang;
@endphp

@section('content')

<div class="page-wrap dark-section">

    <div class="page-hero dark-section">
        <span class="badge dark-text">
            {{ FrontLang::t('تواصل معنا', 'Contact Us') }}
        </span>

        <h1 class="page-hero__title dark-text" style="margin-top:10px;">
            {{ FrontLang::t('يسعدنا سماعك', 'We Are Happy to Hear From You') }}
        </h1>

        <p class="page-hero__sub dark-text">
            {{ FrontLang::t(
                'اكتب رسالتك وسنرد عليك في أقرب وقت. ويمكنك التواصل أيضا عبر الهاتف أو واتساب.',
                'Write your message and we will reply as soon as possible. You can also contact us via phone or WhatsApp.'
            ) }}
        </p>
    </div>

    <div class="page-grid">

        <div class="card-box dark-card">
            <h2 class="dark-text" style="margin:0 0 10px; font-weight:900;">
                {{ FrontLang::t('أرسل رسالة', 'Send a Message') }}
            </h2>

            <!-- نموذج واجهة فقط -->
           <form class="form" action="{{ route('contact.store') }}" method="POST">
            @csrf
                <div>
                    <label class="label dark-text">
                        {{ FrontLang::t('الاسم', 'Name') }}
                    </label>
                    <input
                        class="input dark-input"
                        type="text"
                        name="name"
                        placeholder="{{ FrontLang::t('اكتب اسمك', 'Enter your name') }}"
                    >
                </div>

                <div>
                    <label class="label dark-text">
                        {{ FrontLang::t('رقم الهاتف', 'Phone Number') }}
                    </label>
                    <input
                        class="input dark-input"
                        type="text"
                        name="phone"
                    >
                </div>

                <div>
                    <label class="label dark-text">
                        {{ FrontLang::t('الرسالة', 'Message') }}
                    </label>
                    <textarea
                        class="textarea dark-input"
                        name="message"
                        placeholder="{{ FrontLang::t('اكتب رسالتك هنا', 'Write your message here') }}"
                    ></textarea>
                </div>

                <button class="btn dark-btn" type="submit">
                    {{ FrontLang::t('إرسال', 'Send') }}
                </button>
            </form>

            <small class="dark-text" style="display:block; margin-top:10px; opacity:.7;">
                {{ FrontLang::t(
                    'هذه الصفحة حاليا واجهة فقط. لاحقا نربط الإرسال (واتساب أو حفظ في قاعدة البيانات).',
                    'This page is currently UI only. Later we will connect sending (WhatsApp or database).'
                ) }}
            </small>
        </div>

        <div class="card-box dark-card">
            <h2 class="dark-text" style="margin:0 0 10px; font-weight:900;">
                {{ FrontLang::t('معلومات التواصل', 'Contact Information') }}
            </h2>
            

            <div class="info-row">
                <div class="info-row__label dark-text">
                    {{ FrontLang::t('الهاتف', 'Phone') }}
                </div>
                <div class="info-row__value dark-text">0138092388-0138092420</div>
            </div>

            <div class="info-row">
                <div class="info-row__label dark-text">
                    {{ FrontLang::t('التواصل', 'communicaction') }}
                </div>
                <div class="info-row__value dark-text">0567510757-0530226751</div>
            </div>
                    
            <div class="info-row">
                <div class="info-row__label dark-text">
                    {{ FrontLang::t('واتساب', 'WhatsApp') }}
                </div>
                <div class="info-row__value dark-text">558111372</div>
            </div>

            <div class="info-row">
                <div class="info-row__label dark-text">
                    {{ FrontLang::t('العنوان', 'Address') }}
                </div>
                <div class="info-row__value dark-text">
                    {{ FrontLang::t('الدمام حي السلام', 'Dammam, Al Salam district') }}
                </div>
            </div>

            <div style="margin-top:12px; display:flex; gap:10px; flex-wrap:wrap;">
                <a class="btn dark-btn" href="https://wa.me/966558111372" target="_blank">
                    {{ FrontLang::t('واتساب', 'WhatsApp') }}
                </a>
                <a class="btn btn--ghost dark-btn-ghost" href="/menu">
                    {{ FrontLang::t('المنيو', 'Menu') }}
                </a>
            </div>
        </div>

    </div>
</div>
@endsection
