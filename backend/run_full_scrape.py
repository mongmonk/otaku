import asyncio
import logging
import sys
import os
import httpx
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
        logging.StreamHandler(sys.stdout)
    ]
)
logger = logging.getLogger(__name__)

async def download_poster(url: str, slug: str) -> str:
    """
    Mendownload poster dan menyimpannya ke folder lokal.
    """
    try:
        # Tentukan folder penyimpanan (relatif terhadap root project)
        # Kita simpan di public/posters agar bisa diakses langsung via web
        storage_path = os.path.join(os.getcwd(), "public", "posters")
        if not os.path.exists(storage_path):
            os.makedirs(storage_path)

        extension = url.split(".")[-1].split("?")[0]
        if len(extension) > 4: # Jaga-jaga jika bukan extension
            extension = "jpg"
            
        filename = f"{slug}.{extension}"
        file_path = os.path.join(storage_path, filename)

        async with httpx.AsyncClient(timeout=30.0) as client:
            response = await client.get(url)
            if response.status_code == 200:
                with open(file_path, "wb") as f:
                    f.write(response.content)
                return f"/posters/{filename}" # Path relatif untuk database
    except Exception as e:
        logger.error(f"Gagal mendownload poster {url}: {e}")
    
    return url # Fallback ke URL original jika gagal

async def process_anime(scraper, item, stats, semaphore):
    """
    Memproses satu anime dengan concurrency limit dan retry untuk deadlock.
    """
    max_retries = 3
    for attempt in range(max_retries):
        async with semaphore:
            async with AsyncSessionLocal() as db:
                try:
                    print(f"[*] Memproses: {item['title']}...")
                    
                    # 2. Ambil detail untuk setiap anime
                    detail = await scraper.get_anime_detail(item["url"])
                    if not detail:
                        print(f"   ! Gagal mengambil detail untuk {item['title']}")
                        stats["errors"] += 1
                        return
                    
                    # Download poster ke lokal
                    if detail.get("poster_url"):
                        local_poster_url = await download_poster(detail["poster_url"], detail["slug"])
                        detail["poster_url"] = local_poster_url

                    # 3. Simpan detail anime ke database
                    episodes_data = detail.pop("episodes", [])
                    anime = await save_anime(db, detail)
                    
                    if anime:
                        stats["total_anime"] += 1
                        
                        # 4. Simpan episode dan link-nya
                        for ep in episodes_data:
                            # Ambil link download/stream untuk episode ini
                            links = await scraper.get_episode_links(ep["url"])
                            all_dl_links = links.get("download_links", [])
                            sl_links = links.get("stream_links", [])
                            
                            # Ekstraksi episode number dari title
                            ep_num_match = re.search(r'Episode\s+(\d+)', ep.get("title", ""))
                            if ep_num_match:
                                ep["episode_number"] = ep_num_match.group(1)
                            else:
                                ep["episode_number"] = None

                            # Filter download links: 360p, 480p, 720p & Provider OtakuFiles
                            filtered_dl = []
                            target_resolutions = ["360p", "480p", "720p"]
                            
                            for res in target_resolutions:
                                res_links = [l for l in all_dl_links if res in l.get("resolution", "")]
                                if res_links:
                                    otaku_file_link = next((l for l in res_links if "OtakuFiles" in l.get("provider", "") or "ODFiles" in l.get("provider", "")), None)
                                    if otaku_file_link:
                                        filtered_dl.append(otaku_file_link)
                                    else:
                                        filtered_dl.append(res_links[0])
                            
                            ep["download_links"] = filtered_dl
                            ep["stream_links"] = sl_links
                            
                            num_links = len(filtered_dl) + len(sl_links)
                            
                            await save_episode(db, anime.slug, ep)
                            stats["total_episodes"] += 1
                            stats["total_links"] += num_links
                        
                        print(f"   ✓ Berhasil: {anime.title} ({len(episodes_data)} eps)")
                        return # Berhasil, keluar dari loop retry
                            
                except Exception as e:
                    await db.rollback()
                    if "Deadlock found" in str(e) and attempt < max_retries - 1:
                        logger.warning(f"Deadlock pada {item['title']}, mencoba ulang ({attempt + 1}/{max_retries})...")
                        await asyncio.sleep(1) # Tunggu sebentar sebelum retry
                        continue
                    
                    logger.error(f"Error saat memproses anime {item.get('title')}: {e}")
                    stats["errors"] += 1
                    return

async def run_full_scrape():
    """
    Menjalankan proses scraping penuh menggunakan sitemap.
    """
    print("\n" + "="*50)
    print("MENGAWALI PROSES SCRAPING PENUH VIA SITEMAP")
    print("="*50 + "\n")
    
    scraper = OtakuDesuScraper()
    stats = {
        "total_anime": 0,
        "total_episodes": 0,
        "total_links": 0,
        "errors": 0
    }
    
    sitemaps = [
        "https://otakudesu.best/anime-sitemap1.xml",
        "https://otakudesu.best/anime-sitemap2.xml"
    ]
    
    # Limit concurrency agar tidak diblokir (5-10 request sekaligus)
    semaphore = asyncio.Semaphore(8)
    
    try:
        # 1. Ambil daftar anime dari sitemap
        print("[1/2] Mengambil daftar anime dari sitemap...")
        anime_list = await scraper.crawl_sitemap(sitemaps)
        total_to_process = len(anime_list)
        print(f"Ditemukan total {total_to_process} anime.\n")
        
        if not anime_list:
            print("Tidak ada anime yang ditemukan di sitemap.")
            return

        # Buat task untuk setiap anime
        tasks = []
        for item in anime_list:
            tasks.append(process_anime(scraper, item, stats, semaphore))
        
        # Jalankan semua task secara concurrent
        await asyncio.gather(*tasks)
                    
        print("\n" + "="*50)
        print("SCRAPING SELESAI")
        print("="*50)
        print(f"Total Anime   : {stats['total_anime']}")
        print(f"Total Episode : {stats['total_episodes']}")
        print(f"Total Link    : {stats['total_links']}")
        print(f"Total Error   : {stats['errors']}")
        print("="*50 + "\n")
        
    except Exception as e:
        logger.error(f"Gagal menjalankan scraping: {e}")

if __name__ == "__main__":
    try:
        asyncio.run(run_full_scrape())
    except KeyboardInterrupt:
        print("\nProses dihentikan oleh pengguna.")