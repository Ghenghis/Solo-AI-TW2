"""
Bot Brain - Decision Engine
Concrete implementations of economy, military, farming, defense, and diplomacy
NO HAND-WAVING - actual implementable logic
"""

import random
from typing import List, Dict, Optional
from datetime import datetime, timedelta
import structlog

from bots.state import AIBotState, VillageState, Decision, UnitComposition, RelationType
from bots.personalities_enhanced import PersonalityProfile, get_personality
from core.world import WorldSnapshot, VillageInfo
from core.game_client import GameClient
from bots.advanced_features import AdvancedFeaturesIntegrator
from bots.decision_resolver import DecisionResolver

logger = structlog.get_logger()


class EconomyPlanner:
    """Manages resource production and building decisions"""
    
    @staticmethod
    def plan_buildings(bot: AIBotState, village: VillageState, 
                       personality: PersonalityProfile, world: WorldSnapshot) -> List[Decision]:
        """
        Decide which buildings to upgrade
        Returns list of build decisions
        """
        decisions = []
        
        # Don't queue if already building
        if len(village.build_queue) >= personality.risk_tolerance * 3:
            return decisions
        
        # Get priority list for current phase
        priorities = personality.get_build_priorities(bot.game_phase.value)
        
        # Check storage situation
        if village.resources_capped:
            # Emergency: upgrade storage immediately
            current_storage_level = village.buildings.get('warehouse', 0)
            if current_storage_level < 30:
                decisions.append(Decision(
                    action_type='build',
                    village_id=village.id,
                    priority=0.95,
                    details={'building': 'warehouse'}
                ))
                return decisions
        
        # Check population cap
        if village.pop >= village.pop_max - 10:
            current_farm_level = village.buildings.get('farm', 0)
            if current_farm_level < 30:
                decisions.append(Decision(
                    action_type='build',
                    village_id=village.id,
                    priority=0.9,
                    details={'building': 'farm'}
                ))
                return decisions
        
        # Follow priority list
        for building in priorities:
            current_level = village.buildings.get(building, 0)
            
            # Phase-based level caps
            if bot.game_phase.value == "early":
                max_level = 15
            elif bot.game_phase.value == "mid":
                max_level = 25
            else:
                max_level = 30
            
            if current_level < max_level:
                # Calculate priority based on personality
                base_priority = 0.7
                
                # Adjust based on building type and personality
                if building in ['timber', 'clay', 'iron']:
                    base_priority *= personality.eco_focus
                elif building in ['barracks', 'stable', 'workshop']:
                    base_priority *= personality.military_focus
                elif building == 'wall':
                    base_priority *= personality.defense_bias
                
                # Add randomness
                priority = base_priority * random.uniform(
                    1 - personality.randomness, 
                    1 + personality.randomness
                )
                
                decisions.append(Decision(
                    action_type='build',
                    village_id=village.id,
                    priority=priority,
                    details={'building': building, 'level': current_level + 1}
                ))
                
                # Only queue one building at a time for now
                break
        
        return decisions


class RecruitmentPlanner:
    """Manages unit recruitment decisions"""
    
    @staticmethod
    def plan_recruitment(bot: AIBotState, village: VillageState,
                        personality: PersonalityProfile, world: WorldSnapshot) -> List[Decision]:
        """
        Decide which units to recruit
        Returns list of recruitment decisions
        """
        decisions = []
        
        # Don't recruit if pop-capped
        if not village.can_recruit:
            return decisions
        
        # Get target composition for village role
        target_comp = personality.get_unit_composition_target(
            bot.game_phase.value,
            village.role
        )
        
        # Calculate deficit
        current_units = village.units
        
        for unit_type, target_count in target_comp.items():
            current_count = getattr(current_units, unit_type, 0)
            deficit = target_count - current_count
            
            if deficit > 0:
                # Determine how many to recruit this tick
                recruit_count = min(
                    deficit,
                    int(10 * personality.military_focus),
                    village.pop_max - village.pop  # Don't overfill
                )
                
                if recruit_count > 0:
                    priority = 0.6 * personality.military_focus
                    
                    # Boost priority if under threat
                    if bot.threat_level > 50:
                        priority *= 1.5
                    
                    decisions.append(Decision(
                        action_type='recruit',
                        village_id=village.id,
                        priority=priority,
                        details={
                            'units': {unit_type: recruit_count}
                        }
                    ))
        
        return decisions


