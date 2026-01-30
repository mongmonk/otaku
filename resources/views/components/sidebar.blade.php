<div class="space-y-6">
    <div class="bg-white rounded shadow-sm overflow-hidden">
        <div class="bg-gray-100 px-4 py-2 border-b border-gray-200">
            <h3 class="font-bold text-gray-700">Filter Search</h3>
        </div>
        <div class="p-4">
            <form action="{{ route('anime.search') }}" method="GET" class="space-y-3">
                <div>
                    @php
                        $genres = \App\Models\Genre::orderBy('name')->get();
                    @endphp
                    <select name="genre" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary">
                        <option value="">Select Genre</option>
                        @foreach($genres as $genre)
                            <option value="{{ $genre->slug }}" {{ request('genre') == $genre->slug ? 'selected' : '' }}>{{ $genre->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select name="status" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary">
                        <option value="">Status</option>
                        <option value="Ongoing" {{ request('status') == 'Ongoing' ? 'selected' : '' }}>Ongoing</option>
                        <option value="Completed" {{ request('status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
                <button type="submit" class="w-full bg-primary text-white py-1.5 rounded text-sm font-semibold hover:bg-blue-700 transition">
                    Search
                </button>
            </form>
        </div>
    </div>

    <div class="bg-white rounded shadow-sm overflow-hidden">
        <div class="bg-gray-100 px-4 py-2 border-b border-gray-200">
            <h3 class="font-bold text-gray-700">Popular</h3>
        </div>
        <div class="p-4">
            <ul class="space-y-4">
                {{-- Popular items placeholder --}}
                @php
                    $popularAnimes = \App\Models\Anime::orderBy('score', 'desc')->take(5)->get();
                @endphp
                @foreach($popularAnimes as $index => $anime)
                <li class="flex gap-3">
                    <div class="flex-shrink-0 w-8 h-8 bg-white border border-primary text-primary flex items-center justify-center rounded font-bold">
                        {{ $index + 1 }}
                    </div>
                    <div class="flex-shrink-0 w-12 h-16 bg-gray-200 rounded overflow-hidden">
                        <img src="{{ $anime->poster_url }}" alt="{{ $anime->title }}" class="w-full h-full object-cover">
                    </div>
                    <div class="min-w-0">
                        <h4 class="text-sm font-semibold text-gray-800 truncate">
                            <a href="{{ route('anime.show', $anime->slug) }}" class="hover:text-primary transition">{{ $anime->title }}</a>
                        </h4>
                        <div class="flex items-center gap-1 mt-1">
                            <div class="flex text-yellow-400 text-xs">
                                <i class="fas fa-star"></i>
                            </div>
                            <span class="text-xs text-gray-500">{{ $anime->score }}</span>
                        </div>
                    </div>
                </li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="bg-white rounded shadow-sm overflow-hidden">
        <div class="bg-gray-100 px-4 py-2 border-b border-gray-200">
            <h3 class="font-bold text-gray-700">Ongoing</h3>
        </div>
        <div class="p-2">
            <ul class="divide-y divide-gray-100">
                @php
                    $ongoingEpisodes = \App\Models\Episode::with('anime')
                        ->whereHas('anime', function($q) { $q->where('status', 'Ongoing'); })
                        ->latest('id')
                        ->take(5)
                        ->get();
                @endphp
                @foreach($ongoingEpisodes as $episode)
                <li>
                    <a href="{{ route('episode.show', $episode->episode_slug) }}" class="flex justify-between items-center px-2 py-2 hover:bg-gray-50 transition text-sm">
                        <span class="truncate text-gray-700"><i class="fas fa-angle-right mr-1 text-primary"></i> {{ $episode->anime->title }}</span>
                        <span class="flex-shrink-0 bg-primary text-white text-[10px] px-1.5 py-0.5 rounded ml-2">Ep {{ $episode->episode_number }}</span>
                    </a>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>