"""
Learning-Enhanced Brain - Memory-Based Decision Making
Integrates AIMemory with decision planners for adaptive behavior
"""

from typing import List, Dict
from datetime import datetime
import random
import structlog

from bots.state import AIBotState, VillageState, Decision
from bots.personalities_enhanced import PersonalityProfile
from core.world import WorldSnapshot, VillageInfo
from core.memory import AIMemory

logger = structlog.get_logger()


class LearningFarmingPlanner:
    """
    Enhanced farming with memory
    Learns which targets are profitable and concentrates on them
    """
    
    @staticmethod
    async def plan_farming_with_memory(
        bot: AIBotState, village: VillageState,
        personality: PersonalityProfile, world: WorldSnapshot,
        memory: AIMemory
    ) -> List[Decision]:
        """
        Memory-enhanced farming:
        1. Prefer targets with high historical payoff
        2. Avoid targets with negative payoff (traps)
        3. Explore new targets occasionally
        """
        decisions = []
        
        # Need offensive power
        if village.units.offensive_power < 100:
            return decisions
        
        # Get learned best targets
        best_targets = await memory.get_best_targets(bot.player_id, limit=30)
        
        # Mix strategy: 70% exploit best, 30% explore new
        if best_targets and random.random() < 0.7:
            # Exploit: use proven targets
            for target_data in best_targets[:10]:
                target_id = target_data['target_village_id']
                target = world.get_village(target_id)
                
                if target and target.is_barb:
                    avg_payoff = target_data['avg_payoff']
                    
                    # Skip if this has become unprofitable
                    if avg_payoff < -100:
                        continue
                    
                    # Determine attack size based on past performance
                    if avg_payoff > 1000:
                        # Highly profitable, send smaller wave
                        farm_size = {'axe': 20, 'light': 3}
                    elif avg_payoff > 0:
                        # Profitable, normal wave
                        farm_size = {'axe': 30, 'light': 5}
                    else:
                        # Marginal, bigger wave to be safe
                        farm_size = {'axe': 50, 'light': 10}
                    
                    # Check we have units
                    if (village.units.axe >= farm_size['axe'] and
                        village.units.light >= farm_size['light']):
                        
                        priority = 0.8 * personality.farm_intensity
                        
                        decisions.append(Decision(
                            action_type='attack',
                            village_id=village.id,
                            priority=priority,
                            details={
                                'to_village': target_id,
                                'units': farm_size,
                                'is_farm': True,
                                'learned': True,
                                'expected_payoff': avg_payoff
                            }
                        ))
                        
                        # One farm per tick
                        return decisions
        
        # Explore: try new barb villages
        new_targets = world.get_farmable_barbs(village.x, village.y, radius=30)
        
        # Filter out targets we already know are bad
        for target in new_targets:
            target_score = await memory.get_target_score(bot.player_id, target.id)
            
            # Skip known bad targets
            if target_score < -200:
                continue
            
            # Try this new target
            farm_size = {'axe': 40, 'light': 8}  # Medium-sized exploration wave
            
            if (village.units.axe >= farm_size['axe'] and
                village.units.light >= farm_size['light']):
                
                priority = 0.7 * personality.farm_intensity
                
                decisions.append(Decision(
                    action_type='attack',
                    village_id=village.id,
                    priority=priority,
                    details={
                        'to_village': target.id,
                        'units': farm_size,
                        'is_farm': True,
                        'learned': False,
                        'exploring': True
                    }
                ))
                
                return decisions
        
        return decisions


