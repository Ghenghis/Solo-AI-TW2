#!/usr/bin/env python3
"""
TWLan AI Bot Orchestrator - Enterprise Grade
Manages multiple AI personalities playing the game authentically
"""

import asyncio
import logging
import signal
import sys
from typing import List, Dict
from concurrent.futures import ThreadPoolExecutor
from datetime import datetime

import structlog
from prometheus_client import start_http_server, Counter, Gauge, Histogram

from core.config import Config
from core.database import Database
from core.game_client import GameClient
from bots.bot_manager import BotManager
from bots.personalities import PersonalityFactory
from strategies.war_engine import WarEngine
from strategies.economy_engine import EconomyEngine
from strategies.diplomacy_engine import DiplomacyEngine

# Configure structured logging
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

# Prometheus Metrics
bot_decisions = Counter('bot_decisions_total', 'Total decisions made', ['personality', 'action_type'])
active_bots = Gauge('active_bots', 'Number of active bots', ['personality'])
decision_time = Histogram('decision_time_seconds', 'Time spent making decisions')
http_requests = Counter('http_requests_total', 'HTTP requests to game', ['method', 'endpoint', 'status'])
database_queries = Counter('database_queries_total', 'Database queries executed', ['operation'])


class AIOrchestrator:
    """Main orchestrator managing all AI bot operations"""
    
    def __init__(self, config: Config):
        self.config = config
        self.db = Database(config)
        self.game_client = GameClient(config)
        self.bot_manager = BotManager(config, self.db, self.game_client)
        
        self.war_engine = WarEngine(config, self.db)
        self.economy_engine = EconomyEngine(config, self.db)
        self.diplomacy_engine = DiplomacyEngine(config, self.db)
        
        self.running = False
        self.executor = ThreadPoolExecutor(max_workers=config.max_concurrent_bots)
        
        logger.info("orchestrator_initialized", bot_count=config.bot_count)
    
    async def initialize_bots(self):
        """Initialize all AI bot accounts"""
        logger.info("initializing_bots", count=self.config.bot_count)
        
        # Create or load bot accounts
        bots = await self.bot_manager.initialize_bots()
        
        # Assign personalities based on configuration
        personality_counts = {
            'warmonger': self.config.personality_warmonger,
            'turtle': self.config.personality_turtle,
            'balanced': self.config.personality_balanced,
            'diplomat': self.config.personality_diplomat,
            'chaos': self.config.personality_chaos,
        }
        
        assigned = 0
        for personality_type, percentage in personality_counts.items():
            count = int(self.config.bot_count * (percentage / 100))
            for i in range(count):
                if assigned < len(bots):
                    bot = bots[assigned]
                    personality = PersonalityFactory.create(personality_type, self.config)
                    bot.set_personality(personality)
                    assigned += 1
                    
                    active_bots.labels(personality=personality_type).inc()
        
        logger.info("bots_initialized", 
                   total=assigned,
                   personalities=personality_counts)
        
        return bots
    
    async def run_bot_cycle(self, bot):
        """Execute one decision cycle for a bot"""
        try:
            with decision_time.time():
                # 1. Analyze current state
                game_state = await bot.analyze_state()
                
                # 2. Make decisions based on personality
                decisions = await bot.make_decisions(game_state)
                
                # 3. Execute decisions via HTTP (like real player)
                for decision in decisions:
                    await self.execute_decision(bot, decision)
                    
                    # Track metrics
                    bot_decisions.labels(
                        personality=bot.personality.name,
                        action_type=decision.action_type
                    ).inc()
                
                # 4. Random delay to simulate human behavior
                await bot.human_like_delay()
                
        except Exception as e:
            logger.error("bot_cycle_error", 
                        bot_id=bot.id, 
                        error=str(e),
                        exc_info=True)
    
    async def execute_decision(self, bot, decision):
        """Execute a bot decision by making HTTP requests to the game"""
        try:
            if decision.action_type == 'build':
                await self.game_client.build_building(
                    bot.session,
                    village_id=decision.village_id,
                    building=decision.building
                )
            
            elif decision.action_type == 'recruit':
                await self.game_client.recruit_units(
                    bot.session,
                    village_id=decision.village_id,
                    units=decision.units
                )
            
            elif decision.action_type == 'attack':
                await self.game_client.send_attack(
                    bot.session,
                    from_village=decision.from_village,
                    to_village=decision.to_village,
                    units=decision.units
                )
            
            elif decision.action_type == 'support':
                await self.game_client.send_support(
                    bot.session,
                    from_village=decision.from_village,
                    to_village=decision.to_village,
                    units=decision.units
                )
            
            elif decision.action_type == 'diplomacy':
                await self.diplomacy_engine.execute_action(bot, decision)
            
            logger.info("decision_executed",
                       bot_id=bot.id,
                       action=decision.action_type,
                       details=decision.to_dict())
            
        except Exception as e:
            logger.error("decision_execution_failed",
                        bot_id=bot.id,
                        decision=decision.action_type,
                        error=str(e))
    
    async def run(self):
        """Main orchestrator loop"""
        self.running = True
        
        logger.info("orchestrator_starting")
        
        # Initialize all bots
        bots = await self.initialize_bots()
        
        # Start Prometheus metrics server
        if self.config.enable_metrics:
            start_http_server(self.config.metrics_port)
            logger.info("metrics_server_started", port=self.config.metrics_port)
        
        # Main loop
        cycle = 0
        while self.running:
            cycle += 1
            cycle_start = datetime.now()
            
            logger.info("cycle_start", cycle=cycle, bots=len(bots))
            
            # Run all bots concurrently (with limit)
            tasks = []
            for bot in bots:
                task = asyncio.create_task(self.run_bot_cycle(bot))
                tasks.append(task)
                
                # Limit concurrent execution
                if len(tasks) >= self.config.max_concurrent_bots:
                    await asyncio.gather(*tasks)
                    tasks = []
            
            # Wait for remaining tasks
            if tasks:
                await asyncio.gather(*tasks)
            
            # Calculate cycle time
            cycle_time = (datetime.now() - cycle_start).total_seconds()
            logger.info("cycle_complete", 
                       cycle=cycle,
                       duration_seconds=cycle_time)
            
            # Wait for next cycle
            wait_time = max(0, self.config.bot_tick_rate - cycle_time)
            if wait_time > 0:
                await asyncio.sleep(wait_time)
    
    async def shutdown(self):
        """Graceful shutdown"""
        logger.info("orchestrator_shutting_down")
        self.running = False
        
        # Close all bot sessions
        await self.bot_manager.close_all_sessions()
        
        # Close database connections
        await self.db.close()
        
        # Shutdown executor
        self.executor.shutdown(wait=True)
        
        logger.info("orchestrator_shutdown_complete")


def signal_handler(signum, frame):
    """Handle shutdown signals"""
    logger.info("shutdown_signal_received", signal=signum)
    sys.exit(0)


async def main():
    """Entry point"""
    # Load configuration
    config = Config.from_env()
    
    # Setup signal handlers
    signal.signal(signal.SIGINT, signal_handler)
    signal.signal(signal.SIGTERM, signal_handler)
    
    # Create and run orchestrator
    orchestrator = AIOrchestrator(config)
    
    try:
        await orchestrator.run()
    except KeyboardInterrupt:
        logger.info("keyboard_interrupt_received")
    finally:
        await orchestrator.shutdown()


if __name__ == "__main__":
    asyncio.run(main())
