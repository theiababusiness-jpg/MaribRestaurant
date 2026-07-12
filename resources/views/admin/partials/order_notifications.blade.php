<div
    data-admin-order-notifications="1"
    data-check-url="{{ $adminCheckNewUrl }}"
    data-orders-url="{{ $adminOrdersUrl }}"
    data-last-id="{{ $adminLatestOrderId }}"
    data-poll-ms="10000"
>
    <audio id="adminOrderNotificationAudio" preload="auto">
        <source src="{{ asset('sounds/notification.mp3') }}" type="audio/mpeg">
    </audio>

    <div id="adminOrderNotificationToast" class="admin-order-toast" hidden>
        <div class="admin-order-toast__content">
            <strong id="adminOrderNotificationTitle">وصل طلب جديد</strong>
            <span id="adminOrderNotificationText">يوجد طلب جديد يحتاج المراجعة.</span>
        </div>
        <div class="admin-order-toast__actions">
            <a class="btn btn--small" href="{{ $adminOrdersUrl }}">الطلبات</a>
            <button class="btn btn--small btn--ghost" type="button" id="adminOrderNotificationDismiss">إخفاء</button>
        </div>
    </div>

    <button type="button" id="adminOrderNotificationEnable" class="admin-order-sound-toggle" hidden>
        تفعيل صوت الطلبات الجديدة
    </button>
</div>

<style>
.admin-order-toast{
    position:fixed;
    inset-inline-end:20px;
    bottom:20px;
    width:min(360px, calc(100vw - 28px));
    padding:14px;
    border-radius:16px;
    background:#fff;
    border:1px solid rgba(0,0,0,.08);
    box-shadow:0 14px 32px rgba(0,0,0,.18);
    z-index:9998;
    display:grid;
    gap:12px;
}

.admin-order-toast[hidden],
.admin-order-sound-toggle[hidden]{
    display:none !important;
}

.admin-order-toast__content{
    display:grid;
    gap:6px;
}

.admin-order-toast__content strong{
    font-size:16px;
}

.admin-order-toast__content span{
    opacity:.8;
    line-height:1.5;
}

.admin-order-toast__actions{
    display:flex;
    gap:8px;
    flex-wrap:wrap;
}

.admin-order-sound-toggle{
    position:fixed;
    inset-inline-end:20px;
    bottom:20px;
    z-index:9997;
    border:0;
    border-radius:999px;
    padding:12px 16px;
    background:var(--secondary-color);
    color:#fff;
    font-weight:800;
    box-shadow:0 12px 28px rgba(0,0,0,.18);
    cursor:pointer;
}

@media (max-width: 768px){
    .admin-order-toast,
    .admin-order-sound-toggle{
        inset-inline:14px;
        width:auto;
    }
}
</style>

<script src="{{ asset('js/admin-order-notifications.js') }}?v={{ @filemtime(public_path('js/admin-order-notifications.js')) }}"></script>
