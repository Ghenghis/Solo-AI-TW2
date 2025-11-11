"""
Database access layer for AI Bot Orchestrator
Direct MySQL/MariaDB queries with connection pooling
"""

import asyncio
from typing import List, Dict, Optional, Any
from contextlib import asynccontextmanager

import structlog
from sqlalchemy import create_engine, text
from sqlalchemy.pool import QueuePool
from sqlalchemy.ext.asyncio import create_async_engine, AsyncSession, async_sessionmaker

logger = structlog.get_logger()


class Database:
    """Enterprise-grade database access with connection pooling"""
    
    def __init__(self, config):
        self.config = config
        
        # Create async engine with connection pooling
        self.engine = create_async_engine(
            config.db_url.replace('mysql+mariadb://', 'mysql+aiomysql://'),
            pool_size=config.db_pool_size,
            max_overflow=10,
            pool_pre_ping=True,  # Verify connections before use
            pool_recycle=3600,   # Recycle connections every hour
            echo=False,
        )
        
        # Session factory
        self.async_session = async_sessionmaker(
            self.engine,
            class_=AsyncSession,
            expire_on_commit=False
        )
        
        logger.info("database_initialized", 
                   host=config.db_host,
                   database=config.db_name,
                   pool_size=config.db_pool_size)
    
    @asynccontextmanager
    async def session(self):
        """Context manager for database sessions"""
        async with self.async_session() as session:
            try:
                yield session
                await session.commit()
            except Exception:
                await session.rollback()
                raise
            finally:
                await session.close()
    
    async def execute(self, query: str, params: Optional[Dict] = None) -> Any:
        """Execute a query and return results"""
        async with self.session() as session:
            result = await session.execute(text(query), params or {})
            return result
    
    async def fetch_one(self, query: str, params: Optional[Dict] = None) -> Optional[Dict]:
        """Fetch single row"""
        result = await self.execute(query, params)
        row = result.fetchone()
        return dict(row._mapping) if row else None
    
    async def fetch_all(self, query: str, params: Optional[Dict] = None) -> List[Dict]:
        """Fetch all rows"""
        result = await self.execute(query, params)
        return [dict(row._mapping) for row in result.fetchall()]
    
    # ==========================================
    # Game-Specific Queries (Reverse Engineered)
    # ==========================================
    
    async def get_bot_villages(self, user_id: int) -> List[Dict]:
        """Get all villages owned by a bot"""
        query = """
            SELECT 
                id_village, name, x, y, points, 
                wood, stone, iron, storage,
                pop, pop_max
            FROM villages
            WHERE id_user = :user_id
            ORDER BY id_village
        """
        return await self.fetch_all(query, {'user_id': user_id})
    
    async def get_village_buildings(self, village_id: int) -> Dict[str, int]:
        """Get building levels for a village"""
        query = """
            SELECT building_name, level
            FROM village_buildings
            WHERE id_village = :village_id
        """
        rows = await self.fetch_all(query, {'village_id': village_id})
        return {row['building_name']: row['level'] for row in rows}
    
    async def get_village_units(self, village_id: int) -> Dict[str, int]:
        """Get unit counts for a village"""
        query = """
            SELECT unit_type, amount
            FROM village_units
            WHERE id_village = :village_id
        """
        rows = await self.fetch_all(query, {'village_id': village_id})
        return {row['unit_type']: row['amount'] for row in rows}
    
    async def get_nearby_villages(self, x: int, y: int, radius: int = 20) -> List[Dict]:
        """Find villages within radius for targeting"""
        query = """
            SELECT 
                v.id_village, v.name, v.x, v.y, v.points,
                v.id_user, u.username, u.id_ally
            FROM villages v
            LEFT JOIN users u ON v.id_user = u.id_user
            WHERE 
                v.x BETWEEN :x_min AND :x_max
                AND v.y BETWEEN :y_min AND :y_max
                AND v.id_village != :exclude_id
            ORDER BY 
                ((v.x - :x) * (v.x - :x) + (v.y - :y) * (v.y - :y))
            LIMIT 50
        """
        params = {
            'x': x, 'y': y,
            'x_min': x - radius, 'x_max': x + radius,
            'y_min': y - radius, 'y_max': y + radius,
            'exclude_id': 0  # Will be replaced by bot's own village
        }
        return await self.fetch_all(query, params)
    
    async def get_world_config(self) -> Dict:
        """Get world configuration settings"""
        query = "SELECT config_key, config_value FROM world_config"
        rows = await self.fetch_all(query)
        return {row['config_key']: row['config_value'] for row in rows}
    
    async def create_bot_account(self, username: str, password_hash: str) -> int:
        """Create a new bot user account"""
        async with self.session() as session:
            query = text("""
                INSERT INTO users (username, password, email, created_at)
                VALUES (:username, :password, :email, NOW())
            """)
            result = await session.execute(query, {
                'username': username,
                'password': password_hash,
                'email': f"{username}@twlan-bot.local"
            })
            await session.commit()
            return result.lastrowid
    
    async def create_bot_village(self, user_id: int, x: int, y: int, name: str) -> int:
        """Create starting village for a bot"""
        async with self.session() as session:
            query = text("""
                INSERT INTO villages 
                (id_user, name, x, y, wood, stone, iron, storage, pop, pop_max, created_at)
                VALUES 
                (:user_id, :name, :x, :y, 500, 500, 500, 1000, 4, 24, NOW())
            """)
            result = await session.execute(query, {
                'user_id': user_id,
                'name': name,
                'x': x,
                'y': y
            })
            await session.commit()
            return result.lastrowid
    
    async def get_reports(self, user_id: int, limit: int = 50) -> List[Dict]:
        """Get battle reports for analysis"""
        query = """
            SELECT 
                id_report, type, time, is_read,
                from_village_id, to_village_id,
                result, haul_wood, haul_stone, haul_iron
            FROM reports
            WHERE id_user = :user_id
            ORDER BY time DESC
            LIMIT :limit
        """
        return await self.fetch_all(query, {'user_id': user_id, 'limit': limit})
    
    async def close(self):
        """Close database connections"""
        await self.engine.dispose()
        logger.info("database_closed")
