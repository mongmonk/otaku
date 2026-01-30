<?php

use App\Http\Controllers\AnimeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AnimeController::class, 'index'])->name('home');
Route::get('/anime', [AnimeController::class, 'list'])->name('anime.index');
Route::get('/popular', [AnimeController::class, 'popular'])->name('anime.popular');
Route::get('/latest', [AnimeController::class, 'latest'])->name('anime.latest');
Route::get('/search', [AnimeController::class, 'search'])->name('anime.search');
Route::get('/completed', [AnimeController::class, 'completed'])->name('anime.completed');
Route::get('/studios', [AnimeController::class, 'studios'])->name('anime.studios');
Route::get('/studio/{studio}', [AnimeController::class, 'studio'])->name('anime.studio');
Route::get('/schedule', [AnimeController::class, 'list'])->name('anime.schedule');
Route::get('/az-list', [AnimeController::class, 'azList'])->name('anime.az');
Route::get('/anime/{slug}', [AnimeController::class, 'show'])->name('anime.show');
Route::get('/eps/{slug}.html', [AnimeController::class, 'episode'])->name('episode.show');
Route::get('/genres/{slug}', [AnimeController::class, 'genre'])->name('genre.show');
Route::get('/bookmarks', [AnimeController::class, 'bookmarks'])->name('anime.bookmarks');
