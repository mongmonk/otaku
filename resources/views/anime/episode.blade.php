@extends('layouts.app')

@section('title', 'Nonton ' . $episode->title . ' Subtitle Indonesia')
@section('meta_description', 'Nonton streaming anime ' . $episode->title . ' Sub Indo gratis dengan kualitas HD. Download ' . $episode->title . ' terbaru di Indanime Reborn.')
@section('meta_keywords', $episode->title . ' sub indo, nonton ' . $episode->title . ', streaming ' . $episode->title . ', download ' . $episode->title)
@section('og_image', $episode->anime->poster_url)
@section('og_type', 'video.episode')

@section('schema')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "Episode",
  "name": "{{ $episode->title }}",
  "episodeNumber": "{{ $episode->episode_number }}",
  "description": "Nonton anime {{ $episode->title }} Subtitle Indonesia gratis di Indanime Reborn.",
  "image": "{{ $episode->anime->poster_url }}",
  "partOfSeries": {
    "@@type": "TVSeries",
    "name": "{{ $episode->anime->title }}"
  }
}
</script>
@endsection

@section('content')
<div class="space-y-4 md:space-y-6">
    {{-- Breadcrumb --}}
    <div class="ts-breadcrumb bg-white p-2 md:p-3 rounded shadow-sm text-[10px] md:text-sm">
        <a href="{{ route('home') }}" class="text-gray-600 hover:text-primary transition">Home</a>
        <span class="mx-1 md:mx-2 text-gray-400">›</span>
        <a href="{{ route('anime.show', $episode->anime->slug) }}" class="text-gray-600 hover:text-primary transition">{{ $episode->anime->title }}</a>
        <span class="mx-1 md:mx-2 text-gray-400">›</span>
        <span class="text-gray-800 font-medium line-clamp-1 inline">{{ $episode->title }}</span>
    </div>

    {{-- Mobile Title (Visible only on mobile) --}}
    <div class="md:hidden">
        <h1 class="text-gray-800 font-bold text-lg leading-tight px-2">{{ $episode->title }}</h1>
    </div>

    {{-- Player Box --}}
    <div id="player-container" class="bixbox bg-black md:rounded shadow-sm overflow-hidden md:border border-gray-800 transition-all duration-300 -mx-4 md:mx-0" style="width: auto; max-width: none;">
        <div class="bg-[#222] px-3 py-2 md:px-4 md:py-3 hidden md:flex md:justify-between md:items-center gap-2 border-b border-gray-800">
            <h1 class="text-white font-bold text-sm md:text-lg order-2 md:order-1">{{ $episode->title }}</h1>
            <div class="flex justify-end gap-2 order-1 md:order-2">
                <button id="light-btn" onclick="toggleLight()" class="bg-gray-700 text-white px-2 py-1 rounded text-[10px] md:text-xs hover:bg-gray-600 transition">
                    <i class="far fa-lightbulb"></i> <span class="hidden md:inline ml-1">Turn Off Light</span>
                </button>
                <button onclick="toggleExpand()" class="bg-gray-700 text-white px-2 py-1 rounded text-[10px] md:text-xs hover:bg-gray-600 transition">
                    <i class="fas fa-expand"></i> <span class="hidden md:inline ml-1">Expand</span>
                </button>
            </div>
        </div>
        
        <div class="aspect-video bg-black flex items-center justify-center relative group">
            @if($episode->streamLinks->first())
                <iframe id="video-player" src="{{ $episode->streamLinks->first()->url }}" class="w-full h-full" frameborder="0" allowfullscreen></iframe>
            @else
                <div class="text-gray-500 flex flex-col items-center gap-2 md:gap-4">
                    <i class="fas fa-video-slash text-4xl md:text-6xl"></i>
                    <p class="text-xs md:text-base">No stream links available for this episode.</p>
                </div>
            @endif
        </div>

        <div class="bg-[#1a1a1a] p-2 md:p-3 flex flex-col md:flex-row justify-between items-center gap-3 border-t border-gray-800">
            <div class="flex items-center justify-between md:justify-start gap-2 w-full md:w-auto px-2 md:px-0">
                <div class="flex items-center gap-2 flex-1 md:flex-none">
                    <span class="text-gray-400 text-[10px] md:text-xs uppercase font-bold whitespace-nowrap">Mirror:</span>
                    <select id="mirror-select" onchange="document.getElementById('video-player').src = this.value" class="bg-gray-800 text-white border-gray-700 rounded px-2 py-1 text-xs md:text-sm focus:outline-none focus:ring-1 focus:ring-primary flex-1 md:flex-none">
                        @foreach($episode->streamLinks as $link)
                        <option value="{{ $link->url }}">{{ $link->provider }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-3 md:hidden">
                    <i class="fas fa-lightbulb text-yellow-400 text-sm" onclick="toggleLight()"></i>
                    <i class="fas fa-expand text-gray-400 text-sm" onclick="toggleExpand()"></i>
                </div>
            </div>
            
            <div class="flex items-center justify-center gap-2 md:gap-4 w-full md:w-auto">
                @php
                    $prevEp = $episode->anime->episodes->where('id', '>', $episode->id)->sortBy('id')->first();
                    $nextEp = $episode->anime->episodes->where('id', '<', $episode->id)->sortByDesc('id')->first();
                @endphp
                
                @if($prevEp)
                <a href="{{ route('episode.show', $prevEp->anime->slug . '-episode-' . $prevEp->episode_number) }}" class="bg-gray-800 text-white px-3 py-1.5 rounded text-[10px] md:text-sm hover:bg-primary transition flex-1 md:flex-none text-center">
                    <i class="fas fa-chevron-left md:mr-2"></i> <span class="hidden md:inline">Prev</span>
                </a>
                @endif

                <a href="{{ route('anime.show', $episode->anime->slug) }}" class="bg-primary text-white px-3 py-1.5 rounded text-[10px] md:text-sm hover:bg-blue-700 transition flex-1 md:flex-none text-center">
                    <i class="fas fa-list md:mr-2"></i> <span class="hidden md:inline">All Episodes</span>
                </a>

                @if($nextEp)
                <a href="{{ route('episode.show', $nextEp->anime->slug . '-episode-' . $nextEp->episode_number) }}" class="bg-gray-800 text-white px-3 py-1.5 rounded text-[10px] md:text-sm hover:bg-primary transition flex-1 md:flex-none text-center">
                    <span class="hidden md:inline">Next</span> <i class="fas fa-chevron-right md:ml-2"></i>
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

    {{-- Anime Info --}}
    <div class="bixbox bg-white rounded shadow-sm overflow-hidden">
        <div class="p-4 md:p-5 flex flex-col sm:flex-row gap-6">
            <div class="flex-shrink-0 sm:w-48">
                <div class="aspect-[3/4] rounded shadow-md overflow-hidden bg-gray-100">
                    <img src="{{ $episode->anime->poster_url }}" alt="{{ $episode->anime->title }}" class="w-full h-full object-cover">
                </div>
                <div class="mt-3">
                    <div class="bg-gray-50 p-2 rounded text-center border border-gray-100">
                        <strong class="block text-sm md:text-base text-gray-800">Rating {{ $episode->anime->score }}</strong>
                        <div class="w-full bg-gray-200 h-1.5 rounded-full mt-1.5 overflow-hidden">
                            <div class="bg-yellow-400 h-full" style="width: {{ $episode->anime->score * 10 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex-grow min-w-0">
                <h2 class="text-2xl font-bold text-gray-800 border-b border-gray-100 pb-3 mb-4">{{ $episode->anime->title }}</h2>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-3 text-sm mb-6">
                    <div class="flex items-center">
                        <span class="w-24 font-semibold text-gray-600">Status:</span>
                        <span class="text-gray-800">{{ $episode->anime->status }}</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-24 font-semibold text-gray-600">Studio:</span>
                        <span class="text-gray-800">{{ $episode->anime->studio }}</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-24 font-semibold text-gray-600">Released:</span>
                        <span class="text-gray-800">{{ $episode->anime->release_date }}</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-24 font-semibold text-gray-600">Duration:</span>
                        <span class="text-gray-800">{{ $episode->anime->duration }}</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-24 font-semibold text-gray-600">Type:</span>
                        <span class="text-gray-800">{{ $episode->anime->type }}</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-24 font-semibold text-gray-600">Episodes:</span>
                        <span class="text-gray-800">{{ $episode->anime->total_episode }}</span>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 mb-6">
                    @foreach($episode->anime->genres as $genre)
                    <a href="{{ route('genre.show', $genre->slug) }}" class="px-3 py-1 border border-gray-800 text-gray-800 text-xs rounded hover:bg-gray-800 hover:text-white transition">
                        {{ $genre->name }}
                    </a>
                    @endforeach
                </div>

                <div class="bg-gray-50 p-4 rounded text-sm text-gray-700 leading-relaxed italic border-l-4 border-gray-200">
                    {{ $episode->anime->synopsis }}
                </div>
            </div>
        </div>
    </div>

    {{-- Episode List --}}
    <div class="bixbox bg-white rounded shadow-sm overflow-hidden">
        <div class="bg-gray-100 px-4 py-2 border-b border-gray-200">
            <h3 class="font-bold text-gray-700"><i class="fas fa-list mr-2 text-primary"></i> Watch {{ $episode->anime->title }}</h3>
        </div>
        <div class="p-2 md:p-4">
            <div class="overflow-y-auto max-h-72 custom-scrollbar">
                <ul class="divide-y divide-gray-100">
                    @foreach($episode->anime->episodes->sortByDesc('episode_number') as $ep)
                    <li>
                        <a href="{{ route('episode.show', $episode->anime->slug . '-episode-' . $ep->episode_number) }}" class="flex items-center justify-between py-2 px-2 hover:bg-gray-50 transition group {{ $ep->id == $episode->id ? 'bg-blue-50' : '' }}">
                            <div class="flex items-center gap-3">
                                <span class="font-bold {{ $ep->id == $episode->id ? 'text-primary' : 'text-gray-400' }} w-6 text-xs md:text-sm text-center">{{ $ep->episode_number }}</span>
                                <span class="text-xs md:text-sm {{ $ep->id == $episode->id ? 'text-primary font-bold' : 'text-gray-700 font-medium' }} group-hover:text-primary transition line-clamp-1">{{ $ep->title }}</span>
                            </div>
                            <span class="text-[10px] text-gray-400 whitespace-nowrap ml-2">{{ $ep->uploaded_at }}</span>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>

<div id="light-overlay" onclick="toggleLight()" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.9); z-index: 9998; display: none; transition: opacity 0.3s;"></div>

