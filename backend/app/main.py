from fastapi import FastAPI
from app.core.config import engine
from app.models.database import Base
from app.core.scheduler import start_scheduler, shutdown_scheduler

app = FastAPI(title="Otaku API")

@app.get("/")
async def root():
    return {"message": "Otaku Backend is running"}

@app.on_event("startup")
async def startup():
    # In production, use migrations (Alembic)
    # This will create tables if they don't exist
    async with engine.begin() as conn:
        # await conn.run_sync(Base.metadata.drop_all) # Dangerous
        await conn.run_sync(Base.metadata.create_all)
    
    # Start the task scheduler
    start_scheduler()

@app.on_event("shutdown")
async def shutdown():
    # Shutdown the task scheduler
    shutdown_scheduler()