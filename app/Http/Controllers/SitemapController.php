<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\Episode;
use App\Models\Genre;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $urls = [];

        // Static Pages
        $staticPages = [
            '',
            '/anime',
            '/popular',
            '/latest',
            '/completed',
            '/studios',
            '/genres',
            '/schedule',
            '/az-list',
        ];

        foreach ($staticPages as $page) {
            $urls[] = [
                'loc' => url($page),
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'daily',
                'priority' => '1.0',
            ];
        }

        // Anime Pages
        $animes = Anime::select('slug', 'updated_at')->get();
        foreach ($animes as $anime) {
            $urls[] = [
                'loc' => route('anime.show', $anime->slug),
                'lastmod' => $anime->updated_at->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        }

        // Episode Pages
        $episodes = Episode::select('episode_slug', 'updated_at')->get();
        foreach ($episodes as $episode) {
            $urls[] = [
                'loc' => route('episode.show', $episode->episode_slug),
                'lastmod' => $episode->updated_at->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ];
        }

        // Genre Pages
        $genres = Genre::select('slug', 'created_at')->get();
        foreach ($genres as $genre) {
            $urls[] = [
                'loc' => route('genre.show', $genre->slug),
                'lastmod' => $genre->created_at->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.5',
            ];
        }

        $xml = view('sitemap', compact('urls'))->render();

        return response($xml, 200)->header('Content-Type', 'text/xml');
    }
}