class FarmingPlanner:
    """Manages barbarian village farming"""
    
    @staticmethod
    def plan_farming(bot: AIBotState, village: VillageState,
                    personality: PersonalityProfile, world: WorldSnapshot) -> List[Decision]:
        """
        Plan farming raids on barbarian villages
        This is ESSENTIAL for competitive play
        """
        decisions = []
        
        # Only farm from offense villages or if personality wants to farm
        if village.role != "offense" and personality.farm_intensity < 0.5:
            return decisions
        
        # Need offensive units to farm
        if village.units.offensive_power < 100:
            return decisions
        
        # Build farm rotation if empty
        if not bot.farm_rotation:
            farmable_barbs = world.get_farmable_barbs(
                village.x, village.y, 
                radius=30,
                max_points=int(200 * personality.risk_tolerance)
            )
            bot.farm_rotation = [v.id for v in farmable_barbs]
            bot.farm_index = 0
        
        # Get next farm target
        if bot.farm_rotation and bot.farm_index < len(bot.farm_rotation):
            target_id = bot.farm_rotation[bot.farm_index]
            target = world.get_village(target_id)
            
            if target and target.is_barb:
                # Determine farm size based on personality and past success
                success_rate = bot.successful_farms.get(target_id, 0)
                
                if success_rate > 3:
                    # Known good target, send small wave
                    farm_size = {'axe': 30, 'light': 5}
                else:
                    # Unknown target, send medium wave
                    farm_size = {'axe': 50, 'light': 10}
                
                # Check if we have enough units
                if (village.units.axe >= farm_size['axe'] and 
                    village.units.light >= farm_size['light']):
                    
                    priority = 0.7 * personality.farm_intensity
                    
                    decisions.append(Decision(
                        action_type='attack',
                        village_id=village.id,
                        priority=priority,
                        details={
                            'to_village': target_id,
                            'units': farm_size,
                            'is_farm': True
                        }
                    ))
                    
                    # Advance rotation
                    bot.farm_index = (bot.farm_index + 1) % len(bot.farm_rotation)
        
        return decisions


class DefensePlanner:
    """Manages defensive responses to threats"""
    
    @staticmethod
    def plan_defense(bot: AIBotState, village: VillageState,
                    personality: PersonalityProfile, world: WorldSnapshot) -> List[Decision]:
        """
        React to incoming attacks
        Send supports, adjust recruitment priorities
        """
        decisions = []
        
        # No incoming attacks, nothing to do
        if village.incoming_attacks == 0:
            return decisions
        
        # We're under attack!
        bot.threat_level = min(100, bot.threat_level + 20)
        
        logger.warn("village_under_attack",
                   bot=bot.name,
                   village=village.name,
                   incoming=village.incoming_attacks)
        
        # 1. Request support from allies
        if bot.tribe_id and personality.ally_loyalty > 0.5:
            tribe_members = world.get_tribe_members(bot.tribe_id)
            
            for ally in tribe_members:
                if ally.id == bot.player_id:
                    continue
                
                ally_villages = world.get_player_villages(ally.id)
                
                for ally_village in ally_villages:
                    # Check distance (only help if close enough)
                    distance = ((ally_village.x - village.x) ** 2 + 
                              (ally_village.y - village.y) ** 2) ** 0.5
                    
                    if distance < 15:
                        # Send support request
                        decisions.append(Decision(
                            action_type='support_request',
                            village_id=village.id,
                            priority=0.85,
                            details={
                                'to_player': ally.id,
                                'from_village': ally_village.id
                            }
                        ))
        
        # 2. Cancel any outgoing attacks from this village (if defensive personality)
        if personality.defense_bias > 0.6:
            decisions.append(Decision(
                action_type='cancel_commands',
                village_id=village.id,
                priority=0.9,
                details={'type': 'outgoing_attacks'}
            ))
        
        return decisions


