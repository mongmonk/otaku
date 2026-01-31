<header class="bg-white border-b border-gray-200 py-3">
    <div class="max-w-7xl mx-auto px-4 flex justify-between items-center">
        <div class="flex items-center gap-8">
            <a href="{{ route('home') }}" class="flex items-center">
                <img src="{{ asset('indanime.png') }}" alt="Logo" class="h-7 md:h-9">
            </a>
            <div class="hidden md:block w-80">
                <form action="{{ route('anime.search') }}" method="GET" class="relative">
                    <input type="text" name="s" placeholder="Search..." value="{{ request('s') }}" class="w-full bg-gray-100 border border-gray-300 rounded px-4 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
        </div>
        <div class="flex items-center gap-4 text-sm text-gray-600">
            <div class="flex items-center mr-2">
                <label class="theme-switch" for="dark-mode-toggle">
                    <input type="checkbox" id="dark-mode-toggle">
                    <span class="slider"></span>
                </label>
                <span class="ml-2 text-lg">
                    <i class="fas fa-moon text-gray-400 dark-mode-hide"></i>
                    <i class="fas fa-sun text-yellow-400 dark-mode-show" style="display:none;"></i>
                </span>
            </div>
            <div class="hidden lg:flex items-center gap-3">
                <a href="{{ route('anime.completed') }}" class="hover:text-blue-600 transition">Completed</a>
                <a href="{{ route('anime.latest') }}" class="hover:text-blue-600 transition">Latest</a>
                <a href="{{ route('anime.studios') }}" class="hover:text-blue-600 transition">Studio</a>
                <a href="{{ route('anime.bookmarks') }}" class="hover:text-blue-600 transition flex items-center gap-1">
                    <i class="fas fa-bookmark text-primary"></i>
                    Bookmark
                </a>
            </div>
            <button id="mobile-search-btn" class="md:hidden text-xl text-gray-600 focus:outline-none">
                <i class="fas fa-search"></i>
            </button>
            <button id="mobile-menu-btn" class="md:hidden text-2xl text-gray-600 focus:outline-none">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>
    <!-- Mobile Search Bar -->
    <div id="mobile-search-container" class="hidden md:hidden bg-white border-t border-gray-100 px-4 py-3">
        <form action="{{ route('anime.search') }}" method="GET" class="relative">
            <input type="text" name="s" placeholder="Cari anime..." value="{{ request('s') }}" class="w-full bg-gray-100 border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div>
</header>