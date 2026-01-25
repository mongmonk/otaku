import logging
from apscheduler.schedulers.asyncio import AsyncIOScheduler
from app.scraper.otakudesu import OtakuDesuScraper
from app.scraper.persistence import save_anime, save_episode
from app.core.config import AsyncSessionLocal

# Setup logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

scheduler = AsyncIOScheduler()

async def update_ongoing_task():
    """
    Task scheduler untuk memperbarui anime ongoing secara berkala.
    """
    logger.info("Starting ongoing anime update task...")
    scraper = OtakuDesuScraper()
    
    try:
        # 1. Crawl ongoing anime list
        ongoing_list = await scraper.crawl_ongoing()
        logger.info(f"Found {len(ongoing_list)} ongoing anime to process.")
        
        async with AsyncSessionLocal() as db:
            for item in ongoing_list:
                try:
                    # 2. Get detail for each anime
                    detail = await scraper.get_anime_detail(item["url"])
                    if not detail:
                        continue
                    
                    # 3. Save anime detail to database
                    # Simpan episodes sementara untuk diproses terpisah
                    episodes_data = detail.pop("episodes", [])
                    anime = await save_anime(db, detail)
                    
                    if anime:
                        logger.info(f"Updated anime: {anime.title}")
                        
                        # 4. Save episodes and their links
                        for ep in episodes_data:
                            # Ambil link download/stream untuk episode ini
                            links = await scraper.get_episode_links(ep["url"])
                            ep["download_links"] = links.get("download_links", [])
                            ep["stream_links"] = links.get("stream_links", [])
                            
                            await save_episode(db, anime.slug, ep)
                            
                except Exception as e:
                    logger.error(f"Error processing anime {item.get('title')}: {e}")
                    continue
                    
        logger.info("Ongoing anime update task completed successfully.")
    except Exception as e:
        logger.error(f"Failed to run ongoing anime update task: {e}")

def start_scheduler():
    if not scheduler.running:
        scheduler.add_job(update_ongoing_task, "interval", minutes=60, id="update_ongoing_job", replace_existing=True)
        scheduler.start()
        logger.info("Scheduler started. Task 'update_ongoing_job' scheduled every 60 minutes.")

def shutdown_scheduler():
    if scheduler.running:
        scheduler.shutdown()
        logger.info("Scheduler shut down.")