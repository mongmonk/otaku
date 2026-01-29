@extends('layouts.app')

@section('title', 'Home')

@section('content')
<div class="space-y-8">
    {{-- Hot/Latest Section --}}
    <section class="bg-white rounded shadow-sm overflow-hidden">
        <div class="bg-primary text-white px-4 py-2 flex justify-between items-baseline">
            <h2 class="font-bold uppercase text-sm tracking-wider">LATEST ANIME</h2>
            <a href="#" class="text-[10px] bg-white text-gray-800 px-2 py-0.5 rounded font-bold hover:bg-gray-200 transition">VIEW ALL</a>
        </div>
        <div class="p-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            @foreach($latestAnimes as $anime)
            <article class="group relative bg-gray-900 rounded overflow-hidden shadow-md transition-transform hover:-translate-y-1">
                <a href="{{ route('anime.show', $anime->slug) }}">
                    <div class="aspect-[3/4] relative">
                        <img src="{{ $anime->poster_url }}" alt="{{ $anime->title }}" class="w-full h-full object-cover">
                        <div class="absolute top-0 right-0 bg-red-600 text-white text-[10px] px-2 py-1 rounded-bl font-bold shadow-sm">
                            {{ $anime->status }}
                        </div>
                        <div class="absolute bottom-0 left-0 w-full p-3 bg-gradient-to-t from-black via-black/60 to-transparent">
                            <h3 class="text-white text-xs font-bold text-center leading-tight line-clamp-2 group-hover:text-primary transition">
                                {{ $anime->title }}
                            </h3>
                        </div>
                        <div class="absolute inset-0 bg-primary/20 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                            <i class="fas fa-play-circle text-white text-4xl"></i>
                        </div>
                    </div>
                </a>
            </article>
            @endforeach
        </div>
    </section>

    {{-- Popular Section --}}
    <section class="bg-white rounded shadow-sm overflow-hidden">
        <div class="bg-[#694ba1] text-white px-4 py-2 flex justify-between items-baseline">
            <h2 class="font-bold uppercase text-sm tracking-wider">POPULAR ANIME</h2>
            <a href="#" class="text-[10px] bg-white text-gray-800 px-2 py-0.5 rounded font-bold hover:bg-gray-200 transition">VIEW ALL</a>
        </div>
        <div class="p-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            @foreach($popularAnimes as $anime)
            <article class="group relative bg-gray-900 rounded overflow-hidden shadow-md transition-transform hover:-translate-y-1">
                <a href="{{ route('anime.show', $anime->slug) }}">
                    <div class="aspect-[3/4] relative">
                        <img src="{{ $anime->poster_url }}" alt="{{ $anime->title }}" class="w-full h-full object-cover">
                        <div class="absolute top-2 left-2 w-6 h-6 bg-red-600 text-white flex items-center justify-center rounded-full text-[10px] font-bold">
                            <i class="fas fa-fire"></i>
                        </div>
                        <div class="absolute bottom-0 left-0 w-full p-3 bg-gradient-to-t from-black via-black/60 to-transparent text-center">
                            <h3 class="text-white text-xs font-bold leading-tight line-clamp-2">
                                {{ $anime->title }}
                            </h3>
                            <div class="flex items-center justify-center gap-1 mt-1">
                                <i class="fas fa-star text-yellow-400 text-[10px]"></i>
                                <span class="text-white text-[10px] font-bold">{{ $anime->score }}</span>
                            </div>
                        </div>
                    </div>
                </a>
            </article>
            @endforeach
        </div>
    </section>
</div>
@endsection