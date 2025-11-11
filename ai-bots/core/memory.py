"""
AI Memory System - Learning & Adaptation
Simple but effective: persistent stats + heuristics
NO HEAVY ML - just smart updates and bandit-style selection
"""

import asyncio
from typing import Dict, List, Optional, Tuple
from datetime import datetime, timedelta
import math
import random
import structlog

from core.database import Database

logger = structlog.get_logger()


class AIMemory:
    """
    Persistent memory for AI learning
    Tracks relations, target performance, strategy success
    """
    
    def __init__(self, db: Database):
        self.db = db
        
        # In-memory cache for hot data (refreshed each tick)
        self._relation_cache: Dict[Tuple[int, int], float] = {}
        self._target_cache: Dict[Tuple[int, int], Dict] = {}
    
    async def initialize_schema(self):
        """Create AI memory tables if they don't exist"""
        
        # Relations memory
        await self.db.execute("""
            CREATE TABLE IF NOT EXISTS ai_relations (
                bot_player_id INT NOT NULL,
                other_player_id INT NOT NULL,
                score FLOAT DEFAULT 0,
                last_update TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (bot_player_id, other_player_id),
                INDEX idx_bot_player (bot_player_id),
                INDEX idx_score (bot_player_id, score)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        """)
        
        # Target statistics (farming/attack learning)
        await self.db.execute("""
            CREATE TABLE IF NOT EXISTS ai_target_stats (
                bot_player_id INT NOT NULL,
                target_village_id INT NOT NULL,
                attacks INT DEFAULT 0,
                successful_attacks INT DEFAULT 0,
                total_loot BIGINT DEFAULT 0,
                total_losses BIGINT DEFAULT 0,
                avg_payoff FLOAT DEFAULT 0,
                last_attack TIMESTAMP NULL,
                PRIMARY KEY (bot_player_id, target_village_id),
                INDEX idx_bot_payoff (bot_player_id, avg_payoff DESC)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        """)
        
        # Strategy performance memory
        await self.db.execute("""
            CREATE TABLE IF NOT EXISTS ai_strategy_stats (
                bot_player_id INT NOT NULL,
                strategy_key VARCHAR(64) NOT NULL,
                uses INT DEFAULT 0,
                success_score FLOAT DEFAULT 0,
                last_use TIMESTAMP NULL,
                PRIMARY KEY (bot_player_id, strategy_key),
                INDEX idx_bot_success (bot_player_id, success_score DESC)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        """)
        
        logger.info("ai_memory_schema_initialized")
    
    # ==========================================
    # RELATIONS: Friend/Neutral/Foe Learning
    # ==========================================
    
    async def update_relation(self, bot_id: int, other_id: int, delta: float, 
                             event_type: str = "interaction"):
        """
        Update relation score based on event
        Clamped to -100 (enemy) to +100 (close ally)
        """
        # Get current score
        current = await self.get_relation(bot_id, other_id)
        
        # Apply delta
        new_score = max(-100, min(100, current + delta))
        
        # Update DB
        await self.db.execute("""
            INSERT INTO ai_relations (bot_player_id, other_player_id, score, last_update)
            VALUES (:bot_id, :other_id, :score, NOW())
            ON DUPLICATE KEY UPDATE 
                score = :score,
                last_update = NOW()
        """, {
            'bot_id': bot_id,
            'other_id': other_id,
            'score': new_score
        })
        
        # Update cache
        self._relation_cache[(bot_id, other_id)] = new_score
        
        logger.info("relation_updated",
                   bot_id=bot_id,
                   other_id=other_id,
                   event=event_type,
                   delta=delta,
                   old_score=current,
                   new_score=new_score)
    
    async def get_relation(self, bot_id: int, other_id: int) -> float:
        """Get relation score (default 0 if never interacted)"""
        # Check cache first
        cache_key = (bot_id, other_id)
        if cache_key in self._relation_cache:
            return self._relation_cache[cache_key]
        
        # Query DB
        result = await self.db.fetch_one("""
            SELECT score FROM ai_relations
            WHERE bot_player_id = :bot_id AND other_player_id = :other_id
        """, {'bot_id': bot_id, 'other_id': other_id})
        
        score = result['score'] if result else 0.0
        
        # Cache it
        self._relation_cache[cache_key] = score
        return score
    
    async def get_all_relations(self, bot_id: int) -> Dict[int, float]:
        """Get all relations for a bot"""
        results = await self.db.fetch_all("""
            SELECT other_player_id, score
            FROM ai_relations
            WHERE bot_player_id = :bot_id
        """, {'bot_id': bot_id})
        
        return {row['other_player_id']: row['score'] for row in results}
    
    async def process_relation_event(self, bot_id: int, other_id: int, event_type: str):
        """
        Process specific relation events with predefined deltas
        """
        event_deltas = {
            # Positive events
            'received_support': +8,
            'same_tribe': +10,
            'resource_gift': +5,
            'saved_from_attack': +15,
            
            # Negative events
            'received_attack': -8,
            'received_farm': -3,
            'lost_village_to': -20,
            'betrayed_by': -30,
            
            # Neutral/minor
            'traded_with': +2,
            'scouted_by': -1,
        }
        
        delta = event_deltas.get(event_type, 0)
        if delta != 0:
            await self.update_relation(bot_id, other_id, delta, event_type)
    
    # ==========================================
    # TARGET STATS: Learning Good/Bad Farms
    # ==========================================
    
    async def record_attack_result(self, bot_id: int, target_village_id: int,
                                   loot: Dict[str, int], losses: Dict[str, int],
                                   success: bool):
        """
        Record attack outcome and compute payoff
        This is how AI learns which targets are profitable
        """
        # Calculate total loot value
        total_loot = loot.get('wood', 0) + loot.get('clay', 0) + loot.get('iron', 0)
        
        # Calculate loss value (rough unit costs)
        unit_costs = {
            'spear': 50, 'sword': 100, 'axe': 100, 'archer': 100,
            'light': 250, 'heavy': 500, 'ram': 300, 'catapult': 400
        }
        
        total_losses = sum(
            losses.get(unit, 0) * unit_costs.get(unit, 50)
            for unit in losses
        )
        
        # Compute payoff (profit - cost)
        payoff = total_loot - total_losses
        
        # Get current stats
        current = await self.db.fetch_one("""
            SELECT attacks, successful_attacks, total_loot, total_losses, avg_payoff
            FROM ai_target_stats
            WHERE bot_player_id = :bot_id AND target_village_id = :target_id
        """, {'bot_id': bot_id, 'target_id': target_village_id})
        
        if current:
            new_attacks = current['attacks'] + 1
            new_successful = current['successful_attacks'] + (1 if success else 0)
            new_total_loot = current['total_loot'] + total_loot
            new_total_losses = current['total_losses'] + total_losses
            
            # Exponential moving average for payoff (gives more weight to recent)
            alpha = 0.3  # Learning rate
            new_avg_payoff = (alpha * payoff + 
                            (1 - alpha) * current['avg_payoff'])
        else:
            new_attacks = 1
            new_successful = 1 if success else 0
            new_total_loot = total_loot
            new_total_losses = total_losses
            new_avg_payoff = payoff
        
        # Update DB
        await self.db.execute("""
            INSERT INTO ai_target_stats 
            (bot_player_id, target_village_id, attacks, successful_attacks,
             total_loot, total_losses, avg_payoff, last_attack)
            VALUES (:bot_id, :target_id, :attacks, :successful, 
                    :loot, :losses, :payoff, NOW())
            ON DUPLICATE KEY UPDATE
                attacks = :attacks,
                successful_attacks = :successful,
                total_loot = :loot,
                total_losses = :losses,
                avg_payoff = :payoff,
                last_attack = NOW()
        """, {
            'bot_id': bot_id,
            'target_id': target_village_id,
            'attacks': new_attacks,
            'successful': new_successful,
            'loot': new_total_loot,
            'losses': new_total_losses,
            'payoff': new_avg_payoff
        })
        
        logger.info("attack_result_recorded",
                   bot_id=bot_id,
                   target=target_village_id,
                   success=success,
                   loot=total_loot,
                   losses=total_losses,
                   payoff=payoff,
                   avg_payoff=new_avg_payoff)
    
    async def get_target_score(self, bot_id: int, target_village_id: int) -> float:
        """
        Get learned score for a target
        Higher = better farm, Lower = avoid/trap
        """
        result = await self.db.fetch_one("""
            SELECT avg_payoff, attacks, last_attack
            FROM ai_target_stats
            WHERE bot_player_id = :bot_id AND target_village_id = :target_id
        """, {'bot_id': bot_id, 'target_id': target_village_id})
        
        if not result:
            return 0.0  # Unknown target
        
        payoff = result['avg_payoff']
        attacks = result['attacks']
        last_attack = result['last_attack']
        
        # Apply decay if not attacked recently (targets change over time)
        if last_attack:
            days_ago = (datetime.now() - last_attack).days
            decay = math.exp(-0.1 * days_ago)  # Exponential decay
            payoff *= decay
        
        # Bonus for proven targets (attacked multiple times successfully)
        confidence_bonus = min(1.0, attacks / 10)  # Max at 10 attacks
        
        return payoff * (1 + confidence_bonus * 0.2)
    
    async def get_best_targets(self, bot_id: int, limit: int = 20) -> List[Dict]:
        """Get top-performing targets for this bot"""
        results = await self.db.fetch_all("""
            SELECT target_village_id, avg_payoff, attacks, successful_attacks
            FROM ai_target_stats
            WHERE bot_player_id = :bot_id
            ORDER BY avg_payoff DESC
            LIMIT :limit
        """, {'bot_id': bot_id, 'limit': limit})
        
        return [dict(row) for row in results]
    
    # ==========================================
    # STRATEGY STATS: Learning What Works
    # ==========================================
    
    async def update_strategy_performance(self, bot_id: int, strategy_key: str,
                                         success_score: float):
        """
        Update strategy performance based on outcome
        Success score computed from: villages gained, points gained, K/D ratio, survival
        """
        # Get current
        current = await self.db.fetch_one("""
            SELECT uses, success_score
            FROM ai_strategy_stats
            WHERE bot_player_id = :bot_id AND strategy_key = :strategy
        """, {'bot_id': bot_id, 'strategy': strategy_key})
        
        if current:
            new_uses = current['uses'] + 1
            # Exponential moving average
            alpha = 0.2
            new_score = (alpha * success_score + 
                        (1 - alpha) * current['success_score'])
        else:
            new_uses = 1
            new_score = success_score
        
        # Update DB
        await self.db.execute("""
            INSERT INTO ai_strategy_stats
            (bot_player_id, strategy_key, uses, success_score, last_use)
            VALUES (:bot_id, :strategy, :uses, :score, NOW())
            ON DUPLICATE KEY UPDATE
                uses = :uses,
                success_score = :score,
                last_use = NOW()
        """, {
            'bot_id': bot_id,
            'strategy': strategy_key,
            'uses': new_uses,
            'score': new_score
        })
        
        logger.info("strategy_performance_updated",
                   bot_id=bot_id,
                   strategy=strategy_key,
                   success_score=success_score,
                   avg_score=new_score)
    
    async def select_strategy(self, bot_id: int, available_strategies: List[str],
                            exploration_rate: float = 0.2) -> str:
        """
        Select strategy using epsilon-greedy bandit
        Mostly exploit (pick best), sometimes explore (try others)
        """
        # Random exploration
        if random.random() < exploration_rate:
            return random.choice(available_strategies)
        
        # Exploit: pick best known strategy
        results = await self.db.fetch_all("""
            SELECT strategy_key, success_score
            FROM ai_strategy_stats
            WHERE bot_player_id = :bot_id
            AND strategy_key IN :strategies
            ORDER BY success_score DESC
            LIMIT 1
        """, {
            'bot_id': bot_id,
            'strategies': tuple(available_strategies)
        })
        
        if results:
            return results[0]['strategy_key']
        else:
            # No history, random choice
            return random.choice(available_strategies)
    
    async def get_strategy_scores(self, bot_id: int) -> Dict[str, float]:
        """Get all strategy scores for debugging/monitoring"""
        results = await self.db.fetch_all("""
            SELECT strategy_key, success_score, uses
            FROM ai_strategy_stats
            WHERE bot_player_id = :bot_id
        """, {'bot_id': bot_id})
        
        return {
            row['strategy_key']: {
                'score': row['success_score'],
                'uses': row['uses']
            }
            for row in results
        }
    
    # ==========================================
    # UTILITY: Cache Management
    # ==========================================
    
    async def refresh_caches(self, bot_id: int):
        """Refresh in-memory caches (called each tick)"""
        # Reload relations
        relations = await self.get_all_relations(bot_id)
        for other_id, score in relations.items():
            self._relation_cache[(bot_id, other_id)] = score
    
    def clear_caches(self):
        """Clear all caches"""
        self._relation_cache.clear()
        self._target_cache.clear()
