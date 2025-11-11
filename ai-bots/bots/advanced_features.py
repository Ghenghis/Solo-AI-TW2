"""
7 Essential AI Features - Simple But Game-Changing
NOW WITH MEMORY INTEGRATION - Learns, remembers, adapts
"""

import random
from typing import List, Dict, Tuple, Optional
from datetime import datetime, time, timedelta
import structlog

from bots.state import AIBotState, VillageState, Decision
from bots.personalities_enhanced import PersonalityProfile
from core.world import WorldSnapshot, VillageInfo
from core.memory import AIMemory  # ← WIRE IN MEMORY

logger = structlog.get_logger()


# ==========================================
# FEATURE 1: Scouting (Intel Gathering)
# ==========================================

class ScoutingPlanner:
    """
    Send scouts before attacks to gather intel
    NOW WITH MEMORY: Only scout high-value or uncertain targets
    """
    
    @staticmethod
    async def plan_scouting(bot: AIBotState, village: VillageState,
                           personality: PersonalityProfile, world: WorldSnapshot,
                           memory: AIMemory) -> List[Decision]:
        """
        Scout potential targets before attacking
        """
        decisions = []
        
        # Need scouts available
        if village.units.spy < 5:
            return decisions
        
        # Find unscouted nearby targets
        nearby = world.get_nearby_villages(village.x, village.y, radius=25)
        
        for target in nearby[:5]:
            # Skip barbs (known weak)
            if target.is_barb:
                continue
            
            # ✅ USE MEMORY: Skip allies (learned relations)
            if target.owner_id:
                relation = await memory.get_relation(bot.player_id, target.owner_id)
                if relation >= 40:  # Ally threshold
                    continue
            
            # ✅ USE MEMORY: Prioritize uncertain targets
            target_stats = await memory.get_target_score(bot.player_id, target.id)
            
            # Skip if recently scouted AND we have good data
            if target.last_scouted and (datetime.now() - target.last_scouted).days < 3:
                if target_stats != 0:  # We have data
                    continue
            
            # Send 1-2 scouts
            scout_count = random.randint(1, 2)
            
            decisions.append(Decision(
                action_type='scout',
                village_id=village.id,
                priority=0.6,
                details={
                    'to_village': target.id,
                    'units': {'spy': scout_count},
                    'purpose': 'intel_gathering'
                }
            ))
            
            # One scout mission per tick
            break
        
        return decisions


# ==========================================
# FEATURE 2: Resource Trading (Market)
# ==========================================

class TradingPlanner:
    """
    Trade surplus resources for needed resources
    Simple market optimization
    """
    
    @staticmethod
    def plan_trading(bot: AIBotState, village: VillageState,
                    personality: PersonalityProfile, world: WorldSnapshot) -> List[Decision]:
        """
        Trade surplus resources at market
        """
        decisions = []
        
        # Need market building
        if village.buildings.get('market', 0) < 1:
            return decisions
        
        # Check for imbalanced resources
        wood = village.wood
        clay = village.clay
        iron = village.iron
        avg = (wood + clay + iron) / 3
        
        # Find surplus (>50% above average)
        surplus = None
        needed = None
        
        if wood > avg * 1.5:
            surplus = 'wood'
        elif clay > avg * 1.5:
            surplus = 'clay'
        elif iron > avg * 1.5:
            surplus = 'iron'
        
        # Find deficit (<50% of average)
        if wood < avg * 0.5:
            needed = 'wood'
        elif clay < avg * 0.5:
            needed = 'clay'
        elif iron < avg * 0.5:
            needed = 'iron'
        
        # Execute trade if both exist
        if surplus and needed and surplus != needed:
            # Trade 500 surplus for needed
            amount = min(500, getattr(village, surplus) - 500)
            
            if amount > 100:
                decisions.append(Decision(
                    action_type='trade',
                    village_id=village.id,
                    priority=0.7,
                    details={
                        'sell': surplus,
                        'buy': needed,
                        'amount': amount
                    }
                ))
        
        return decisions


# ==========================================
# FEATURE 3: Timed Attacks (Coordination)
# ==========================================

