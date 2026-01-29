<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'OtakuStream') - Anime Streaming</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #eef0f2;
            font-family: 'Fira Sans', sans-serif;
        }
        .dark body {
            background-color: #16151d;
            color: #ccc;
        }
        .bg-primary {
            background-color: #0c70de;
        }
    </style>
</head>
<body class="antialiased">
    <div id="app">
        <x-header />
        
        <nav id="main-menu" class="bg-primary text-white mb-4">
            <div class="max-w-7xl mx-auto px-4">
                <ul class="flex space-x-4 overflow-x-auto whitespace-nowrap py-2">
                    <li><a href="{{ route('home') }}" class="hover:bg-black/20 px-3 py-2 rounded transition">Home</a></li>
                    <li><a href="{{ route('anime.index') }}" class="hover:bg-black/20 px-3 py-2 rounded transition">Anime Lists</a></li>
                    <li><a href="{{ route('anime.az') }}" class="hover:bg-black/20 px-3 py-2 rounded transition">AZ Lists</a></li>
                </ul>
            </div>
        </nav>

        <main class="max-w-7xl mx-auto px-4">
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