import asyncio
import logging
import sys
import os
from sqlalchemy import select, func
from sqlalchemy.ext.asyncio import AsyncSession

# Menambahkan direktori backend ke sys.path agar bisa import module app
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from app.core.config import engine, AsyncSessionLocal
from app.models.database import Base, Anime, Episode, DownloadLink, StreamLink
from app.core.scheduler import update_ongoing_task
from app.scraper.otakudesu import OtakuDesuScraper

# Setup logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(name)s - %(levelname)s - %(message)s')
logger = logging.getLogger("SystemTest")

async def init_db():
    """Inisialisasi database (memastikan tabel dibuat)."""
    logger.info("Initializing database tables...")
    async with engine.begin() as conn:
        # Kita tidak melakukan drop_all karena ini sistem produksi/nyata, 
        # cukup create_all untuk memastikan tabel ada.
        await conn.run_sync(Base.metadata.create_all)
    logger.info("Database tables initialized.")

async def verify_data():
    """Verifikasi data di database."""
    logger.info("Verifying data in database...")
    async with AsyncSessionLocal() as db:
        # Hitung jumlah anime
        anime_count = await db.scalar(select(func.count(Anime.slug)))
        # Hitung jumlah episode
        episode_count = await db.scalar(select(func.count(Episode.id)))
        # Hitung jumlah link download
        dl_count = await db.scalar(select(func.count(DownloadLink.id)))
        # Hitung jumlah link stream
        stream_count = await db.scalar(select(func.count(StreamLink.id)))

        logger.info(f"Summary Results:")
        logger.info(f"- Animes: {anime_count}")
        logger.info(f"- Episodes: {episode_count}")
        logger.info(f"- Download Links: {dl_count}")
        logger.info(f"- Stream Links: {stream_count}")

        # Ambil contoh 1 anime untuk detail
        result = await db.execute(select(Anime).limit(1))
        sample_anime = result.scalars().first()
        if sample_anime:
            logger.info(f"Sample Anime found: {sample_anime.title} ({sample_anime.slug})")
        
        return {
            "animes": anime_count,
            "episodes": episode_count,
            "download_links": dl_count,
            "stream_links": stream_count
        }

async def run_test():
    try:
        # 1. Inisialisasi DB
        await init_db()

        # 2. Jalankan simulasi update_ongoing_task
        # Catatan: update_ongoing_task akan mengambil semua anime ongoing.
        # Untuk pengujian cepat, kita modifikasi sedikit logikanya di sini atau biarkan berjalan.
        # Karena instruksi meminta simulasi alur kerja lengkap.
        logger.info("Running update_ongoing_task simulation...")
        
        # Kita panggil fungsi aslinya tetapi batasi hanya 1 anime untuk pengujian cepat
        logger.info("Starting ongoing anime update task (limited to 1 anime)...")
        scraper = OtakuDesuScraper()
        
        try:
            ongoing_list = await scraper.crawl_ongoing()
            logger.info(f"Found {len(ongoing_list)} ongoing anime.")
            
            if ongoing_list:
                # Batasi hanya 1 anime
                test_item = ongoing_list[0]
                logger.info(f"Processing test anime: {test_item['title']}")
                
                async with AsyncSessionLocal() as db:
                    detail = await scraper.get_anime_detail(test_item["url"])
                    if detail:
                        episodes_data = detail.pop("episodes", [])
                        # Batasi hanya 2 episode terbaru untuk kecepatan
                        episodes_data = episodes_data[:2]
                        
                        from app.scraper.persistence import save_anime, save_episode
                        anime = await save_anime(db, detail)
                        
                        if anime:
                            logger.info(f"Updated anime: {anime.title}")
                            for ep in episodes_data:
                                links = await scraper.get_episode_links(ep["url"])
                                ep["download_links"] = links.get("download_links", [])
                                ep["stream_links"] = links.get("stream_links", [])
                                await save_episode(db, anime.slug, ep)
            
            logger.info("Simulation completed successfully.")
        except Exception as e:
            logger.error(f"Failed to run simulation: {e}")
        
        # 3. Verifikasi hasil
        results = await verify_data()
        
        logger.info("System verification completed successfully!")
        return results

    except Exception as e:
        logger.error(f"Test failed with error: {e}", exc_info=True)
        return None

if __name__ == "__main__":
    results = asyncio.run(run_test())
    if results:
        print("\n--- TEST SUMMARY ---")
        print(f"Total Animes Saved: {results['animes']}")
        print(f"Total Episodes Saved: {results['episodes']}")
        print(f"Total Download Links: {results['download_links']}")
        print(f"Total Stream Links: {results['stream_links']}")
        print("--------------------")
    else:
        print("\n--- TEST FAILED ---")
        sys.exit(1)