class TimedAttackPlanner:
    """
    Coordinate attacks from multiple villages to land simultaneously
    Game-changer for taking down strong targets
    """
    
    @staticmethod
    def plan_timed_attack(bot: AIBotState, target_village_id: int,
                         world: WorldSnapshot) -> List[Decision]:
        """
        Plan coordinated attack from multiple villages
        """
        decisions = []
        
        target = world.get_village(target_village_id)
        if not target:
            return decisions
        
        # Find all bot's villages with offensive power
        attack_villages = [
            v for v in bot.own_villages
            if v.units.offensive_power > 300
        ]
        
        if len(attack_villages) < 2:
            return decisions  # Need at least 2 villages
        
        # Calculate travel times from each village
        attacks = []
        for village in attack_villages[:3]:  # Max 3 villages
            distance = ((village.x - target.x) ** 2 + 
                       (village.y - target.y) ** 2) ** 0.5
            
            # Assume speed of light cavalry (10 min per field)
            travel_minutes = distance * 10
            
            attacks.append({
                'village': village,
                'travel_time': travel_minutes,
                'units': {
                    'axe': int(village.units.axe * 0.7),
                    'light': int(village.units.light * 0.7)
                }
            })
        
        # Find slowest attack (this determines when others launch)
        max_travel = max(a['travel_time'] for a in attacks)
        
        # Schedule attacks to arrive together
        for attack in attacks:
            delay = max_travel - attack['travel_time']
            
            decisions.append(Decision(
                action_type='timed_attack',
                village_id=attack['village'].id,
                priority=0.9,
                details={
                    'to_village': target_village_id,
                    'units': attack['units'],
                    'delay_minutes': delay,
                    'coordinated': True,
                    'attack_group': target_village_id  # Group ID
                }
            ))
        
        return decisions


# ==========================================
# FEATURE 4: Village Specialization
# ==========================================

