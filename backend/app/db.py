from functools import lru_cache
import os
from typing import AsyncGenerator

from pydantic import BaseSettings, Field
from sqlalchemy.ext.asyncio import AsyncSession, async_sessionmaker, create_async_engine
from sqlalchemy.orm import declarative_base


class Settings(BaseSettings):
    db_host: str = Field(default="localhost", env="APP_DB_HOST")
    db_port: int = Field(default=5400, env="APP_DB_PORT")
    db_name: str = Field(default="credimore", env="APP_DB_NAME")
    db_user: str = Field(default="postgres", env="APP_DB_USER")
    db_password: str = Field(default="posgres", env="APP_DB_PASSWORD")

    jwt_secret: str = Field(default="change-me", env="APP_JWT_SECRET")
    jwt_algorithm: str = Field(default="HS256", env="APP_JWT_ALGORITHM")
    jwt_expires_minutes: int = Field(default=120, env="APP_JWT_EXPIRES_MINUTES")

    class Config:
        env_file = os.getenv("APP_ENV_FILE", ".env")
        env_file_encoding = "utf-8"


@lru_cache()
def get_settings() -> Settings:
    return Settings()


def _build_database_url(settings: Settings) -> str:
    return (
        f"postgresql+asyncpg://{settings.db_user}:{settings.db_password}"
        f"@{settings.db_host}:{settings.db_port}/{settings.db_name}"
    )


settings = get_settings()
database_url = _build_database_url(settings)
engine = create_async_engine(database_url, future=True, echo=False)
AsyncSessionLocal = async_sessionmaker(engine, expire_on_commit=False)
Base = declarative_base()


async def get_session() -> AsyncGenerator[AsyncSession, None]:
    async with AsyncSessionLocal() as session:
        yield session