class LearningAttackPlanner:
    """
    Memory-enhanced attacks
    Uses relations to pick targets and coordinate
    """
    
    @staticmethod
    async def plan_attacks_with_memory(
        bot: AIBotState, village: VillageState,
        personality: PersonalityProfile, world: WorldSnapshot,
        memory: AIMemory
    ) -> List[Decision]:
        """
        Memory-based targeting:
        1. Prioritize players with negative relations
        2. Coordinate with allies (implicit via relations)
        3. Learn who's too strong to attack
        """
        decisions = []
        
        # Need army
        if village.units.offensive_power < 500:
            return decisions
        
        # Get all relations
        all_relations = await memory.get_all_relations(bot.player_id)
        
        # Find enemies (negative relations)
        enemies = [
            (player_id, score)
            for player_id, score in all_relations.items()
            if score < -20  # Hostile threshold
        ]
        
        # Sort by most hostile
        enemies.sort(key=lambda x: x[1])
        
        # Attack top enemies
        for enemy_id, relation_score in enemies[:3]:
            enemy_villages = world.get_player_villages(enemy_id)
            
            if not enemy_villages:
                continue
            
            # Pick closest village
            target = min(
                enemy_villages,
                key=lambda v: ((v.x - village.x) ** 2 + (v.y - village.y) ** 2)
            )
            
            # Check target's past performance (if we've attacked before)
            target_score = await memory.get_target_score(bot.player_id, target.id)
            
            # If we've lost badly here before, skip or send bigger force
            if target_score < -500:
                continue  # Too strong, avoid
            
            # Determine attack size
            if target_score < 0:
                # Had losses before, send bigger force
                attack_size = {
                    'axe': int(village.units.axe * 0.8),
                    'light': int(village.units.light * 0.8),
                    'ram': village.units.ram
                }
            else:
                # Normal attack
                attack_size = {
                    'axe': int(village.units.axe * 0.6),
                    'light': int(village.units.light * 0.6),
                    'ram': int(village.units.ram * 0.5)
                }
            
            priority = (0.85 * personality.aggression * 
                       (abs(relation_score) / 100))  # More hostile = higher priority
            
            decisions.append(Decision(
                action_type='attack',
                village_id=village.id,
                priority=priority,
                details={
                    'to_village': target.id,
                    'units': attack_size,
                    'is_revenge': True,
                    'relation_score': relation_score
                }
            ))
            
            # One attack per tick
            break
        
        # If no explicit enemies, look for opportunistic targets
        if not enemies and personality.opportunism > 0.6:
            nearby = world.get_nearby_villages(village.x, village.y, radius=20)
            
            for target in nearby[:5]:
                # Skip bots, allies, strong players
                if target.is_bot or target.is_barb:
                    continue
                
                if target.owner_id:
                    relation = await memory.get_relation(bot.player_id, target.owner_id)
                    
                    # Skip allies
                    if relation > 20:
                        continue
                    
                    # Only attack if significantly weaker
                    if target.points < village.points * 0.5:
                        attack_size = {'axe': 100, 'light': 30}
                        
                        priority = 0.5 * personality.opportunism
                        
                        decisions.append(Decision(
                            action_type='attack',
                            village_id=village.id,
                            priority=priority,
                            details={
                                'to_village': target.id,
                                'units': attack_size,
                                'is_opportunistic': True
                            }
                        ))
                        break
        
        return decisions


class LearningDiplomacyPlanner:
    """
    Memory-enhanced diplomacy
    Tracks relations and adjusts support behavior
    """
    
    @staticmethod
    async def process_world_events(bot: AIBotState, world: WorldSnapshot, memory: AIMemory):
        """
        Process recent events and update relations
        This runs each tick to keep memory fresh
        """
        # TODO: Query battle reports from DB
        # For now, simulate with tribe-based relation boosts
        
        if bot.tribe_id:
            tribe_members = world.get_tribe_members(bot.tribe_id)
            
            for member in tribe_members:
                if member.id != bot.player_id:
                    # Boost relation with tribe mates
                    await memory.process_relation_event(
                        bot.player_id,
                        member.id,
                        'same_tribe'
                    )
    
    @staticmethod
    async def plan_supports_with_memory(
        bot: AIBotState, village: VillageState,
        personality: PersonalityProfile, world: WorldSnapshot,
        memory: AIMemory
    ) -> List[Decision]:
        """
        Send support based on learned relations
        Strong positive relations = more likely to help
        """
        decisions = []
        
        # Need defensive units
        if village.units.defensive_power < 200:
            return decisions
        
        # Get all positive relations
        all_relations = await memory.get_all_relations(bot.player_id)
        
        friends = [
            (player_id, score)
            for player_id, score in all_relations.items()
            if score > 40  # Friend threshold
        ]
        
        # Sort by strongest friendships
        friends.sort(key=lambda x: x[1], reverse=True)
        
        # Help top friends
        for friend_id, relation_score in friends[:3]:
            friend_villages = world.get_player_villages(friend_id)
            
            for friend_village in friend_villages:
                # Check distance
                distance = ((friend_village.x - village.x) ** 2 + 
                          (friend_village.y - village.y) ** 2) ** 0.5
                
                if distance < 20:
                    # Support size based on relation strength
                    support_ratio = min(0.4, (relation_score / 100) * 0.4)
                    
                    support_size = {
                        'spear': int(village.units.spear * support_ratio),
                        'sword': int(village.units.sword * support_ratio)
                    }
                    
                    if support_size['spear'] > 30 or support_size['sword'] > 15:
                        priority = (0.75 * personality.support_allies * 
                                  (relation_score / 100))
                        
                        decisions.append(Decision(
                            action_type='support',
                            village_id=village.id,
                            priority=priority,
                            details={
                                'to_village': friend_village.id,
                                'units': support_size,
                                'relation_score': relation_score
                            }
                        ))
                        
                        return decisions
        
        return decisions
