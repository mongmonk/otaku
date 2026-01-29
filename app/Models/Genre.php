<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Genre extends Model
{
    protected $table = 'genres';
    protected $primaryKey = 'slug';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];

    public function animes(): BelongsToMany
    {
        return $this->belongsToMany(Anime::class, 'anime_genres', 'genre_slug', 'anime_slug', 'slug', 'slug');
    }
}