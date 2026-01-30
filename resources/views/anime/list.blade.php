@extends('layouts.app')

@section('title', 'Anime List')

@section('content')
<div class="bixbox">
    <div class="releases flex justify-between items-center px-4 py-3 border-b border-gray-200">
        <h1 class="text-xl font-bold text-gray-800">
            @if(isset($studio))
                Anime List Studio: {{ $studio }}
            @elseif(isset($genre))
                Anime List Genre: {{ $genre->name }}
            @elseif(isset($status) && $status === 'Completed')
                Anime List Completed
            @elseif(isset($isPopular))
                Popular Anime List
            @else
                Anime List
            @endif
        </h1>
    </div>
    
    <div class="listupd p-4">
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            @foreach($animes as $anime)
            <article class="bs relative group">
                <div class="bsx relative overflow-hidden rounded shadow-sm bg-white transition-transform duration-200 group-hover:-translate-y-1">
                    <a href="{{ route('anime.show', $anime->slug) }}" title="{{ $anime->title }}">
                        <div class="limit relative aspect-[3/4] overflow-hidden bg-gray-900">
                            @if($anime->status)
                            <div class="status absolute" style="top: 10px; left: -30px; background-color: #dc2626; color: white; font-size: 10px; padding: 2px 0; width: 100px; text-align: center; transform: rotate(-45deg); z-index: 10; text-transform: uppercase; font-weight: bold; box-shadow: 0 1px 2px rgba(0,0,0,0.1);">
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
                            <div class="bt absolute" style="bottom: 8px; left: 8px; z-index: 20;">
                                <span class="epx text-white text-[10px] font-bold px-2 py-1 rounded shadow-lg" style="background-color: #0ea5e9; display: inline-block;">{{ $anime->total_episode ?? '?' }} Episodes</span>
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