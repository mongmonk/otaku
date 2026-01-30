import './bootstrap';

document.addEventListener('DOMContentLoaded', function() {
    const bookmarkBtn = document.getElementById('bookmark-btn');
    if (bookmarkBtn) {
        const animeId = bookmarkBtn.dataset.id;
        const animeData = {
            id: animeId,
            title: bookmarkBtn.dataset.title,
            poster: bookmarkBtn.dataset.poster,
            slug: bookmarkBtn.dataset.slug
        };

        // Check if already bookmarked
        let bookmarks = JSON.parse(localStorage.getItem('anime_bookmarks') || '[]');
        const isBookmarked = bookmarks.some(item => item.id == animeId);

        if (isBookmarked) {
            updateBookmarkBtn(true);
        }

        bookmarkBtn.addEventListener('click', function() {
            bookmarks = JSON.parse(localStorage.getItem('anime_bookmarks') || '[]');
            const index = bookmarks.findIndex(item => item.id == animeId);

            if (index === -1) {
                // Add to bookmarks
                bookmarks.push(animeData);
                localStorage.setItem('anime_bookmarks', JSON.stringify(bookmarks));
                updateBookmarkBtn(true);
                alert('Berhasil ditambahkan ke bookmark!');
            } else {
                // Remove from bookmarks
                bookmarks.splice(index, 1);
                localStorage.setItem('anime_bookmarks', JSON.stringify(bookmarks));
                updateBookmarkBtn(false);
                alert('Dihapus dari bookmark!');
            }
        });

        function updateBookmarkBtn(active) {
            const icon = bookmarkBtn.querySelector('i');
            const text = bookmarkBtn.querySelector('span');
            if (active) {
                bookmarkBtn.classList.remove('bg-primary', 'hover:bg-blue-700');
                bookmarkBtn.classList.add('bg-red-600', 'hover:bg-red-700');
                icon.classList.remove('far');
                icon.classList.add('fas');
                text.textContent = 'Remove Bookmark';
            } else {
                bookmarkBtn.classList.remove('bg-red-600', 'hover:bg-red-700');
                bookmarkBtn.classList.add('bg-primary', 'hover:bg-blue-700');
                icon.classList.remove('fas');
                icon.classList.add('far');
                text.textContent = 'Bookmark';
            }
        }
    }
});
