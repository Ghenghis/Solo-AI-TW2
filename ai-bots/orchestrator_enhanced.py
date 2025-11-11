#!/usr/bin/env python3
"""
Enhanced TWLan AI Orchestrator - No Hand-Waving Edition
Concrete implementation with proper tick loop, state management, and decision execution
"""

import asyncio
import signal
import sys
from typing import List
from datetime import datetime

import structlog
from prometheus_client import start_http_server

from core.config import Config
from core.database import Database
from core.world import WorldSnapshot
from core.game_client import GameClient
from core.memory import AIMemory
from bots.state import AIBotState, VillageState, UnitComposition
from bots.personalities_enhanced import get_personality, PERSONALITIES
from bots.brain import run_bot_tick

# Configure logging
structlog.configure(
    processors=[
        structlog.processors.TimeStamper(fmt="iso"),
        structlog.stdlib.add_log_level,
        structlog.processors.StackInfoRenderer(),
        structlog.processors.format_exc_info,
        structlog.processors.JSONRenderer()
    ],
    logger_factory=structlog.stdlib.LoggerFactory(),
    cache_logger_on_first_use=True,
)

logger = structlog.get_logger()


async def load_bots(db: Database, config: Config) -> List[AIBotState]:
    """
    Load or create bot accounts
    Ensures BOT_COUNT bots exist in database
    """
    logger.info("loading_bots", target_count=config.bot_count)
    
    # Check existing bot accounts
    existing_bots = await db.fetch_all("""
        SELECT id_user, username, id_ally, points
        FROM users
        WHERE is_bot = 1 AND deleted = 0
        ORDER BY id_user
        LIMIT :limit
    """, {'limit': config.bot_count})
    
    bots = []
    
    for row in existing_bots:
        # Determine personality based on distribution
        personality_name = assign_personality(len(bots), config)
        
        bot = AIBotState(
            player_id=row['id_user'],
            name=f"AI-{row['username']}",
            username=row['username'],
            personality=personality_name,
            tribe_id=row.get('id_ally')
        )
        
        # Load villages
        villages_data = await db.fetch_all("""
            SELECT 
                v.id_village, v.name, v.x, v.y, v.points,
                v.wood, v.clay, v.iron, v.storage,
                v.pop, v.pop_max
            FROM villages v
            WHERE v.id_user = :user_id AND v.deleted = 0
        """, {'user_id': bot.player_id})
        
        for v_row in villages_data:
            village = VillageState(
                id=v_row['id_village'],
                name=v_row['name'],
                x=v_row['x'],
                y=v_row['y'],
                points=v_row['points'],
                wood=v_row.get('wood', 0),
                clay=v_row.get('clay', 0),
                iron=v_row.get('iron', 0),
                storage=v_row.get('storage', 1000),
                pop=v_row.get('pop', 0),
                pop_max=v_row.get('pop_max', 24)
            )
            
            # Load buildings
            buildings_data = await db.fetch_all("""
                SELECT building_name, level
                FROM village_buildings
                WHERE id_village = :village_id
            """, {'village_id': village.id})
            
            village.buildings = {
                row['building_name']: row['level'] 
                for row in buildings_data
            }
            
            # Load units
            units_data = await db.fetch_all("""
                SELECT unit_type, amount
                FROM village_units
                WHERE id_village = :village_id
            """, {'village_id': village.id})
            
            units = UnitComposition()
            for row in units_data:
                setattr(units, row['unit_type'], row['amount'])
            village.units = units
            
            bot.own_villages.append(village)
        
        bots.append(bot)
        logger.info("bot_loaded",
                   bot=bot.name,
                   personality=bot.personality,
                   villages=len(bot.own_villages))
    
    # Create missing bots if needed
    bots_to_create = config.bot_count - len(bots)
    if bots_to_create > 0:
        logger.info("creating_bots", count=bots_to_create)
        
        for i in range(bots_to_create):
            bot_num = len(bots) + 1
            personality_name = assign_personality(len(bots), config)
            
            # Create bot account in DB
            bot_id = await db.execute("""
                INSERT INTO users (username, password, email, is_bot, created_at)
                VALUES (:username, :password, :email, 1, NOW())
            """, {
                'username': f"AIBot{bot_num}",
                'password': 'unused',  # Bots don't login normally
                'email': f"bot{bot_num}@twlan.local"
            })
            
            # Create starting village
            # Note: In production, implement proper empty space finder
            # For now: distribute bots across map grid (500-600 range)
            start_x = 500 + (bot_num % 100)
            start_y = 500 + (bot_num // 100)
            
            village_id = await db.execute("""
                INSERT INTO villages 
                (id_user, name, x, y, wood, clay, iron, storage, pop, pop_max, created_at)
                VALUES (:user_id, :name, :x, :y, 500, 500, 500, 1000, 4, 24, NOW())
            """, {
                'user_id': bot_id,
                'name': f"AI Village {bot_num}",
                'x': start_x,
                'y': start_y
            })
            
            # Initialize basic buildings
            for building in ['headquarters', 'barracks', 'farm', 'warehouse']:
                await db.execute("""
                    INSERT INTO village_buildings (id_village, building_name, level)
                    VALUES (:village_id, :building, 1)
                """, {
                    'village_id': village_id,
                    'building': building
                })
            
            bot = AIBotState(
                player_id=bot_id,
                name=f"AI-Bot{bot_num}",
                username=f"AIBot{bot_num}",
                personality=personality_name
            )
            
            bots.append(bot)
    
    logger.info("bots_loaded", total=len(bots))
    return bots


def assign_personality(bot_index: int, config: Config) -> str:
    """
    Assign personality based on distribution percentages
    """
    personalities = [
        ('warmonger', config.personality_warmonger),
        ('turtle', config.personality_turtle),
        ('balanced', config.personality_balanced),
        ('diplomat', config.personality_diplomat),
        ('chaos', config.personality_chaos),
    ]
    
    # Calculate which personality this bot should be
    total = config.bot_count
    cumulative = 0
    
    for name, percentage in personalities:
        cumulative += int(total * (percentage / 100))
        if bot_index < cumulative:
            return name
    
    return 'balanced'  # Fallback


class EnhancedOrchestrator:
    """Main orchestrator - the brains of the operation"""
    
    def __init__(self, config: Config):
        self.config = config
        self.db = Database(config)
        self.game_client = GameClient(config)
        self.memory = AIMemory(self.db)  # ← Add memory system
        self.bots: List[AIBotState] = []
        self.running = False
    
    async def initialize(self):
        """Set up database, load/create bots"""
        logger.info("orchestrator_initializing")
        
        # Connect to database
        # await self.db.connect()  # Assumes Database has connect method
        
        # Initialize memory schema
        await self.memory.initialize_schema()
        logger.info("memory_system_initialized")
        
        # Load bots
        self.bots = await load_bots(self.db, self.config)
        
        # Start metrics server
        if self.config.enable_metrics:
            start_http_server(self.config.metrics_port)
            logger.info("metrics_server_started", port=self.config.metrics_port)
        
        logger.info("orchestrator_initialized", bots=len(self.bots))
    
    async def run(self):
        """
        Main loop - this is THE orchestrator tick
        No hand-waving, actual implementation
        """
        self.running = True
        cycle = 0
        
        logger.info("orchestrator_starting")
        
        while self.running:
            cycle += 1
            cycle_start = datetime.now()
            
            try:
                logger.info("cycle_start", cycle=cycle, bots=len(self.bots))
                
                # 1. Build world snapshot (query DB)
                world = await WorldSnapshot.build(self.db)
                
                # 2. Refresh bot village states from world
                for bot in self.bots:
                    # Update village data from world
                    for village in bot.own_villages:
                        world_village = world.get_village(village.id)
                        if world_village:
                            village.points = world_village.points
                
                # 3. Run all bots (with concurrency limit)
                tasks = []
                for bot in self.bots:
                    # ✅ Pass memory to bot tick for learning
                    task = run_bot_tick(bot, world, self.game_client, self.memory, self.db, self.config)
                    tasks.append(task)
                    
                    # Batch execution to avoid overwhelming server
                    if len(tasks) >= self.config.max_concurrent_bots:
                        await asyncio.gather(*tasks, return_exceptions=True)
                        tasks = []
                
                # Execute remaining
                if tasks:
                    await asyncio.gather(*tasks, return_exceptions=True)
                
                # 4. Calculate cycle time
                cycle_time = (datetime.now() - cycle_start).total_seconds()
                
                logger.info("cycle_complete",
                           cycle=cycle,
                           duration=cycle_time,
                           bots=len(self.bots))
                
                # 5. Wait for next tick
                wait_time = max(0, self.config.bot_tick_rate - cycle_time)
                if wait_time > 0:
                    await asyncio.sleep(wait_time)
                
            except Exception as e:
                logger.error("cycle_failed",
                            cycle=cycle,
                            error=str(e),
                            exc_info=True)
                # Continue despite errors
                await asyncio.sleep(10)
    
    async def shutdown(self):
        """Graceful shutdown"""
        logger.info("orchestrator_shutting_down")
        self.running = False
        
        # Close database
        await self.db.close()
        
        logger.info("orchestrator_shutdown_complete")


def signal_handler(signum, frame):
    """Handle shutdown signals"""
    logger.info("shutdown_signal_received", signal=signum)
    sys.exit(0)


async def main():
    """Entry point - loads config and runs orchestrator"""
    # Load configuration
    config = Config.from_env()
    config.validate()
    
    # Setup signal handlers
    signal.signal(signal.SIGINT, signal_handler)
    signal.signal(signal.SIGTERM, signal_handler)
    
    # Create and run orchestrator
    orchestrator = EnhancedOrchestrator(config)
    
    try:
        await orchestrator.initialize()
        await orchestrator.run()
    except KeyboardInterrupt:
        logger.info("keyboard_interrupt")
    except Exception as e:
        logger.error("orchestrator_fatal_error", error=str(e), exc_info=True)
    finally:
        await orchestrator.shutdown()


if __name__ == "__main__":
    asyncio.run(main())