<script>
    function toggleLight() {
        const overlay = document.getElementById('light-overlay');
        const container = document.getElementById('player-container');
        const btn = document.getElementById('light-btn');
        const span = btn.querySelector('span');
        const icon = btn.querySelector('i');
        
        if (overlay.style.display === 'none') {
            overlay.style.display = 'block';
            container.style.position = 'relative';
            container.style.zIndex = '9999';
            span.innerText = 'Turn On Light';
            icon.className = 'fas fa-lightbulb mr-1 text-yellow-400';
        } else {
            overlay.style.display = 'none';
            container.style.position = '';
            container.style.zIndex = '';
            span.innerText = 'Turn Off Light';
            icon.className = 'far fa-lightbulb mr-1';
        }
    }

    function toggleExpand() {
        const container = document.getElementById('player-container');
        const mainContent = document.querySelector('main > div > div:first-child');
        const aside = document.querySelector('aside');
        
        if (mainContent.style.width === '100%') {
            mainContent.style.width = '';
            mainContent.style.flex = '';
            aside.style.display = '';
        } else {
            mainContent.style.width = '100%';
            mainContent.style.flex = '100%';
            aside.style.display = 'none';
        }
    }
</script>
<script type="text/javascript">
    var accountID = 5816;
    var adType = "int";
    var domains = ["desustream.com", "gofile.io", "www.mediafire.com", "acefile.co", "pixeldrain.com", "new6.gdflix.cfd", "filedon.co", "www.mirrored.to", "new5.gdflix.cfd", "new4.gdflix.cfd", "mir.cr", "smkn1stg-my.sharepoint.com", "lbx.to", "www110.zippyshare.com", "www62.zippyshare.com", "www.linkbox.to", "www64.zippyshare.com", "pomf2.lain.la", "new3.gdflix.dad", "new2.gdflix.dad", "akirabox.com", "buzzheavier.com", "mitedrive.my.id", "krakenfiles.com", "new6.gdtot.cfd", "drive.google.com", "sharer.pw", "www.blogger.com", "new4.gdflix.dad", "new8.gdflix.dad", "new7.gdflix.dad", "new6.gdflix.dad", "new.gdflix.dad", "new10.gdflix.dad", "new5.gdflix.dad", "new1.gdflix.dad", "dart.gdflix.ink", "new1.gdflix.cfd", "www101.zippyshare.com", "www17.zippyshare.com", "www106.zippyshare.com", "racaty.net", "clicknupload.cc", "www21.zippyshare.com", "www24.zippyshare.com", "www34.zippyshare.com", "www95.zippyshare.com", "www4.zippyshare.com", "www89.zippyshare.com", "www76.zippyshare.com", "www58.zippyshare.com", "www36.zippyshare.com", "www15.zippyshare.com", "www41.zippyshare.com", "www84.zippyshare.com", "www105.zippyshare.com", "www75.zippyshare.com", "www42.zippyshare.com", "www118.zippyshare.com", "d.terabox.com", "www78.zippyshare.com", "www10.zippyshare.com", "www97.zippyshare.com", "www31.zippyshare.com", "www11.zippyshare.com", "www37.zippyshare.com", "www14.zippyshare.com", "www39.zippyshare.com", "www19.zippyshare.com", "www108.zippyshare.com", "www114.zippyshare.com", "www68.zippyshare.com", "www102.zippyshare.com", "www63.zippyshare.com", "www104.zippyshare.com", "www79.zippyshare.com", "www96.zippyshare.com", "www20.zippyshare.com", "www35.zippyshare.com", "www52.zippyshare.com", "www16.zippyshare.com", "www100.zippyshare.com", "www12.zippyshare.com", "www85.zippyshare.com", "www30.zippyshare.com", "www107.zippyshare.com", "www115.zippyshare.com", "www47.zippyshare.com", "www23.zippyshare.com", "www55.zippyshare.com", "www73.zippyshare.com", "www50.zippyshare.com", "www46.zippyshare.com", "www57.zippyshare.com", "www6.zippyshare.com", "www70.zippyshare.com", "www59.zippyshare.com", "www18.zippyshare.com", "www8.zippyshare.com", "www94.zippyshare.com", "www28.zippyshare.com", "www103.zippyshare.com", "www45.zippyshare.com", "www66.zippyshare.com", "www2.zippyshare.com", "www25.zippyshare.com", "www69.zippyshare.com", "www120.zippyshare.com", "www81.zippyshare.com", "www93.zippyshare.com", "www91.zippyshare.com", "www74.zippyshare.com", "www61.zippyshare.com", "www60.zippyshare.com", "www48.zippyshare.com", "www38.zippyshare.com", "www90.zippyshare.com", "www92.zippyshare.com", "www32.zippyshare.com", "www56.zippyshare.com", "www72.zippyshare.com", "www54.zippyshare.com", "www80.zippyshare.com", "www86.zippyshare.com", "www27.zippyshare.com", "www111.zippyshare.com", "www98.zippyshare.com", "www112.zippyshare.com", "www87.zippyshare.com", "www44.zippyshare.com", "www9.zippyshare.com", "www117.zippyshare.com", "www51.zippyshare.com", "www13.zippyshare.com", "www65.zippyshare.com", "www99.zippyshare.com", "www49.zippyshare.com", "www26.zippyshare.com", "www83.zippyshare.com", "www29.zippyshare.com", "www71.zippyshare.com", "www113.zippyshare.com", "www77.zippyshare.com", "www33.zippyshare.com", "www43.zippyshare.com", "nerd.gdflix.ink", "oploverz.strp2p.site", "www67.zippyshare.com", "www40.zippyshare.com", "www22.zippyshare.com", "www82.zippyshare.com", "racaty.io", "megaup.net", "oplv.io", "elsfile.org", "clicknupload.co", "www109.zippyshare.com", "www119.zippyshare.com", "www7.zippyshare.com", "www3.zippyshare.com"];
</script>
<script type="text/javascript" src="//bc.vc/js/link-converter.js"></script>
@endsection