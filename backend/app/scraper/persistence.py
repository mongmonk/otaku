import re
import unicodedata
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy.future import select
from sqlalchemy import update, delete
from sqlalchemy.orm import selectinload
from app.models.database import Anime, Episode, DownloadLink, StreamLink, Genre
from datetime import datetime

def slugify(text: str) -> str:
    """
    Menghasilkan slug yang konsisten dengan Laravel Str::slug.
    """
    # Normalisasi karakter unicode ke ASCII
    text = unicodedata.normalize('NFKD', text).encode('ascii', 'ignore').decode('ascii')
    # Lowercase
    text = text.lower()
    # Hapus karakter non-alphanumeric (kecuali spasi dan dash)
    text = re.sub(r'[^a-z0-9\s-]', '', text)
    # Ganti spasi dan underscore dengan dash tunggal
    text = re.sub(r'[\s_]+', '-', text)
    # Hapus dash berlebih
    text = re.sub(r'-+', '-', text)
    # Trim dash di awal dan akhir
    return text.strip('-')

async def save_anime(db: AsyncSession, anime_data: dict):
    """
    Menyimpan atau memperbarui data anime (upsert).
    """
    # Simpan slug asli dari URL scraper
    original_url_slug = anime_data.get("slug")
    title = anime_data.get("title")
    
    # Generate slug baru yang bersih
    new_clean_slug = slugify(title) if title else original_url_slug
    
    # Update anime_data untuk menggunakan slug baru
    anime_data["slug"] = new_clean_slug

    if not new_clean_slug:
        return None

    # Handle genres separately
    raw_genres = anime_data.pop("genre", "")
    if raw_genres is None:
        raw_genres = ""
    genre_names = [g.strip() for g in str(raw_genres).split(",") if g.strip()]

    # STRATEGI PENCARIAN GANDA:
    # 1. Cek berdasarkan Title (Paling akurat untuk menghindari duplikat)
    anime = None
    if title:
        result = await db.execute(
            select(Anime)
            .where(Anime.title == title)
            .options(selectinload(Anime.genres))
        )
        anime = result.scalars().first()

    # 2. Jika tidak ketemu title, cek berdasarkan slug baru
    if not anime:
        result = await db.execute(
            select(Anime)
            .where(Anime.slug == new_clean_slug)
            .options(selectinload(Anime.genres))
        )
        anime = result.scalars().first()

    # 3. Jika masih tidak ketemu, cek berdasarkan slug asli dari URL
    if not anime and original_url_slug and original_url_slug != new_clean_slug:
        result = await db.execute(
            select(Anime)
            .where(Anime.slug == original_url_slug)
            .options(selectinload(Anime.genres))
        )
        anime = result.scalars().first()

    if anime:
        # Update existing
        for key, value in anime_data.items():
            setattr(anime, key, value)
    else:
        # Create new jika benar-benar tidak ada
        anime = Anime(**anime_data)
        db.add(anime)
        anime.genres = [] # Initialize relationship for new object
    
    await db.flush()

    # Process genres
    if genre_names:
        # Clear existing genres
        anime.genres = []
        for name in genre_names:
            slug = slugify(name)
            # Menggunakan SELECT ... FOR UPDATE untuk menghindari race condition saat pembuatan genre
            gen_res = await db.execute(select(Genre).where(Genre.slug == slug))
            genre = gen_res.scalars().first()
            if not genre:
                # Gunakan nested transaction agar rollback tidak merusak session utama
                nested = await db.begin_nested()
                try:
                    genre = Genre(slug=slug, name=name)
                    db.add(genre)
                    await nested.commit()
                except Exception:
                    await nested.rollback()
                    gen_res = await db.execute(select(Genre).where(Genre.slug == slug))
                    genre = gen_res.scalars().first()
            
            if genre and genre not in anime.genres:
                anime.genres.append(genre)

    # await db.commit()  # Removed commit to allow parent transaction control
    await db.flush()
    await db.refresh(anime)
    return anime

async def save_episode(db: AsyncSession, anime_id: int, episode_data: dict):
    """
    Menyimpan atau memperbarui data episode serta link download/stream.
    """
    episode_slug = episode_data.get("episode_slug")
    if not episode_slug:
        return None

    # Pisahkan links dari data episode utama
    download_links = episode_data.pop("download_links", [])
    stream_links = episode_data.pop("stream_links", [])

    # Hapus 'url' dari episode_data karena tidak ada di model database
    episode_data.pop("url", None)

    result = await db.execute(select(Episode).where(Episode.episode_slug == episode_slug))
    episode = result.scalars().first()

    if episode:
        # Update existing episode
        for key, value in episode_data.items():
            setattr(episode, key, value)
        episode.anime_id = anime_id
    else:
        # Create new episode
        episode = Episode(**episode_data, anime_id=anime_id)
        db.add(episode)
    
    await db.flush() # Mendapatkan ID episode jika baru

    # Update Download Links (Hapus yang lama dan tambah yang baru untuk kesederhanaan upsert)
    await db.execute(delete(DownloadLink).where(DownloadLink.episode_id == episode.id))
    for dl in download_links:
        new_dl = DownloadLink(episode_id=episode.id, **dl)
        db.add(new_dl)

    # Update Stream Links
    await db.execute(delete(StreamLink).where(StreamLink.episode_id == episode.id))
    for sl in stream_links:
        new_sl = StreamLink(episode_id=episode.id, **sl)
        db.add(new_sl)

    # await db.commit()  # Removed commit to allow parent transaction control
    await db.flush()
    await db.refresh(episode)
    return episode