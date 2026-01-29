import asyncio
import logging
import sys
import os
from sqlalchemy.future import select
from sqlalchemy.orm import selectinload
from sqlalchemy import func

# Menambahkan path saat ini ke sys.path agar bisa import module 'app'
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from app.scraper.otakudesu import OtakuDesuScraper
from app.scraper.persistence import save_episode
from app.core.config import AsyncSessionLocal
from app.models.database import Episode, StreamLink

# Setup logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.StreamHandler(sys.stdout),
        logging.FileHandler('fix_streams.log', encoding='utf-8')
    ]
)
logger = logging.getLogger(__name__)

async def fix_missing_streams():
    """
    Mencari episode yang tidak memiliki stream_links dan melakukan scrape ulang link-nya.
    """
    print("\n" + "="*50)
    print("MEMULAI PERBAIKAN EPISODE TANPA STREAM LINK")
    print("="*50 + "\n")
    
    scraper = OtakuDesuScraper()
    stats = {
        "checked": 0,
        "fixed": 0,
        "errors": 0
    }
    
    try:
        # 1. Cari episode yang tidak memiliki stream_links
        async with AsyncSessionLocal() as db:
            subquery = select(StreamLink.episode_id)
            query = select(Episode).where(~Episode.id.in_(subquery))
            
            result = await db.execute(query)
            episodes_to_fix = result.scalars().all()
            
            # Detach dari session agar bisa diproses di session lain
            episodes_data = [
                {
                    "id": ep.id,
                    "title": ep.title,
                    "episode_slug": ep.episode_slug,
                    "episode_number": ep.episode_number,
                    "uploaded_at": ep.uploaded_at,
                    "anime_slug": ep.anime_slug
                } for ep in episodes_to_fix
            ]
        
        total_to_fix = len(episodes_data)
        print(f"Ditemukan {total_to_fix} episode tanpa stream link.\n")
        
        if not episodes_data:
            print("Semua episode sudah memiliki stream link.")
            return

        # 2. Proses perbaikan
        semaphore = asyncio.Semaphore(3) # Kurangi concurrency untuk stabilitas

        async def process_fix(ep_info):
            nonlocal stats
            async with semaphore:
                async with AsyncSessionLocal() as db:
                    try:
                        ep_url = f"https://otakudesu.best/episode/{ep_info['episode_slug']}/"
                        print(f"[*] Memperbaiki: {ep_info['title']}...")
                        
                        links = await scraper.get_episode_links(ep_url)
                        sl_links = links.get("stream_links", [])
                        
                        if not sl_links:
                            print(f"   ! Masih tidak ada stream link untuk {ep_info['title']}")
                            stats["errors"] += 1
                            return

                        from app.models.database import DownloadLink
                        dl_result = await db.execute(select(DownloadLink).where(DownloadLink.episode_id == ep_info['id']))
                        existing_dls = dl_result.scalars().all()
                        
                        ep_data = {
                            "title": ep_info['title'],
                            "episode_slug": ep_info['episode_slug'],
                            "episode_number": ep_info['episode_number'],
                            "uploaded_at": ep_info['uploaded_at'],
                            "stream_links": sl_links,
                            "download_links": [
                                {"resolution": dl.resolution, "provider": dl.provider, "url": dl.url}
                                for dl in existing_dls
                            ]
                        }

                        await save_episode(db, ep_info['anime_slug'], ep_data)
                        await db.commit()
                        
                        print(f"   ✓ Berhasil diperbarui: {ep_info['title']} ({len(sl_links)} streams)")
                        stats["fixed"] += 1
                        
                    except Exception as e:
                        try: await db.rollback()
                        except: pass
                        logger.error(f"Error pada {ep_info['episode_slug']}: {e}")
                        stats["errors"] += 1

        # Jalankan perbaikan secara berurutan atau batch kecil untuk menghindari loop closed
        for ep_info in episodes_data:
            await process_fix(ep_info)
            
        print("\n" + "="*50)
        print("PROSES PERBAIKAN SELESAI")
        print("="*50)
        print(f"Total Episode Dicek : {total_to_fix}")
        print(f"Total Berhasil      : {stats['fixed']}")
        print(f"Total Gagal/Error   : {stats['errors']}")
        print("="*50 + "\n")
            
    except Exception as e:
        logger.error(f"Gagal menjalankan perbaikan: {e}")

if __name__ == "__main__":
    try:
        asyncio.run(fix_missing_streams())
    except KeyboardInterrupt:
        print("\nProses dihentikan oleh pengguna.")