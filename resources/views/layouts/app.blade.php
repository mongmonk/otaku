<!DOCTYPE html>
<html lang="id" id="html-tag">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="index, follow">
    <meta name="description" content="@yield('meta_description', 'Nonton anime streaming sub indo gratis dengan kualitas HD. Update terbaru setiap hari di Indanime Reborn.')">
    <meta name="keywords" content="@yield('meta_keywords', 'nonton anime, streaming anime, anime sub indo, indanime reborn, download anime')">
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('title', 'Indanime Reborn') - Anime Streaming">
    <meta property="og:description" content="@yield('meta_description', 'Nonton anime streaming sub indo gratis dengan kualitas HD. Update terbaru setiap hari di Indanime Reborn.')">
    <meta property="og:image" content="@yield('og_image', asset('indanime.png'))">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="@yield('title', 'Indanime Reborn') - Anime Streaming">
    <meta property="twitter:description" content="@yield('meta_description', 'Nonton anime streaming sub indo gratis dengan kualitas HD. Update terbaru setiap hari di Indanime Reborn.')">
    <meta property="twitter:image" content="@yield('og_image', asset('indanime.png'))">

    <title>@yield('title', 'Indanime Reborn') - Anime Streaming</title>
    <link rel="icon" type="image/png" href="{{ asset('haoshokuicon.png') }}">
    
    <!-- PWA -->
    <meta name="theme-color" content="#1a202c">
    <link rel="apple-touch-icon" href="{{ asset('indanime.png') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #eef0f2;
            font-family: 'Fira Sans', sans-serif;
        }
        .bg-primary {
            background-color: #0c70de;
        }
        
        /* Dark Mode Styles */
        .dark body {
            background-color: #16151d;
            color: #ccc;
        }
        .dark header {
            background-color: #1f1e29 !important;
            border-color: #2d2b3d !important;
        }
        .dark header .text-gray-600, .dark header a {
            color: #aaa !important;
        }
        .dark header input {
            background-color: #2d2b3d !important;
            border-color: #3f3d56 !important;
            color: #fff !important;
        }
        .dark #main-menu {
            background-color: #0a58ad !important;
        }
        .dark aside .bg-white, .dark .bg-white, .dark .bixbox, .dark .ts-breadcrumb, .dark .animefull {
            background-color: #1f1e29 !important;
            color: #ccc !important;
        }
        .dark .bg-gray-100, .dark .bg-gray-50 {
            background-color: #2d2b3d !important;
            color: #ccc !important;
        }
        .dark .text-gray-600, .dark .text-gray-700, .dark .text-gray-800 {
            color: #aaa !important;
        }
        .dark .border-gray-100, .dark .border-gray-200, .dark .border-gray-300 {
            border-color: #3f3d56 !important;
        }
        .dark h1, .dark h2, .dark h3, .dark h4 {
            color: #fff !important;
        }
        
        /* Toggle Switch Styles */
        .theme-switch {
            position: relative;
            display: inline-block;
            width: 40px;
            height: 20px;
        }
        .theme-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 20px;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 2px;
            bottom: 2px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .slider {
            background-color: #0c70de;
        }
        input:checked + .slider:before {
            transform: translateX(20px);
        }

        /* Visited Link Style for Episode List */
        .bixbox ul li a:visited span {
            color: #0c70de !important;
        }
        .bixbox ul li a:visited span.text-gray-400,
        .bixbox ul li a:visited span.text-xs.text-gray-400,
        .bixbox ul li a:visited span.text-\[10px\].text-gray-400 {
            color: #9ca3af !important; /* Kembalikan warna abu-abu untuk info tanggal/waktu */
        }
    </style>
    <script>
        // Immediate script to prevent flash of unstyled content
        (function() {
            const theme = localStorage.getItem('theme') || 'light';
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
            }
        })();

        document.addEventListener('DOMContentLoaded', function() {
            const toggle = document.getElementById('dark-mode-toggle');
            const html = document.documentElement;
            const moonIcon = document.querySelector('.dark-mode-hide');
            const sunIcon = document.querySelector('.dark-mode-show');

            function updateUI(isDark) {
                if (isDark) {
                    html.classList.add('dark');
                    if (moonIcon) moonIcon.style.display = 'none';
                    if (sunIcon) sunIcon.style.display = 'inline-block';
                    if (toggle) toggle.checked = true;
                } else {
                    html.classList.remove('dark');
                    if (moonIcon) moonIcon.style.display = 'inline-block';
                    if (sunIcon) sunIcon.style.display = 'none';
                    if (toggle) toggle.checked = false;
                }
            }

            // Sync UI on load
            updateUI(html.classList.contains('dark'));

            if (toggle) {
                toggle.addEventListener('change', function() {
                    const isDark = this.checked;
                    updateUI(isDark);
                    localStorage.setItem('theme', isDark ? 'dark' : 'light');
                });
            }
        });
    </script>
    @yield('schema')
</head>
<body class="antialiased">
    <div id="app">
        <x-header />
        
        <nav id="main-menu" class="hidden md:block bg-primary text-white mb-4">
            <div class="max-w-7xl mx-auto md:px-4">
                <ul class="flex flex-col md:flex-row md:space-x-4 overflow-x-auto whitespace-nowrap py-2">
                    <li><a href="{{ route('home') }}" class="hover:bg-black/20 px-3 py-2 rounded transition">Home</a></li>
                    <li><a href="{{ route('anime.index') }}" class="hover:bg-black/20 px-3 py-2 rounded transition">Anime Lists</a></li>
                    <li><a href="{{ route('anime.az') }}" class="hover:bg-black/20 px-3 py-2 rounded transition">AZ Lists</a></li>
                    <li><a href="{{ route('anime.genres') }}" class="hover:bg-black/20 px-3 py-2 rounded transition">Genre</a></li>
                </ul>
            </div>
        </nav>

        <main class="max-w-7xl mx-auto md:px-4">
            <div class="flex flex-col md:flex-row gap-6">
                <div class="w-full md:w-2/3 lg:w-3/4">
                    @yield('content')
                </div>
                <aside class="w-full md:w-1/3 lg:w-1/4">
                    <x-sidebar />
                </aside>
            </div>
        </main>

        <x-footer />
    </div>
</body>
</html>