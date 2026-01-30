@extends('layouts.app')

@section('title', 'Genres')

@section('content')
<div class="bixbox">
    <div class="releases flex justify-between items-center px-4 py-3 border-b border-gray-200">
        <h1 class="text-xl font-bold text-gray-800">Genres</h1>
    </div>
    
    <div class="p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($genres as $genre)
            <a href="{{ route('genre.show', $genre->slug) }}" class="flex justify-between items-center bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded px-4 py-3 transition-colors group">
                <span class="text-sm font-medium text-gray-700 group-hover:text-primary truncate">{{ $genre->name }}</span>
                <span class="bg-gray-200 text-gray-500 text-[10px] font-bold px-2 py-0.5 rounded ml-2">{{ $genre->animes_count }}</span>
            </a>
            @endforeach
        </div>
    </div>
</div>
@endsection