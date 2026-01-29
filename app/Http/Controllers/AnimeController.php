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
        $popularAnimes = Anime::orderBy('score', 'desc')->take(10)->get();
        $ongoingAnimes = Anime::where('status', 'Ongoing')->latest('updated_at')->take(10)->get();

        return view('home', compact('latestAnimes', 'popularAnimes', 'ongoingAnimes'));
    }

    public function list()
    {
        $animes = Anime::orderBy('title', 'asc')->paginate(20);
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
}
