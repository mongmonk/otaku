<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Episode extends Model
{
    protected $table = 'episodes';
    protected $guarded = [];

    public function anime(): BelongsTo
    {
        return $this->belongsTo(Anime::class, 'anime_slug', 'slug');
    }

    public function streamLinks(): HasMany
    {
        return $this->hasMany(StreamLink::class, 'episode_id');
    }

    public function downloadLinks(): HasMany
    {
        return $this->hasMany(DownloadLink::class, 'episode_id');
    }
}