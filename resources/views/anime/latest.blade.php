@extends('layouts.app')

@section('title', 'Latest Episodes')

@section('content')
<div class="bixbox">
    <div class="releases flex justify-between items-center px-4 py-3 border-b border-gray-200">
        <h1 class="text-xl font-bold text-gray-800">Latest Episodes</h1>
    </div>
    
    <div class="listupd p-4">
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            @foreach($episodes as $episode)
            <article class="bs relative group">
                <div class="bsx relative overflow-hidden rounded shadow-sm bg-white transition-transform duration-200 group-hover:-translate-y-1">
                    <a href="{{ route('episode.show', $episode->episode_slug) }}" title="{{ $episode->anime->title }} Episode {{ $episode->episode_number }}">
                        <div class="limit relative aspect-[3/4] overflow-hidden bg-gray-900">
                            <img src="{{ $episode->anime->poster_url }}" alt="{{ $episode->anime->title }}" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110">
                            
                            {{-- Top Badges --}}
                            <div class="absolute flex gap-1" style="z-index: 50; top: 8px; left: 8px;">
                                <span class="text-white text-[10px] font-bold px-1.5 py-0.5 rounded shadow-lg" style="background-color: #e11d48; display: inline-block;">{{ $episode->anime->type ?? 'TV' }}</span>
                                @php
                                    $year = null;
                                    if ($episode->anime->release_date) {
                                        if (preg_match('/\d{4}/', $episode->anime->release_date, $matches)) {
                                            $year = $matches[0];
                                        }
                                    }
                                @endphp
                                @if($year)
                                <span class="text-white text-[10px] font-bold px-1.5 py-0.5 rounded shadow-lg" style="background-color: #0891b2; display: inline-block;">{{ $year }}</span>
                                @endif
                                <span class="text-white text-[10px] font-bold px-1.5 py-0.5 rounded shadow-lg" style="background-color: #ca8a04; display: inline-block;">Eps {{ $episode->episode_number }}</span>
                            </div>

                            <div class="ply absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity" style="z-index: 10;">
                                <i class="far fa-play-circle text-5xl text-white"></i>
                            </div>

                            {{-- Bottom Badges --}}
                            <div class="absolute flex gap-1" style="z-index: 100; bottom: 8px; right: 8px; display: flex; flex-direction: row;">
                                @if(isset($episode->anime->status) && $episode->anime->status !== '')
                                <span class="text-white text-[10px] font-bold px-1.5 py-0.5 rounded shadow-lg" style="background-color: #10b981; display: block; white-space: nowrap;">{{ $episode->anime->status }}</span>
                                @endif
                                @if(isset($episode->anime->score) && $episode->anime->score > 0)
                                <span class="text-white text-[10px] font-bold px-1.5 py-0.5 rounded shadow-lg" style="background-color: #f59e0b; display: block; white-space: nowrap;">{{ number_format($episode->anime->score, 2) }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="tt p-2 text-center">
                            <h2 class="text-sm font-medium text-gray-800 line-clamp-2 leading-tight group-hover:text-blue-600 transition-colors">
                                {{ $episode->anime->title }}
                            </h2>
                        </div>
                    </a>
                </div>
            </article>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $episodes->links() }}
        </div>
    </div>
</div>
@endsection