@extends('layouts.app')

@section('title', $episode->title)

@section('content')
<div class="space-y-6">
    {{-- Breadcrumb --}}
    <div class="ts-breadcrumb bg-white p-3 rounded shadow-sm text-sm">
        <a href="{{ route('home') }}" class="text-gray-600 hover:text-primary transition">Home</a>
        <span class="mx-2 text-gray-400">›</span>
        <a href="{{ route('anime.show', $episode->anime->slug) }}" class="text-gray-600 hover:text-primary transition">{{ $episode->anime->title }}</a>
        <span class="mx-2 text-gray-400">›</span>
        <span class="text-gray-800 font-medium">{{ $episode->title }}</span>
    </div>

    {{-- Player Box --}}
    <div class="bixbox bg-black rounded shadow-sm overflow-hidden border border-gray-800">
        <div class="bg-[#222] px-4 py-3 flex justify-between items-center border-b border-gray-800">
            <h1 class="text-white font-bold text-lg">{{ $episode->title }}</h1>
            <div class="flex gap-2">
                <button class="bg-gray-700 text-white px-3 py-1 rounded text-xs hover:bg-gray-600 transition">
                    <i class="far fa-lightbulb mr-1"></i> Light
                </button>
                <button class="bg-gray-700 text-white px-3 py-1 rounded text-xs hover:bg-gray-600 transition">
                    <i class="fas fa-expand mr-1"></i> Expand
                </button>
            </div>
        </div>
        
        <div class="aspect-video bg-black flex items-center justify-center relative group">
            @if($episode->streamLinks->first())
                <iframe src="{{ $episode->streamLinks->first()->url }}" class="w-full h-full" frameborder="0" allowfullscreen></iframe>
            @else
                <div class="text-gray-500 flex flex-col items-center gap-4">
                    <i class="fas fa-video-slash text-6xl"></i>
                    <p>No stream links available for this episode.</p>
                </div>
            @endif
        </div>

        <div class="bg-[#222] p-3 flex flex-wrap justify-between items-center gap-4 border-t border-gray-800">
            <div class="flex items-center gap-2">
                <span class="text-gray-400 text-xs uppercase font-bold">Mirror:</span>
                <select class="bg-gray-800 text-white border-gray-700 rounded px-3 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-primary">
                    @foreach($episode->streamLinks as $link)
                    <option value="{{ $link->url }}">{{ $link->provider }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="flex items-center gap-4">
                @php
                    $prevEp = $episode->anime->episodes->where('id', '<', $episode->id)->sortByDesc('id')->first();
                    $nextEp = $episode->anime->episodes->where('id', '>', $episode->id)->sortBy('id')->first();
                @endphp
                
                @if($prevEp)
                <a href="{{ route('episode.show', $prevEp->episode_slug) }}" class="bg-gray-800 text-white px-4 py-1.5 rounded text-sm hover:bg-primary transition">
                    <i class="fas fa-chevron-left mr-2"></i> Prev
                </a>
                @endif

                <a href="{{ route('anime.show', $episode->anime->slug) }}" class="bg-primary text-white px-4 py-1.5 rounded text-sm hover:bg-blue-700 transition">
                    <i class="fas fa-list"></i> All Episodes
                </a>

                @if($nextEp)
                <a href="{{ route('episode.show', $nextEp->episode_slug) }}" class="bg-gray-800 text-white px-4 py-1.5 rounded text-sm hover:bg-primary transition">
                    Next <i class="fas fa-chevron-right ml-2"></i>
                </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Download Links --}}
    <div class="bixbox bg-white rounded shadow-sm overflow-hidden">
        <div class="bg-gray-100 px-4 py-2 border-b border-gray-200">
            <h3 class="font-bold text-gray-700"><i class="fas fa-download mr-2 text-primary"></i> Download {{ $episode->title }}</h3>
        </div>
        <div class="p-4">
            <div class="space-y-4">
                @php
                    $groupedDownloads = $episode->downloadLinks->groupBy('resolution');
                @endphp
                @forelse($groupedDownloads as $resolution => $links)
                <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 p-3 bg-gray-50 rounded border border-gray-100">
                    <div class="flex-shrink-0 w-20">
                        <span class="bg-primary text-white px-2 py-1 rounded text-xs font-bold">{{ $resolution }}</span>
                    </div>
                    <div class="flex flex-wrap gap-x-4 gap-y-2 text-sm">
                        @foreach($links as $link)
                        <a href="{{ $link->url }}" target="_blank" class="text-blue-600 hover:text-blue-800 font-medium transition flex items-center">
                            <i class="fas fa-external-link-alt mr-1 text-[10px]"></i> {{ $link->provider }}
                        </a>
                        @endforeach
                    </div>
                </div>
                @empty
                <p class="text-center text-gray-500 text-sm italic">No download links available.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection