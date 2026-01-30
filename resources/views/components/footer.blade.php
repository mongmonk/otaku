<footer class="bg-[#222] text-white mt-12">
    <div class="bg-primary py-2 text-center text-sm">
        <ul style="display: flex; flex-wrap: wrap; justify-content: center; align-items: center; padding: 0 1rem; list-style: none;">
            <li style="margin: 0.25rem 0.75rem;"><a href="{{ route('home') }}" style="color: white; text-decoration: none;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">Home</a></li>
            <li style="margin: 0.25rem 0.75rem;"><a href="{{ route('anime.index') }}" style="color: white; text-decoration: none;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">Anime Lists</a></li>
            <li style="margin: 0.25rem 0.75rem;"><a href="{{ route('anime.az') }}" style="color: white; text-decoration: none;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">AZ Lists</a></li>
            <li style="margin: 0.25rem 0.75rem;"><a href="{{ route('anime.completed') }}" style="color: white; text-decoration: none;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">Completed</a></li>
            <li style="margin: 0.25rem 0.75rem;"><a href="{{ route('anime.latest') }}" style="color: white; text-decoration: none;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">Latest</a></li>
            <li style="margin: 0.25rem 0.75rem;"><a href="{{ route('anime.studios') }}" style="color: white; text-decoration: none;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">Studio</a></li>
            <li style="margin: 0.25rem 0.75rem;"><a href="{{ route('anime.genres') }}" style="color: white; text-decoration: none;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">Genre</a></li>
            <li style="margin: 0.25rem 0.75rem;"><a href="{{ route('anime.bookmarks') }}" style="color: white; text-decoration: none;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">Bookmark</a></li>
        </ul>
    </div>
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex items-center gap-4">
                <img src="{{ asset('indanime_logo.png') }}" alt="Logo" class="h-8 md:h-12 brightness-0 invert">
                <div class="text-left">
                    <p class="text-sm font-semibold">Copyleft © {{ date('Y') }} Indanime Reborn. All Rights Reserved</p>
                    <p class="text-[10px] text-gray-400 mt-1 max-w-md">Disclaimer: This site does not store any files on its server. All contents are provided by non-affiliated third parties.</p>
                </div>
            </div>
            <div class="flex gap-4">
                {{-- Social Icons --}}
                <a href="#" class="w-8 h-8 bg-gray-700 flex items-center justify-center rounded-full hover:bg-primary transition"><i class="fab fa-facebook-f text-xs"></i></a>
                <a href="#" class="w-8 h-8 bg-gray-700 flex items-center justify-center rounded-full hover:bg-primary transition"><i class="fab fa-twitter text-xs"></i></a>
                <a href="#" class="w-8 h-8 bg-gray-700 flex items-center justify-center rounded-full hover:bg-primary transition"><i class="fab fa-instagram text-xs"></i></a>
            </div>
        </div>
    </div>
</footer>

<!-- PWA Install Banner -->
<div id="pwa-install-banner" class="fixed bottom-0 left-0 right-0 bg-gray-900 text-white p-4 shadow-2xl transform translate-y-full transition-transform duration-300 z-[9999] border-t border-primary/30" style="display: none;">
    <div class="max-w-7xl mx-auto flex items-center justify-between gap-2 md:gap-4">
        <div class="flex items-center gap-2 md:gap-3">
            <img src="{{ asset('icon_192.png') }}" alt="App Icon" class="w-10 h-10 md:w-12 md:h-12 rounded-lg shadow-md">
            <div>
                <h4 class="font-bold text-xs md:text-base">Instal IndAnime Reborn</h4>
                <p id="pwa-desc" class="text-[9px] md:text-xs text-gray-400">Nonton anime lebih cepat & ringan!</p>
            </div>
        </div>
        <div class="flex items-center gap-1 md:gap-2">
            <button id="pwa-close" class="px-2 py-1 md:px-3 md:py-1.5 text-[10px] md:text-xs text-gray-400 hover:text-white transition">Nanti</button>
            <button id="pwa-install" class="bg-primary hover:bg-blue-700 text-white px-3 py-1 md:px-4 md:py-1.5 rounded-full text-[10px] md:text-sm font-bold shadow-lg transition transform active:scale-95">Instal</button>
        </div>
    </div>
</div>

<script>
    let deferredPrompt;
    const pwaBanner = document.getElementById('pwa-install-banner');
    const installBtn = document.getElementById('pwa-install');
    const closeBtn = document.getElementById('pwa-close');
    const pwaDesc = document.getElementById('pwa-desc');

    // Check if is iOS
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;

    function showBanner() {
        if (isStandalone) return;
        
        pwaBanner.style.display = 'block';
        setTimeout(() => {
            pwaBanner.classList.remove('translate-y-full');
        }, 100);
    }

    // Android / Chrome / Edge
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        showBanner();
    });

    // iOS logic (iOS doesn't support beforeinstallprompt)
    if (isIOS && !isStandalone) {
        pwaDesc.innerText = "Klik 'Share' lalu 'Add to Home Screen'";
        installBtn.style.display = 'none'; // iOS installation is manual
        showBanner();
    }

    installBtn.addEventListener('click', async () => {
        if (!deferredPrompt) return;
        deferredPrompt.prompt();
        const { outcome } = await deferredPrompt.userChoice;
        deferredPrompt = null;
        hideBanner();
    });

    function hideBanner() {
        pwaBanner.classList.add('translate-y-full');
        setTimeout(() => {
            pwaBanner.style.display = 'none';
        }, 300);
    }

    closeBtn.addEventListener('click', hideBanner);

    window.addEventListener('appinstalled', (evt) => {
        pwaBanner.style.display = 'none';
    });
</script>

<!-- Histats.com  START  (aync)-->
<script type="text/javascript">var _Hasync= _Hasync|| [];
_Hasync.push(['Histats.start', '1,5003596,4,0,0,0,00010000']);
_Hasync.push(['Histats.fasi', '1']);
_Hasync.push(['Histats.track_hits', '']);
(function() {
var hs = document.createElement('script'); hs.type = 'text/javascript'; hs.async = true;
hs.src = ('//s10.histats.com/js15_as.js');
(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(hs);
})();</script>
<noscript><a href="/" target="_blank"><img  src="//sstatic1.histats.com/0.gif?5003596&101" alt="" border="0"></a></noscript>
<!-- Histats.com  END  -->