<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تحديد اللغة
if (isset($_GET['lang']) && in_array($_GET['lang'], ['ar', 'en'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

// اللغة الافتراضية
$lang = $_SESSION['lang'] ?? 'ar';

// تحميل ملف اللغة
require_once __DIR__ . '/../app/lang/' . $lang . '.php';

// دالة الترجمة
function t($key) {
    global $langArr;
    return $langArr[$key] ?? $key;
}
