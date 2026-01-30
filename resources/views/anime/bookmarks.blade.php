@extends('layouts.app')

@section('title', 'My Bookmarks')

@section('content')
<div class="bixbox">
    <div class="releases flex justify-between items-center px-4 py-3 border-b border-gray-200">
        <h1 class="text-xl font-bold text-gray-800">
            My Bookmarks
        </h1>
    </div>
    
    <div class="listupd p-4">
        <div id="bookmarks-container" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            <!-- Bookmarks will be loaded here via JS -->
            <div class="col-span-full py-10 text-center text-gray-500 loading-msg">
                <i class="fas fa-spinner fa-spin text-3xl mb-3"></i>
                <p>Loading bookmarks...</p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('bookmarks-container');
    const bookmarks = JSON.parse(localStorage.getItem('anime_bookmarks') || '[]');

    if (bookmarks.length === 0) {
        container.innerHTML = `
            <div class="col-span-full py-20 text-center">
                <div class="text-gray-300 mb-4">
                    <i class="far fa-bookmark text-6xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-600">Belum ada bookmark</h3>
                <p class="text-gray-400 mt-2">Anime yang kamu tandai akan muncul di sini.</p>
                <a href="{{ route('home') }}" class="inline-block mt-6 bg-primary text-white px-6 py-2 rounded-full hover:bg-blue-700 transition">Jelajahi Anime</a>
            </div>
        `;
        return;
    }

    let html = '';
    bookmarks.forEach(anime => {
        const showUrl = "{{ route('anime.show', ':slug') }}".replace(':slug', anime.slug);
        html += `
            <article class="bs relative group">
                <div class="bsx relative overflow-hidden rounded shadow-sm bg-white transition-transform duration-200 group-hover:-translate-y-1">
                    <a href="${showUrl}" title="${anime.title}">
                        <div class="limit relative aspect-[3/4] overflow-hidden bg-gray-900">
                            <img src="${anime.poster}" alt="${anime.title}" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110">
                            <div class="ply absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <i class="far fa-play-circle text-5xl text-white"></i>
                            </div>
                        </div>
                        <div class="tt p-2 text-center">
                            <h2 class="text-sm font-medium text-gray-800 line-clamp-2 leading-tight group-hover:text-blue-600 transition-colors">
                                ${anime.title}
                            </h2>
                        </div>
                    </a>
                    <button onclick="removeBookmark('${anime.id}')" class="absolute top-2 right-2 bg-black/60 hover:bg-red-600 text-white w-8 h-8 rounded flex items-center justify-center shadow-lg opacity-0 group-hover:opacity-100 transition-all duration-300 transform hover:scale-110" title="Remove Bookmark">
                        <i class="fas fa-trash-alt text-xs"></i>
                    </button>
                </div>
            </article>
        `;
    });

    container.innerHTML = html;
});

function removeBookmark(id) {
    if (confirm('Hapus dari bookmark?')) {
        let bookmarks = JSON.parse(localStorage.getItem('anime_bookmarks') || '[]');
        bookmarks = bookmarks.filter(item => item.id != id);
        localStorage.setItem('anime_bookmarks', JSON.stringify(bookmarks));
        location.reload();
    }
}
</script>
@endsection