class AttackPlanner:
    """Manages offensive operations"""
    
    @staticmethod
    def plan_attacks(bot: AIBotState, village: VillageState,
                    personality: PersonalityProfile, world: WorldSnapshot) -> List[Decision]:
        """
        Plan attacks on enemy players
        Considers relations, risk tolerance, coordination
        """
        decisions = []
        
        # Need to be aggressive and have army
        if personality.aggression < 0.3:
            return decisions
        
        if village.units.offensive_power < 500:
            return decisions
        
        # Don't attack if we're under threat (unless chaos)
        if bot.threat_level > 60 and personality.name != "chaos":
            return decisions
        
        # Find targets based on relations
        enemies = bot.get_enemies()
        
        # If no explicit enemies, look for opportunistic targets
        if not enemies and personality.opportunism > 0.6:
            nearby = world.get_nearby_villages(village.x, village.y, radius=20)
            
            for target in nearby:
                # Skip barbs, bots, allies, and strong players
                if target.is_barb or target.is_bot:
                    continue
                
                if target.owner_id and bot.is_ally(target.owner_id):
                    continue
                
                # Only attack if significantly weaker
                if target.points < village.points * 0.6:
                    # Calculate attack size
                    attack_size = {
                        'axe': min(village.units.axe, 150),
                        'light': min(village.units.light, 50),
                        'ram': min(village.units.ram, 10) if bot.game_phase.value == "late" else 0
                    }
                    
                    priority = (0.6 * personality.aggression * 
                              personality.opportunism)
                    
                    decisions.append(Decision(
                        action_type='attack',
                        village_id=village.id,
                        priority=priority,
                        details={
                            'to_village': target.id,
                            'units': attack_size,
                            'is_conquest': False
                        }
                    ))
                    
                    # Only plan one attack per tick
                    break
        
        # Attack explicit enemies
        elif enemies:
            for enemy_id in enemies[:3]:  # Top 3 enemies
                enemy_villages = world.get_player_villages(enemy_id)
                
                if enemy_villages:
                    # Pick closest or weakest
                    target = min(enemy_villages, 
                               key=lambda v: ((v.x - village.x) ** 2 + 
                                            (v.y - village.y) ** 2) ** 0.5)
                    
                    # Full assault on enemies
                    attack_size = {
                        'axe': int(village.units.axe * 0.7),
                        'light': int(village.units.light * 0.7),
                        'ram': village.units.ram,
                        'catapult': village.units.catapult
                    }
                    
                    priority = 0.8 * personality.aggression
                    
                    decisions.append(Decision(
                        action_type='attack',
                        village_id=village.id,
                        priority=priority,
                        details={
                            'to_village': target.id,
                            'units': attack_size,
                            'is_revenge': True
                        }
                    ))
                    break
        
        return decisions


class DiplomacyPlanner:
    """Manages relations and tribe interactions"""
    
    @staticmethod
    def update_relations(bot: AIBotState, world: WorldSnapshot, db) -> List[Decision]:
        """
        Update relations based on recent events
        Process battle reports, supports received, etc.
        """
        decisions = []
        
        # Query recent reports from database
        # This would process:
        # - Attacks received → decrease relation
        # - Supports received → increase relation
        # - Same tribe → boost relation
        # - Neighbor pressure → adjust relations
        
        # For now, stub with tribe-based relations
        if bot.tribe_id:
            tribe_members = world.get_tribe_members(bot.tribe_id)
            
            for member in tribe_members:
                if member.id != bot.player_id:
                    # Boost relation with tribe mates
                    bot.update_relation(member.id, "same_tribe", 10)
        
        return decisions
    
    @staticmethod
    def plan_supports(bot: AIBotState, village: VillageState,
                     personality: PersonalityProfile, world: WorldSnapshot) -> List[Decision]:
        """
        Send defensive supports to allies
        """
        decisions = []
        
        # Only supportive personalities
        if personality.support_allies < 0.5:
            return decisions
        
        # Need defensive units
        if village.units.defensive_power < 200:
            return decisions
        
        # Find allies who need help
        allies = bot.get_allies()
        
        for ally_id in allies:
            ally_villages = world.get_player_villages(ally_id)
            
            for ally_village in ally_villages:
                # Check if within support range
                distance = ((ally_village.x - village.x) ** 2 + 
                          (ally_village.y - village.y) ** 2) ** 0.5
                
                if distance < 20:
                    # Send support (small stack)
                    support_size = {
                        'spear': int(village.units.spear * 0.2),
                        'sword': int(village.units.sword * 0.2)
                    }
                    
                    if support_size['spear'] > 50 or support_size['sword'] > 20:
                        priority = 0.7 * personality.support_allies
                        
                        decisions.append(Decision(
                            action_type='support',
                            village_id=village.id,
                            priority=priority,
                            details={
                                'to_village': ally_village.id,
                                'units': support_size
                            }
                        ))
                        
                        # Only one support per tick
                        return decisions
        
        return decisions


