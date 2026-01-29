@extends('layouts.app')

@section('title', 'Anime List')

@section('content')
<div class="bixbox">
    <div class="releases flex justify-between items-center px-4 py-3 border-b border-gray-200">
        <h1 class="text-xl font-bold text-gray-800">Anime List</h1>
    </div>
    
    <div class="listupd p-4">
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            @foreach($animes as $anime)
            <article class="bs relative group">
                <div class="bsx relative overflow-hidden rounded shadow-sm bg-white transition-transform duration-200 group-hover:-translate-y-1">
                    <a href="{{ route('anime.show', $anime->slug) }}" title="{{ $anime->title }}">
                        <div class="limit relative aspect-[3/4] overflow-hidden bg-gray-900">
                            @if($anime->status)
                            <div class="status absolute top-2 left-[-30px] bg-red-600 text-white text-[10px] py-0.5 w-[100px] text-center rotate-[-45deg] z-10 uppercase font-bold">
                                {{ $anime->status }}
                            </div>
                            @endif
                            <div class="typez absolute top-2 right-2 bg-black/80 text-white text-[10px] px-2 py-0.5 rounded z-10">
                                {{ $anime->type }}
                            </div>
                            <img src="{{ $anime->poster_url }}" alt="{{ $anime->title }}" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110">
                            <div class="ply absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <i class="far fa-play-circle text-5xl text-white"></i>
                            </div>
                            <div class="bt absolute bottom-0 left-0 right-0 p-2 bg-gradient-to-t from-black/90 to-transparent">
                                <span class="epx text-white text-xs block truncate">{{ $anime->total_episode ?? '?' }} Episodes</span>
                            </div>
                        </div>
                        <div class="tt p-2 text-center">
                            <h2 class="text-sm font-medium text-gray-800 line-clamp-2 leading-tight group-hover:text-blue-600 transition-colors">
                                {{ $anime->title }}
                            </h2>
                        </div>
                    </a>
                </div>
            </article>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $animes->links() }}
        </div>
    </div>
</div>
@endsection