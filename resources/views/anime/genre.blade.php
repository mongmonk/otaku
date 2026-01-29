@extends('layouts.app')

@section('title', 'Genre: ' . $genre->name)

@section('content')
<div class="space-y-6">
    {{-- Breadcrumb --}}
    <div class="ts-breadcrumb bg-white p-3 rounded shadow-sm text-sm">
        <a href="{{ route('home') }}" class="text-gray-600 hover:text-primary transition">Home</a>
        <span class="mx-2 text-gray-400">›</span>
        <span class="text-gray-800 font-medium">Genre</span>
        <span class="mx-2 text-gray-400">›</span>
        <span class="text-gray-800 font-medium">{{ $genre->name }}</span>
    </div>

    {{-- Genre Header --}}
    <div class="bg-white rounded shadow-sm overflow-hidden">
        <div class="bg-primary text-white px-4 py-2">
            <h1 class="font-bold uppercase text-sm tracking-wider">GENRE: {{ $genre->name }}</h1>
        </div>
        <div class="p-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            @forelse($animes as $anime)
            <article class="group relative bg-gray-900 rounded overflow-hidden shadow-md transition-transform hover:-translate-y-1">
                <a href="{{ route('anime.show', $anime->slug) }}">
                    <div class="aspect-[3/4] relative">
                        <img src="{{ $anime->poster_url }}" alt="{{ $anime->title }}" class="w-full h-full object-cover">
                        <div class="absolute bottom-0 left-0 w-full p-3 bg-gradient-to-t from-black via-black/60 to-transparent">
                            <h3 class="text-white text-xs font-bold text-center leading-tight line-clamp-2">
                                {{ $anime->title }}
                            </h3>
                        </div>
                    </div>
                </a>
            </article>
            @empty
            <div class="col-span-full py-12 text-center text-gray-500">
                <i class="fas fa-folder-open text-4xl mb-4 block"></i>
                <p>No anime found in this genre.</p>
            </div>
            @endforelse
        </div>
        
        @if($animes->hasPages())
        <div class="p-4 border-t border-gray-100 flex justify-center">
            {{ $animes->links() }}
        </div>
        @endif
    </div>
</div>
@endsection