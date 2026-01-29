import asyncio
import logging
import sys
import os
import re

# Menambahkan path saat ini ke sys.path agar bisa import module 'app'
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from app.scraper.otakudesu import OtakuDesuScraper
from app.scraper.persistence import save_anime, save_episode
from app.core.config import AsyncSessionLocal

# Setup logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.StreamHandler(sys.stdout),
        logging.FileHandler('scrape_latest.log', encoding='utf-8')
    ]
)
logger = logging.getLogger(__name__)

async def scrape_latest():
    """
    Scraping episode terbaru dari homepage OtakuDesu.
    """
    print("\n" + "="*50)
    print("MEMULAI SCRAPING EPISODE TERBARU DARI HOMEPAGE")
    print("="*50 + "\n")
    
    scraper = OtakuDesuScraper()
    stats = {
        "anime_updated": 0,
        "episodes_saved": 0,
        "errors": 0
    }
    
    try:
        # 1. Ambil daftar anime ongoing/terbaru dari homepage
        print("[1/3] Mengambil daftar anime terbaru dari homepage...")
        latest_anime = await scraper.crawl_ongoing()
        print(f"Ditemukan {len(latest_anime)} anime di homepage.\n")
        
        if not latest_anime:
            print("Tidak ada anime yang ditemukan.")
            return

        # 2. Proses setiap anime
        for item in latest_anime:
            try:
                async with AsyncSessionLocal() as db:
                    print(f"[*] Memproses Anime: {item['title']}...")
                    
                    # Ambil detail anime untuk mendapatkan daftar episode terbaru
                    detail = await scraper.get_anime_detail(item["url"])
                    if not detail:
                        print(f"   ! Gagal mengambil detail untuk {item['title']}")
                        stats["errors"] += 1
                        continue
                    
                    # Simpan/Update data anime
                    episodes_data = detail.pop("episodes", [])
                    anime = await save_anime(db, detail)
                    
                    if anime:
                        stats["anime_updated"] += 1
                        
                        # 3. Ambil episode terbaru (biasanya yang pertama di list)
                        if episodes_data:
                            # Kita proses semua episode yang mungkin belum ada, 
                            # atau minimal episode terbaru. Untuk keamanan, cek 3 teratas.
                            for ep in episodes_data[:3]:
                                print(f"   [*] Memproses Episode: {ep['title']}...")
                                
                                # Ambil SEMUA link streaming (all_mirrors=True)
                                links = await scraper.get_episode_links(ep["url"], all_mirrors=True)
                                ep["stream_links"] = links.get("stream_links", [])
                                ep["download_links"] = [] # Bisa ditambahkan filter download jika perlu
                                
                                # Ekstraksi episode number
                                ep_num_match = re.search(r'Episode\s+(\d+)', ep.get("title", ""))
                                ep["episode_number"] = ep_num_match.group(1) if ep_num_match else None
                                
                                await save_episode(db, anime.slug, ep)
                                stats["episodes_saved"] += 1
                                
                            await db.commit()
                            print(f"   ✓ Berhasil update {item['title']}")
                            
            except Exception as e:
                logger.error(f"Error saat memproses {item.get('title')}: {e}")
                stats["errors"] += 1

        print("\n" + "="*50)
        print("SCRAPING SELESAI")
        print("="*50)
        print(f"Anime Diupdate   : {stats['anime_updated']}")
        print(f"Episode Disimpan : {stats['episodes_saved']}")
        print(f"Total Error      : {stats['errors']}")
        print("="*50 + "\n")
        
    except Exception as e:
        logger.error(f"Gagal menjalankan scraping terbaru: {e}")

if __name__ == "__main__":
    try:
        asyncio.run(scrape_latest())
    except KeyboardInterrupt:
        print("\nProses dihentikan oleh pengguna.")