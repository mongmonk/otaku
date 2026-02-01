import asyncio
import logging
import sys
import os

# Menambahkan path saat ini ke sys.path agar bisa import module 'app'
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from app.core.config import AsyncSessionLocal
from app.models.database import Anime
from sqlalchemy.future import select
from sqlalchemy import update

# Setup logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

async def fix_poster_paths():
    """
    Memperbaiki poster_url di database yang tidak memiliki prefix /posters/
    """
    print("\n" + "="*50)
    print("MEMULAI PERBAIKAN PATH POSTER DI DATABASE")
    print("="*50 + "\n")
    
    async with AsyncSessionLocal() as db:
        try:
            # Ambil semua anime yang poster_url-nya tidak diawali dengan /posters/ dan bukan URL http
            result = await db.execute(
                select(Anime).where(
                    ~Anime.poster_url.like('/posters/%'),
                    ~Anime.poster_url.like('http%'),
                    Anime.poster_url != None
                )
            )
            animes = result.scalars().all()
            
            print(f"Ditemukan {len(animes)} record yang perlu diperbaiki.\n")
            
            count = 0
            for anime in animes:
                old_path = anime.poster_url
                new_path = f"/posters/{old_path}"
                
                print(f"[*] Updating ID {anime.id}: {old_path} -> {new_path}")
                anime.poster_url = new_path
                count += 1
                
                if count % 50 == 0:
                    await db.flush()
            
            await db.commit()
            print(f"\n[✓] Berhasil memperbarui {count} record.")
            
        except Exception as e:
            await db.rollback()
            logger.error(f"Terjadi kesalahan saat memperbarui database: {e}")
            raise e

    print("\n" + "="*50)
    print("PERBAIKAN SELESAI")
    print("="*50 + "\n")

if __name__ == "__main__":
    asyncio.run(fix_poster_paths())