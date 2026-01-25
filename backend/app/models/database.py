from sqlalchemy import Column, Integer, String, Text, Float, DateTime, ForeignKey, func, TIMESTAMP, Table
from sqlalchemy.orm import relationship, declarative_base

Base = declarative_base()

# Association table for Anime and Genre
anime_genres = Table(
    "anime_genres",
    Base.metadata,
    Column("anime_slug", String(255), ForeignKey("animes.slug", ondelete="CASCADE"), primary_key=True),
    Column("genre_id", Integer, ForeignKey("genres.id", ondelete="CASCADE"), primary_key=True),
)

class Genre(Base):
    __tablename__ = "genres"
    id = Column(Integer, primary_key=True, autoincrement=True)
    name = Column(String(100), unique=True, nullable=False)
    animes = relationship("Anime", secondary=anime_genres, back_populates="genres")

class Anime(Base):
    __tablename__ = "animes"

    slug = Column(String(255), primary_key=True, nullable=False)
    title = Column(String(255), nullable=False)
    title_jp = Column(String(255))
    score = Column(Float)
    producer = Column(String(255))
    type = Column(String(100))
    status = Column(String(100))
    total_episode = Column(Integer, default=0)
    duration = Column(String(100))
    release_date = Column(String(100))
    studio = Column(String(255))
    synopsis = Column(Text)
    poster_url = Column(String(500))
    created_at = Column(TIMESTAMP, server_default=func.current_timestamp())
    updated_at = Column(TIMESTAMP, server_default=func.current_timestamp(), onupdate=func.current_timestamp())

    episodes = relationship("Episode", back_populates="anime", cascade="all, delete-orphan")
    genres = relationship("Genre", secondary=anime_genres, back_populates="animes", lazy="selectin")

class Episode(Base):
    __tablename__ = "episodes"

    id = Column(Integer, primary_key=True, autoincrement=True)
    anime_slug = Column(String(255), ForeignKey("animes.slug", ondelete="CASCADE", onupdate="CASCADE"), nullable=False)
    title = Column(String(255), nullable=False)
    episode_number = Column(String(50))
    episode_slug = Column(String(255), unique=True, nullable=False)
    uploaded_at = Column(String(100))
    created_at = Column(TIMESTAMP, server_default=func.current_timestamp())
    updated_at = Column(TIMESTAMP, server_default=func.current_timestamp(), onupdate=func.current_timestamp())

    anime = relationship("Anime", back_populates="episodes")
    download_links = relationship("DownloadLink", back_populates="episode", cascade="all, delete-orphan")
    stream_links = relationship("StreamLink", back_populates="episode", cascade="all, delete-orphan")

class DownloadLink(Base):
    __tablename__ = "download_links"

    id = Column(Integer, primary_key=True, autoincrement=True)
    episode_id = Column(Integer, ForeignKey("episodes.id", ondelete="CASCADE", onupdate="CASCADE"), nullable=False)
    resolution = Column(String(50), nullable=False)
    provider = Column(String(100), nullable=False)
    url = Column(String(1000), nullable=False)
    created_at = Column(TIMESTAMP, server_default=func.current_timestamp())
    updated_at = Column(TIMESTAMP, server_default=func.current_timestamp(), onupdate=func.current_timestamp())

    episode = relationship("Episode", back_populates="download_links")

class StreamLink(Base):
    __tablename__ = "stream_links"

    id = Column(Integer, primary_key=True, autoincrement=True)
    episode_id = Column(Integer, ForeignKey("episodes.id", ondelete="CASCADE", onupdate="CASCADE"), nullable=False)
    provider = Column(String(100), nullable=False)
    url = Column(String(1000), nullable=False)
    created_at = Column(TIMESTAMP, server_default=func.current_timestamp())
    updated_at = Column(TIMESTAMP, server_default=func.current_timestamp(), onupdate=func.current_timestamp())

    episode = relationship("Episode", back_populates="stream_links")