<?php

/**
 * Script untuk mengimport data anime lengkap dari database 'animes' ke 'otaku'
 * Menyertakan: Animes, Genres, Anime_Genres, Episodes, Stream_Links, Download_Links
 * Dibuat oleh Kilo Code
 */

$host = '127.0.0.1';
$user = 'root';
$pass = '';
$db_source = 'animes';
$db_target = 'otaku';
$source_poster_dir = 'C:/laragon/www/animes/public/storage/posters';
$target_poster_dir = __DIR__ . '/public/posters';

if (!is_dir($target_poster_dir)) {
    mkdir($target_poster_dir, 0755, true);
}

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    echo "--- Memulai Import Lengkap ---\n";

    // 1. Import Genres
    echo "Importing genres...\n";
    $genres = $pdo->query("SELECT * FROM $db_source.genres")->fetchAll();
    $stmtGenre = $pdo->prepare("INSERT IGNORE INTO $db_target.genres (slug, name, created_at) VALUES (:slug, :name, :created_at)");
    foreach ($genres as $g) {
        $stmtGenre->execute([
            ':slug' => $g['slug'],
            ':name' => $g['name'],
            ':created_at' => $g['created_at'] ?? date('Y-m-d H:i:s')
        ]);
    }

    // 2. Ambil Anime yang belum ada
    $animes = $pdo->query("SELECT * FROM $db_source.animes WHERE slug NOT IN (SELECT slug FROM $db_target.animes)")->fetchAll();
    echo "Ditemukan " . count($animes) . " anime baru.\n";

    $stmtAnime = $pdo->prepare("
        INSERT INTO $db_target.animes 
        (slug, title, poster_url, synopsis, score, status, type, release_date, created_at, updated_at)
        VALUES (:slug, :title, :poster_url, :synopsis, :score, :status, :type, :release_date, :created_at, :updated_at)
    ");

    $stmtAnimeGenre = $pdo->prepare("INSERT IGNORE INTO $db_target.anime_genres (anime_id, genre_slug) VALUES (:aid, :gslug)");
    
    $stmtEpisode = $pdo->prepare("
        INSERT INTO $db_target.episodes (anime_id, title, episode_number, episode_slug, created_at, updated_at)
        VALUES (:aid, :title, :enum, :eslug, :ca, :ua)
    ");

    $stmtStream = $pdo->prepare("INSERT INTO $db_target.stream_links (episode_id, provider, url) VALUES (:eid, :prov, :url)");
    $stmtDownload = $pdo->prepare("INSERT INTO $db_target.download_links (episode_id, resolution, provider, url) VALUES (:eid, :res, :prov, :url)");

    foreach ($animes as $anime) {
        $pdo->beginTransaction();
        try {
            // Copy Poster
            $poster_val = $anime['poster_url'];
            if (!empty($poster_val) && !filter_var($poster_val, FILTER_VALIDATE_URL)) {
                $src = $source_poster_dir . '/' . $poster_val;
                $dst = $target_poster_dir . '/' . $poster_val;
                if (file_exists($src) && !file_exists($dst)) {
                    copy($src, $dst);
                }
            }

            // Insert Anime
            $stmtAnime->execute([
                ':slug' => $anime['slug'],
                ':title' => $anime['title'],
                ':poster_url' => $poster_val,
                ':synopsis' => $anime['synopsis'],
                ':score' => $anime['rating'],
                ':status' => $anime['status'],
                ':type' => $anime['type'],
                ':release_date' => $anime['release_date'],
                ':created_at' => $anime['created_at'] ?? date('Y-m-d H:i:s'),
                ':updated_at' => $anime['updated_at'] ?? date('Y-m-d H:i:s'),
            ]);
            $new_anime_id = $pdo->lastInsertId();

            // Anime Genres
            $a_genres = $pdo->prepare("SELECT g.slug FROM $db_source.genres g JOIN $db_source.anime_genre ag ON g.id = ag.genre_id WHERE ag.anime_id = ?");
            $a_genres->execute([$anime['id']]);
            foreach ($a_genres->fetchAll() as $ag) {
                $stmtAnimeGenre->execute([':aid' => $new_anime_id, ':gslug' => $ag['slug']]);
            }

            // Episodes
            $episodes = $pdo->prepare("SELECT * FROM $db_source.episodes WHERE anime_id = ?");
            $episodes->execute([$anime['id']]);
            foreach ($episodes->fetchAll() as $ep) {
                $stmtEpisode->execute([
                    ':aid' => $new_anime_id,
                    ':title' => $ep['title'] ?? ('Episode ' . $ep['episode_number']),
                    ':enum' => $ep['episode_number'],
                    ':eslug' => $ep['slug'],
                    ':ca' => $ep['created_at'] ?? date('Y-m-d H:i:s'),
                    ':ua' => $ep['updated_at'] ?? date('Y-m-d H:i:s')
                ]);
                $new_eid = $pdo->lastInsertId();

                // Stream Links
                $streams = $pdo->prepare("SELECT * FROM $db_source.episode_videos WHERE episode_id = ?");
                $streams->execute([$ep['id']]);
                foreach ($streams->fetchAll() as $st) {
                    $stmtStream->execute([':eid' => $new_eid, ':prov' => $st['source'], ':url' => $st['url']]);
                }

                // Download Links
                $downloads = $pdo->prepare("SELECT * FROM $db_source.episode_downloads WHERE episode_id = ?");
                $downloads->execute([$ep['id']]);
                foreach ($downloads->fetchAll() as $dl) {
                    $stmtDownload->execute([
                        ':eid' => $new_eid,
                        ':res' => $dl['quality'],
                        ':prov' => $dl['host'],
                        ':url' => $dl['url']
                    ]);
                }
            }

            $pdo->commit();
            echo "Imported: {$anime['slug']}\n";
        } catch (Exception $e) {
            $pdo->rollBack();
            echo "Error importing {$anime['slug']}: " . $e->getMessage() . "\n";
        }
    }

    echo "--- Import Selesai ---\n";

} catch (PDOException $e) {
    echo "Koneksi Gagal: " . $e->getMessage() . "\n";
}