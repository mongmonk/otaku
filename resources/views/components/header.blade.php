<header class="bg-white border-b border-gray-200 py-3">
    <div class="max-w-7xl mx-auto px-4 flex justify-between items-center">
        <div class="flex items-center gap-8">
            <a href="{{ route('home') }}" class="flex items-center">
                <img src="https://animestream.themesia.com/wp-content/uploads/2023/01/animestream-dark.png" alt="Logo" class="h-9">
            </a>
            <div class="hidden md:block w-80">
                <form action="#" method="GET" class="relative">
                    <input type="text" name="s" placeholder="Search..." class="w-full bg-gray-100 border border-gray-300 rounded px-4 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
        </div>
        <div class="flex items-center gap-4 text-sm text-gray-600">
            <div class="hidden lg:flex items-center gap-3">
                <a href="#" class="hover:text-blue-600 transition">Season</a>
                <a href="{{ route('home') }}" class="hover:text-blue-600 transition">Latest</a>
                <a href="#" class="hover:text-blue-600 transition">Studio</a>
            </div>
            <button class="md:hidden text-2xl text-gray-600">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>
</header>