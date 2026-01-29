import asyncio
import logging
import sys
import os
from sqlalchemy.future import select
from sqlalchemy import delete, or_

# Menambahkan path saat ini ke sys.path agar bisa import module 'app'
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from app.scraper.otakudesu import OtakuDesuScraper
from app.core.config import AsyncSessionLocal
from app.models.database import StreamLink

# Setup logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.StreamHandler(sys.stdout),
        logging.FileHandler('cleanup_streams.log', encoding='utf-8')
    ]
)
logger = logging.getLogger(__name__)

async def cleanup_desustream():
    """
    Mencari link desustream di database, mencoba resolve ke blogger,
    dan menghapusnya jika video mati atau tidak bisa di-resolve.
    """
    print("\n" + "="*50)
    print("MEMULAI PEMBERSIHAN LINK DESUSTREAM")
    print("="*50 + "\n")
    
    scraper = OtakuDesuScraper()
    stats = {
        "checked": 0,
        "resolved": 0,
        "deleted": 0,
        "errors": 0
    }
    
    try:
        # 1. Ambil semua link yang mengandung desustream
        async with AsyncSessionLocal() as db:
            query = select(StreamLink).where(
                or_(
                    StreamLink.url.like("%desustream.info%"),
                    StreamLink.url.like("%desustream.com%")
                )
            )
            result = await db.execute(query)
            links_to_check = result.scalars().all()
            
            # Detach data agar aman diproses
            links_data = [
                {"id": l.id, "url": l.url, "episode_id": l.episode_id} 
                for l in links_to_check
            ]

        total_to_check = len(links_data)
        print(f"Ditemukan {total_to_check} link desustream untuk diperiksa.\n")
        
        if not links_data:
            print("Tidak ada link desustream yang perlu dibersihkan.")
            return

        # 2. Proses pembersihan
        semaphore = asyncio.Semaphore(5)

        async def process_link(link_info):
            nonlocal stats
            async with semaphore:
                async with AsyncSessionLocal() as db:
                    try:
                        print(f"[*] Memeriksa ID {link_info['id']}: {link_info['url'][:50]}...")
                        
                        # Coba resolve link desustream
                        resolved_url = await scraper.resolve_desustream(link_info['url'])
                        
                        if resolved_url and "blogger.com" in resolved_url:
                            # Berhasil resolve ke blogger, update link
                            from sqlalchemy import update
                            await db.execute(
                                update(StreamLink)
                                .where(StreamLink.id == link_info['id'])
                                .values(url=resolved_url, provider="DesuDrive (Resolved)")
                            )
                            await db.commit()
                            print(f"   ✓ Berhasil di-resolve ke Blogger.")
                            stats["resolved"] += 1
                        else:
                            # Link mati atau tidak bisa di-resolve, hapus row
                            await db.execute(
                                delete(StreamLink).where(StreamLink.id == link_info['id'])
                            )
                            await db.commit()
                            print(f"   × Video mati/tidak valid. Row dihapus.")
                            stats["deleted"] += 1
                            
                    except Exception as e:
                        try: await db.rollback()
                        except: pass
                        logger.error(f"Error pada link ID {link_info['id']}: {e}")
                        stats["errors"] += 1

        # Jalankan secara paralel dengan limit
        tasks = [process_link(l) for l in links_data]
        await asyncio.gather(*tasks)
        
        print("\n" + "="*50)
        print("PROSES PEMBERSIHAN SELESAI")
        print("="*50)
        print(f"Total Dicek    : {total_to_check}")
        print(f"Total Resolved : {stats['resolved']}")
        print(f"Total Dihapus  : {stats['deleted']}")
        print(f"Total Error    : {stats['errors']}")
        print("="*50 + "\n")
            
    except Exception as e:
        logger.error(f"Gagal menjalankan cleanup: {e}")

if __name__ == "__main__":
    try:
        asyncio.run(cleanup_desustream())
    except KeyboardInterrupt:
        print("\nProses dihentikan oleh pengguna.")