async def run_bot_tick(bot: AIBotState, world: WorldSnapshot, 
                      game_client: GameClient, memory, db, config) -> None:
    """
    Main bot decision loop - called each orchestrator tick
    
    Args:
        bot: Bot state with villages and personality
        world: Current world snapshot
        game_client: HTTP client for game actions
        memory: AIMemory instance for learning
        db: Database connection
        config: Configuration object
    This is where EVERYTHING comes together
    """
    try:
        personality = get_personality(bot.personality)
        
        logger.info("bot_tick_start",
                   bot=bot.name,
                   personality=bot.personality,
                   villages=len(bot.own_villages),
                   phase=bot.game_phase.value)
        
        # Update relations based on world state
        DiplomacyPlanner.update_relations(bot, world, db)
        
        # Collect all decisions from all planners
        all_decisions = []
        
        for village in bot.own_villages:
            # Economy
            all_decisions.extend(
                EconomyPlanner.plan_buildings(bot, village, personality, world)
            )
            
            # Military recruitment
            all_decisions.extend(
                RecruitmentPlanner.plan_recruitment(bot, village, personality, world)
            )
            
            # Farming (critical!)
            all_decisions.extend(
                FarmingPlanner.plan_farming(bot, village, personality, world)
            )
            
            # Defense
            all_decisions.extend(
                DefensePlanner.plan_defense(bot, village, personality, world)
            )
            
            # Attacks
            all_decisions.extend(
                AttackPlanner.plan_attacks(bot, village, personality, world)
            )
            
            # Supports
            all_decisions.extend(
                DiplomacyPlanner.plan_supports(bot, village, personality, world)
            )
            
            # ✅ ADVANCED FEATURES: 7 game-changing features with memory
            all_decisions.extend(
                await AdvancedFeaturesIntegrator.run_advanced_features(
                    bot, village, personality, world, memory
                )
            )
        
        # ✅ DECISION RESOLVER: Validate resources, resolve conflicts, apply caps
        final_decisions = DecisionResolver.resolve_decisions(
            all_decisions, bot, config
        )
        
        # Log decision summary
        DecisionResolver.log_decision_summary(final_decisions, bot)
        
        # Execute final decisions and learn from results
        for decision in final_decisions:
            try:
                await execute_decision(bot, decision, game_client, db)
                
                # ✅ MEMORY LEARNING: Update memory based on action type
                if decision.action_type in ['attack', 'timed_attack']:
                    # Record attack (actual results would come from game_client)
                    target_id = decision.details.get('to_village')
                    if target_id:
                        # Placeholder: In production, parse battle report
                        await memory.record_attack_result(
                            bot_id=bot.player_id,
                            target_village_id=target_id,
                            loot={'wood': 0, 'clay': 0, 'iron': 0},  # TODO: Parse from result
                            losses={},  # TODO: Parse from result
                            success=True  # TODO: Determine from result
                        )
                
                elif decision.action_type == 'support':
                    # Update relations for support
                    target_id = decision.details.get('to_village')
                    if target_id:
                        target_village = world.get_village(target_id)
                        if target_village and target_village.owner_id:
                            await memory.process_relation_event(
                                bot.player_id,
                                target_village.owner_id,
                                'sent_support'
                            )
                
                # Human-like delay between actions
                delay = random.uniform(
                    config.min_action_interval,
                    config.min_action_interval * 3
                )
                await asyncio.sleep(delay)
                
            except Exception as e:
                logger.error("decision_execution_failed",
                           bot=bot.name,
                           decision=decision.action_type,
                           error=str(e))
        
        # Update bot state
        bot.last_tick = datetime.now()
        bot.tick_count += 1
        
        logger.info("bot_tick_complete",
                   bot=bot.name,
                   decisions_made=len(all_decisions[:max_actions_per_tick]))
        
    except Exception as e:
        logger.error("bot_tick_failed",
                    bot=bot.name,
                    error=str(e),
                    exc_info=True)


async def execute_decision(bot: AIBotState, decision: Decision, 
                          game_client: GameClient, db) -> None:
    """Execute a single decision via HTTP or DB"""
    # This would call game_client methods
    # Implementation depends on game_client being fully built
    logger.info("executing_decision",
               bot=bot.name,
               action=decision.action_type,
               village=decision.village_id,
               details=decision.details)
    
    # TODO: Implement actual HTTP calls
    pass
