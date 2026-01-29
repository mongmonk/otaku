@extends('layouts.app')

@section('title', 'A-Z List')

@section('content')
<div class="bixbox mb-6">
    <div class="releases px-4 py-3 border-b border-gray-200">
        <h1 class="text-xl font-bold text-gray-800">Anime List A-Z</h1>
    </div>
    
    <div class="p-4 bg-gray-50 border-b border-gray-200">
        <div class="flex flex-wrap justify-center gap-2">
            @php
                $letters = array_merge(['#', '0-9'], range('A', 'Z'));
            @endphp
            @foreach($letters as $l)
                <a href="{{ route('anime.az', ['show' => $l]) }}" 
                   class="min-w-[35px] h-9 flex items-center justify-center rounded border text-sm font-medium transition-colors {{ $letter === $l ? 'bg-blue-600 border-blue-600 text-white' : 'bg-white border-gray-300 text-gray-700 hover:border-blue-500 hover:text-blue-600' }}">
                    {{ $l }}
                </a>
            @endforeach
        </div>
    </div>

    <div class="listupd p-4">
        @if($animes->isEmpty())
            <div class="py-10 text-center text-gray-500">
                Tidak ada anime yang ditemukan dengan awalan "{{ $letter }}".
            </div>
        @else
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
                {{ $animes->appends(['show' => $letter])->links() }}
            </div>
        @endif
    </div>
</div>
@endsection