<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\Episode;
use App\Models\Genre;
use Illuminate\Http\Request;

class AnimeController extends Controller
{
    public function index()
    {
        $latestAnimes = Anime::latest('updated_at')->take(10)->get();
        $popularAnimes = Anime::orderBy('score', 'desc')->where('score', '>', 0)->take(10)->get();
        $ongoingAnimes = Anime::where('status', 'Ongoing')->latest('updated_at')->take(10)->get();

        return view('home', compact('latestAnimes', 'popularAnimes', 'ongoingAnimes'));
    }

    public function list()
    {
        $animes = Anime::latest('updated_at')->paginate(20);
        return view('anime.list', compact('animes'));
    }

    public function latest()
    {
        $episodes = Episode::with('anime')->latest('updated_at')->paginate(20);
        return view('anime.latest', compact('episodes'));
    }

    public function studios()
    {
        $studios = Anime::select('studio', \DB::raw('count(*) as total'))
            ->whereNotNull('studio')
            ->groupBy('studio')
            ->orderBy('studio')
            ->get();
        return view('anime.studios', compact('studios'));
    }

    public function studio($studio)
    {
        $animes = Anime::where('studio', $studio)->latest('updated_at')->paginate(20);
        return view('anime.list', compact('animes', 'studio'));
    }

    public function completed()
    {
        $animes = Anime::where('status', 'Completed')->latest('updated_at')->paginate(20);
        $status = 'Completed';
        return view('anime.list', compact('animes', 'status'));
    }

    public function popular()
    {
        $animes = Anime::orderBy('score', 'desc')->where('score', '>', 0)->paginate(20);
        $isPopular = true;
        return view('anime.list', compact('animes', 'isPopular'));
    }

    public function search(Request $request)
    {
        $search = $request->get('s');
        $status = $request->get('status');
        $genre = $request->get('genre');

        $query = Anime::query();

        if ($search) {
            $query->where('title', 'like', '%' . $search . '%');
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($genre) {
            $query->whereHas('genres', function($q) use ($genre) {
                $q->where('slug', $genre);
            });
        }

        $animes = $query->orderBy('title', 'asc')->paginate(20);
        return view('anime.list', compact('animes'));
    }

    public function azList(Request $request)
    {
        $letter = $request->get('show', 'A');
        
        $query = Anime::orderBy('title', 'asc');

        if ($letter === '0-9') {
            $query->whereRaw('title REGEXP "^[0-9]"');
        } elseif ($letter === '#') {
            $query->whereRaw('title REGEXP "^[^a-zA-Z0-9]"');
        } else {
            $query->where('title', 'like', $letter . '%');
        }

        $animes = $query->paginate(30);
        
        return view('anime.az-list', compact('animes', 'letter'));
    }

    public function show($slug)
    {
        $anime = Anime::with(['genres', 'episodes' => function($q) {
            $q->orderBy('id', 'desc');
        }])->where('slug', $slug)->firstOrFail();

        return view('anime.show', compact('anime'));
    }

    public function episode($slug)
    {
        $episode = Episode::with(['anime.episodes', 'streamLinks', 'downloadLinks'])
            ->where('episode_slug', $slug)
            ->firstOrFail();

        return view('anime.episode', compact('episode'));
    }

    public function genre($slug)
    {
        $genre = Genre::where('slug', $slug)->firstOrFail();
        $animes = $genre->animes()->paginate(20);

        return view('anime.genre', compact('genre', 'animes'));
    }

    public function bookmarks()
    {
        return view('anime.bookmarks');
    }
}