class VillageSpecializer:
    """
    Designate villages for specific roles
    NOW SMART: Uses actual enemy positions, not hardcoded coords
    """
    
    @staticmethod
    async def assign_village_roles(bot: AIBotState, world: WorldSnapshot, 
                                   memory: AIMemory):
        """
        Assign roles: offense, defense, farm, noble
        ✅ IMPROVED: Calculate frontline from actual enemies
        """
        if len(bot.own_villages) < 2:
            # Single village = balanced
            if bot.own_villages:
                bot.own_villages[0].role = 'balanced'
            return
        
        villages = bot.own_villages
        
        # ✅ SMART: Calculate "frontline" from enemy positions
        all_relations = await memory.get_all_relations(bot.player_id)
        enemies = [pid for pid, score in all_relations.items() if score < -20]
        
        # Find average enemy position
        if enemies:
            enemy_villages = []
            for enemy_id in enemies:
                enemy_villages.extend(world.get_player_villages(enemy_id))
            
            if enemy_villages:
                avg_enemy_x = sum(v.x for v in enemy_villages) / len(enemy_villages)
                avg_enemy_y = sum(v.y for v in enemy_villages) / len(enemy_villages)
            else:
                # Fallback: Use world stats
                avg_enemy_x = 500
                avg_enemy_y = 500
        else:
            # No enemies yet, use geographic center
            all_villages = world.villages.values()
            avg_enemy_x = sum(v.x for v in all_villages) / max(1, len(all_villages))
            avg_enemy_y = sum(v.y for v in all_villages) / max(1, len(all_villages))
        
        # Sort by distance from enemy (frontline)
        for village in villages:
            village.distance_from_frontline = (
                (village.x - avg_enemy_x) ** 2 + 
                (village.y - avg_enemy_y) ** 2
            ) ** 0.5
        
        # Sort: Farthest from enemies = safest (core)
        villages.sort(key=lambda v: v.distance_from_frontline, reverse=True)
        
        # Assign roles
        # Safe villages (far from enemies) = offense + noble
        # Dangerous villages (near enemies) = defense
        safe_villages = villages[:len(villages)//2]
        frontline_villages = villages[len(villages)//2:]
        
        for i, village in enumerate(safe_villages):
            if i == 0 and len(villages) > 3:
                village.role = 'noble'  # Safest = noble factory
            else:
                village.role = 'offense'
        
        for village in frontline_villages:
            village.role = 'defense'  # Frontline = defensive
        
        # Designate 1-2 dedicated farm villages
        if len(villages) > 4:
            # Pick villages with best farm access (near barbs)
            for village in villages[:2]:
                nearby_barbs = len(world.get_farmable_barbs(village.x, village.y, 20))
                if nearby_barbs > 10:
                    village.role = 'farm'
        
        logger.info("village_roles_assigned",
                   bot=bot.name,
                   roles={v.name: v.role for v in villages})


# ==========================================
# FEATURE 5: Night Bonus Timing
# ==========================================

class NightBonusPlanner:
    """
    Attack during night bonus (100% more loot!)
    Simple time check, massive payoff
    """
    
    @staticmethod
    def is_night_bonus() -> bool:
        """
        Check if current time is night bonus period
        Typically: 00:00 - 08:00 server time
        """
        now = datetime.now()
        current_time = now.time()
        
        # Night bonus: midnight to 8am
        night_start = time(0, 0)
        night_end = time(8, 0)
        
        return night_start <= current_time <= night_end
    
    @staticmethod
    def should_wait_for_night(personality: PersonalityProfile) -> bool:
        """
        Decide if we should wait for night bonus
        """
        # Aggressive personalities don't wait
        if personality.aggression > 0.8:
            return False
        
        # Economic personalities always wait
        if personality.eco_focus > 0.7:
            return True
        
        # Others wait 60% of the time
        return random.random() < 0.6
    
    @staticmethod
    def adjust_farm_priority(base_priority: float, personality: PersonalityProfile) -> float:
        """
        Boost farming priority during night bonus
        """
        if NightBonusPlanner.is_night_bonus():
            return base_priority * 1.5  # 50% boost
        elif NightBonusPlanner.should_wait_for_night(personality):
            return base_priority * 0.3  # Wait for night
        else:
            return base_priority


# ==========================================
# FEATURE 6: Return Attack Prevention
# ==========================================

class DefensiveReservePlanner:
    """
    Never send ALL units - keep defensive reserve
    Prevents easy retaliation
    """
    
    @staticmethod
    def calculate_safe_attack_size(village: VillageState, 
                                   desired_units: Dict[str, int]) -> Dict[str, int]:
        """
        Reduce attack size to keep defensive reserve
        ✅ FIXED: Reserve ratio per unit type, not reused
        """
        safe_units = {}
        
        for unit_type, desired_count in desired_units.items():
            current_count = getattr(village.units, unit_type, 0)
            
            # ✅ FIX: Calculate reserve per unit type
            if unit_type in ['spear', 'sword', 'heavy', 'archer']:
                # Defensive units: keep 40%
                reserve_ratio = 0.4
            else:
                # Offensive units: keep 20-30%
                reserve_ratio = random.uniform(0.2, 0.3)
            
            max_sendable = int(current_count * (1 - reserve_ratio))
            safe_units[unit_type] = min(desired_count, max_sendable)
        
        return safe_units
    
    @staticmethod
    def ensure_minimum_defense(village: VillageState) -> bool:
        """
        Check if village has minimum defensive units
        """
        defensive_power = village.units.defensive_power
        
        # Minimum: 50 spears or equivalent
        return defensive_power >= 1000


# ==========================================
# FEATURE 7: Resource Balancing
# ==========================================

class ResourceBalancer:
    """
    Send resources between own villages
    Optimize resource usage across empire
    """
    
    @staticmethod
    def plan_resource_transfers(bot: AIBotState, 
                               world: WorldSnapshot) -> List[Decision]:
        """
        Balance resources between bot's villages
        """
        decisions = []
        
        if len(bot.own_villages) < 2:
            return decisions
        
        villages = bot.own_villages
        
        # Calculate resource needs
        for village in villages:
            village.resource_balance = (
                village.wood + village.clay + village.iron - 
                village.storage * 0.5  # Target: 50% full
            )
        
        # Sort: most surplus first, most deficit last
        villages.sort(key=lambda v: v.resource_balance, reverse=True)
        
        surplus_villages = [v for v in villages if v.resource_balance > 500]
        deficit_villages = [v for v in villages if v.resource_balance < -500]
        
        # Match surplus with deficit
        for surplus in surplus_villages[:2]:
            for deficit in deficit_villages[:2]:
                # Check distance
                distance = ((surplus.x - deficit.x) ** 2 + 
                          (surplus.y - deficit.y) ** 2) ** 0.5
                
                if distance < 20:  # Close enough
                    # Send balanced resources
                    amount_per_resource = min(
                        500,
                        surplus.wood - 1000,  # Keep some reserve
                        surplus.clay - 1000,
                        surplus.iron - 1000
                    )
                    
                    if amount_per_resource > 100:
                        decisions.append(Decision(
                            action_type='send_resources',
                            village_id=surplus.id,
                            priority=0.65,
                            details={
                                'to_village': deficit.id,
                                'resources': {
                                    'wood': amount_per_resource,
                                    'clay': amount_per_resource,
                                    'iron': amount_per_resource
                                },
                                'internal_transfer': True
                            }
                        ))
                        
                        # One transfer per tick
                        return decisions
        
        return decisions


# ==========================================
# Integration Helper
# ==========================================

class AdvancedFeaturesIntegrator:
    """
    Integrate all 7 features into bot tick
    ✅ NOW WITH MEMORY INTEGRATION
    """
    
    @staticmethod
    async def run_advanced_features(bot: AIBotState, village: VillageState,
                                    personality: PersonalityProfile, 
                                    world: WorldSnapshot,
                                    memory: AIMemory) -> List[Decision]:
        """
        Run all advanced features and collect decisions
        """
        decisions = []
        
        # 1. Scouting (intel gathering) ✅ WITH MEMORY
        if personality.aggression > 0.4:  # Aggressive bots scout more
            decisions.extend(
                await ScoutingPlanner.plan_scouting(bot, village, personality, world, memory)
            )
        
        # 2. Resource Trading
        if village.buildings.get('market', 0) > 0:
            decisions.extend(
                TradingPlanner.plan_trading(bot, village, personality, world)
            )
        
        # 3. Village Specialization (run once per bot, not per village) ✅ WITH MEMORY
        if village == bot.own_villages[0]:  # First village only
            await VillageSpecializer.assign_village_roles(bot, world, memory)
        
        # 4. Resource Balancing (bot-level, not village)
        if village == bot.own_villages[0] and len(bot.own_villages) > 1:
            decisions.extend(
                ResourceBalancer.plan_resource_transfers(bot, world)
            )
        
        return decisions
    
    @staticmethod
    def adjust_attack_with_features(decision: Decision, village: VillageState,
                                    personality: PersonalityProfile) -> Decision:
        """
        Apply advanced features to attack decisions
        """
        if decision.action_type not in ['attack', 'timed_attack']:
            return decision
        
        # Feature 5: Night Bonus Timing
        if decision.details.get('is_farm'):
            # Adjust priority based on night bonus
            decision.priority = NightBonusPlanner.adjust_farm_priority(
                decision.priority,
                personality
            )
        
        # Feature 6: Defensive Reserve
        desired_units = decision.details.get('units', {})
        safe_units = DefensiveReservePlanner.calculate_safe_attack_size(
            village,
            desired_units
        )
        decision.details['units'] = safe_units
        
        # Check minimum defense
        if not DefensiveReservePlanner.ensure_minimum_defense(village):
            # Cancel attack if defense too weak
            decision.priority = 0.0
            logger.warn("attack_cancelled_insufficient_defense",
                       village=village.name)
        
        return decision


# ==========================================
# Usage Summary
# ==========================================

"""
INTEGRATION INTO MAIN BRAIN:

from bots.advanced_features import AdvancedFeaturesIntegrator

async def run_bot_tick(bot, world, game_client, db, config):
    for village in bot.own_villages:
        # ... existing planners ...
        
        # Add advanced features
        advanced_decisions = await AdvancedFeaturesIntegrator.run_advanced_features(
            bot, village, personality, world
        )
        all_decisions.extend(advanced_decisions)
        
        # Apply features to attack decisions
        for decision in all_decisions:
            decision = AdvancedFeaturesIntegrator.adjust_attack_with_features(
                decision, village, personality
            )
"""
