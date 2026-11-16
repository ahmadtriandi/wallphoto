<!DOCTYPE html>
<html lang="id">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Photo Gallery')</title>

    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>

{{-- Pusher JS --}}
<script src="https://cdn.jsdelivr.net/npm/pusher-js@8.4.0/dist/web/pusher.min.js"></script>

{{-- Laravel Echo --}}
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>

<script>
    window.EchoInstance = new Echo({
        broadcaster: 'pusher',
        key: '{{ env("REVERB_APP_KEY") }}',
        wsHost: '127.0.0.1',
        wsPort: 8081,
        wssPort: 8081,
        forceTLS: false,
        enabledTransports: ['ws', 'wss'],
        disableStats: true,
        cluster: 'mt1',
    });
</script>

{{-- Alpine TERAKHIR --}}
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        @keyframes shrink {
            from { width: 100%; }
            to   { width: 0%; }
        }
        .progress-animate {
            animation: shrink 5s linear forwards;
        }

        [x-cloak] { display: none !important; }

        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to   { transform: translateX(0);    opacity: 1; }
        }
        .popup-enter {
            animation: slideInRight 0.4s ease-out forwards;
        }

        @keyframes slideOutRight {
            from { transform: translateX(0);    opacity: 1; }
            to   { transform: translateX(100%); opacity: 0; }
        }
        .popup-leave {
            animation: slideOutRight 0.3s ease-in forwards;
        }

        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 5px; }
    </style>

    @stack('styles')
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen">

    {{-- Navbar --}}
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-40">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="/gallery" class="flex items-center gap-2 font-semibold text-gray-800">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Photo Gallery
            </a>
            {{-- <div class="flex items-center gap-4 text-sm">
                <a href="/gallery"
                   class="px-3 py-1.5 rounded-lg {{ request()->is('gallery') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-500 hover:text-gray-800' }}">
                    Gallery
                </a>
                <a href="/upload"
                   class="px-3 py-1.5 rounded-lg {{ request()->is('upload') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-500 hover:text-gray-800' }}">
                    Upload
                </a>
            </div> --}}
        </div>
    </nav>

    {{-- Konten halaman --}}
    <main class="max-w-6xl mx-auto px-4 py-6">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>