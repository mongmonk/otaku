import './bootstrap';

document.addEventListener('DOMContentLoaded', function() {
    // Dark Mode Logic
    const htmlElement = document.documentElement;
    const darkModeToggle = document.getElementById('dark-mode-toggle');
    const moonIcon = document.querySelector('.dark-mode-hide');
    const sunIcon = document.querySelector('.dark-mode-show');
    const currentTheme = localStorage.getItem('theme') || 'light';

    function updateIcons(isDark) {
        if (moonIcon && sunIcon) {
            if (isDark) {
                moonIcon.style.display = 'none';
                sunIcon.style.display = 'inline-block';
            } else {
                moonIcon.style.display = 'inline-block';
                sunIcon.style.display = 'none';
            }
        }
    }

    // Set initial theme
    if (currentTheme === 'dark') {
        htmlElement.classList.add('dark');
        if (darkModeToggle) darkModeToggle.checked = true;
        updateIcons(true);
    } else {
        updateIcons(false);
    }

    if (darkModeToggle) {
        darkModeToggle.addEventListener('change', function() {
            if (this.checked) {
                htmlElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
                updateIcons(true);
            } else {
                htmlElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
                updateIcons(false);
            }
        });
    }

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

    // Mobile Menu Toggle
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mainMenu = document.getElementById('main-menu');

    if (mobileMenuBtn && mainMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            mainMenu.classList.toggle('hidden');
        });
    }
});
