<?php

use App\Http\Controllers\AnimeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AnimeController::class, 'index'])->name('home');
Route::get('/anime', [AnimeController::class, 'list'])->name('anime.index');
Route::get('/az-list', [AnimeController::class, 'azList'])->name('anime.az');
Route::get('/anime/{slug}', [AnimeController::class, 'show'])->name('anime.show');
Route::get('/episode/{slug}', [AnimeController::class, 'episode'])->name('episode.show');
Route::get('/genres/{slug}', [AnimeController::class, 'genre'])->name('genre.show');
