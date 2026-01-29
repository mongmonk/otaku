@extends('layouts.app')

@section('title', $anime->title)

@section('content')
<div class="space-y-6">
    {{-- Breadcrumb --}}
    <div class="ts-breadcrumb bg-white p-3 rounded shadow-sm text-sm">
        <a href="{{ route('home') }}" class="text-gray-600 hover:text-primary transition">Home</a>
        <span class="mx-2 text-gray-400">›</span>
        <span class="text-gray-800 font-medium">{{ $anime->title }}</span>
    </div>

    {{-- Info Box --}}
    <div class="bixbox animefull bg-white rounded shadow-sm overflow-hidden">
        <div class="p-5 flex flex-col md:flex-row gap-6">
            <div class="flex-shrink-0 w-full md:w-60">
                <div class="aspect-[3/4] rounded shadow-md overflow-hidden bg-gray-100">
                    <img src="{{ $anime->poster_url }}" alt="{{ $anime->title }}" class="w-full h-full object-cover">
                </div>
                <div class="mt-4 space-y-2">
                    <div class="bg-gray-100 p-3 rounded text-center">
                        <strong class="block text-lg">Rating {{ $anime->score }}</strong>
                        <div class="w-full bg-gray-300 h-1.5 rounded-full mt-2 overflow-hidden">
                            <div class="bg-yellow-400 h-full" style="width: {{ $anime->score * 10 }}%"></div>
                        </div>
                    </div>
                    <button class="w-full bg-primary text-white py-2 rounded font-semibold hover:bg-blue-700 transition">
                        <i class="far fa-bookmark mr-2"></i> Bookmark
                    </button>
                </div>
            </div>

            <div class="flex-grow">
                <h1 class="text-2xl font-bold text-gray-800 border-b border-gray-100 pb-3 mb-4">{{ $anime->title }}</h1>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-3 text-sm">
                    <div class="flex">
                        <span class="w-24 font-semibold text-gray-600">Status:</span>
                        <span class="text-gray-800">{{ $anime->status }}</span>
                    </div>
                    <div class="flex">
                        <span class="w-24 font-semibold text-gray-600">Studio:</span>
                        <span class="text-gray-800">{{ $anime->studio }}</span>
                    </div>
                    <div class="flex">
                        <span class="w-24 font-semibold text-gray-600">Released:</span>
                        <span class="text-gray-800">{{ $anime->release_date }}</span>
                    </div>
                    <div class="flex">
                        <span class="w-24 font-semibold text-gray-600">Duration:</span>
                        <span class="text-gray-800">{{ $anime->duration }}</span>
                    </div>
                    <div class="flex">
                        <span class="w-24 font-semibold text-gray-600">Type:</span>
                        <span class="text-gray-800">{{ $anime->type }}</span>
                    </div>
                    <div class="flex">
                        <span class="w-24 font-semibold text-gray-600">Episodes:</span>
                        <span class="text-gray-800">{{ $anime->total_episode }}</span>
                    </div>
                </div>

                <div class="mt-6">
                    <div class="flex flex-wrap gap-2">
                        @foreach($anime->genres as $genre)
                        <a href="{{ route('genre.show', $genre->slug) }}" class="px-3 py-1 border border-primary text-primary text-xs rounded hover:bg-primary hover:text-white transition">
                            {{ $genre->name }}
                        </a>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6 bg-gray-50 p-4 rounded text-sm text-gray-700 leading-relaxed italic">
                    {{ $anime->synopsis }}
                </div>
            </div>
        </div>
    </div>

    {{-- Episode List --}}
    <div class="bixbox bg-white rounded shadow-sm overflow-hidden">
        <div class="bg-gray-100 px-4 py-2 border-b border-gray-200">
            <h2 class="font-bold text-gray-700">Watch {{ $anime->title }}</h2>
        </div>
        <div class="p-4">
            <div class="overflow-y-auto max-h-96">
                <ul class="divide-y divide-gray-100">
                    @forelse($anime->episodes as $episode)
                    <li>
                        <a href="{{ route('episode.show', $episode->episode_slug) }}" class="flex items-center justify-between py-3 px-2 hover:bg-gray-50 transition group">
                            <div class="flex items-center gap-4">
                                <span class="font-bold text-primary w-8">{{ $episode->episode_number }}</span>
                                <span class="text-gray-700 font-medium group-hover:text-primary transition">{{ $episode->title }}</span>
                            </div>
                            <span class="text-xs text-gray-400">{{ $episode->uploaded_at }}</span>
                        </a>
                    </li>
                    @empty
                    <li class="py-4 text-center text-gray-500">No episodes available.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection