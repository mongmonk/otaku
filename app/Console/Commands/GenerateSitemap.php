<?php

namespace App\Console\Commands;

use App\Models\Anime;
use App\Models\Episode;
use App\Models\Genre;
use Illuminate\Console\Command;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';

    protected $description = 'Generate sitemap.xml file in public directory';

    public function handle()
    {
        $this->info('Generating sitemap.xml...');

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

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL;

        foreach ($urls as $url) {
            $xml .= '    <url>'.PHP_EOL;
            $xml .= '        <loc>'.htmlspecialchars($url['loc']).'</loc>'.PHP_EOL;
            $xml .= '        <lastmod>'.$url['lastmod'].'</lastmod>'.PHP_EOL;
            $xml .= '        <changefreq>'.$url['changefreq'].'</changefreq>'.PHP_EOL;
            $xml .= '        <priority>'.$url['priority'].'</priority>'.PHP_EOL;
            $xml .= '    </url>'.PHP_EOL;
        }

        $xml .= '</urlset>';

        file_put_contents(public_path('sitemap.xml'), $xml);

        $this->info('sitemap.xml has been generated successfully in '.public_path('sitemap.xml'));
    }
}
