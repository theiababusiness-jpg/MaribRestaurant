<!DOCTYPE html>
<html lang="{{ app()->getLocale() ?? 'ar' }}">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.admin_panel') }} - {{ __('messages.site_title') }}</title>
    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="/css/variables.css">
</head>
<body dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Admin header -->
    <header style="background-color: var(--primary-color); color: white; padding: 1rem;">
        <div class="container">
            <h1>{{ __('messages.admin_panel') }}</h1>
        </div>
    </header>

    <!-- Admin navigation -->
    <nav class="container" style="margin-top: 1rem;">
        <a href="/admin">{{ __('messages.dashboard') }}</a> |
        <a href="/admin/products">{{ __('messages.products') }}</a>
    </nav>

    <!-- Main admin content -->
    <main class="container" style="margin-top: 1rem;">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer style="background-color: var(--secondary-color); color: white; padding: 1rem; margin-top: 2rem;">
        <div class="container">
            <p>&copy; {{ date('Y') }} {{ __('messages.site_title') }}.</p>
        </div>
    </footer>
    <script src="{{ asset('js/auto-translate.js') }}"></script>
    <script src="/js/admin.js"></script>
</body>
</html>