"""
COMPLETE INTEGRATION EXAMPLE
Shows full pipeline from decisions → memory learning

This is THE wiring you need in orchestrator_enhanced.py
"""

import asyncio
from typing import List
from datetime import datetime
import structlog

from core.config import Config
from core.database import Database
from core.world import WorldSnapshot
from core.game_client import GameClient
from core.memory import AIMemory

from bots.state import AIBotState, VillageState, Decision
from bots.personalities_enhanced import get_personality
from bots.brain import (
    EconomyPlanner,
    RecruitmentPlanner,
    DefensePlanner,
    AttackPlanner,
    DiplomacyPlanner
)
from bots.learning_brain import (
    LearningFarmingPlanner,
    LearningAttackPlanner,
    LearningDiplomacyPlanner
)
from bots.advanced_features import AdvancedFeaturesIntegrator
from bots.decision_resolver import DecisionResolver
from core.guardrails import GuardrailEnforcer  # ✅ ADD GUARDRAILS

logger = structlog.get_logger()


async def run_bot_tick_COMPLETE_EXAMPLE(
    bot: AIBotState,
    world: WorldSnapshot,
    game_client: GameClient,
    memory: AIMemory,
    db: Database,
    config: Config
) -> None:
    """
    COMPLETE bot decision loop with ALL the pieces
    This is the actual implementation you need
    """
    try:
        personality = get_personality(bot.personality)
        
        logger.info("bot_tick_start",
                   bot=bot.name,
                   personality=bot.personality,
                   villages=len(bot.own_villages),
                   phase=bot.game_phase.value)
        
        # ==========================================
        # STEP 1: Update relations from world events
        # ==========================================
        await LearningDiplomacyPlanner.process_world_events(bot, world, memory)
        
        # ==========================================
        # STEP 2: Collect decisions from ALL planners
        # ==========================================
        all_decisions: List[Decision] = []
        
        for village in bot.own_villages:
            # Core planners (basic behavior)
            all_decisions.extend(
                EconomyPlanner.plan_buildings(bot, village, personality, world)
            )
            
            all_decisions.extend(
                RecruitmentPlanner.plan_recruitment(bot, village, personality, world)
            )
            
            # Learning-enhanced planners (memory-aware)
            all_decisions.extend(
                await LearningFarmingPlanner.plan_farming_with_memory(
                    bot, village, personality, world, memory
                )
            )
            
            all_decisions.extend(
                await LearningAttackPlanner.plan_attacks_with_memory(
                    bot, village, personality, world, memory
                )
            )
            
            all_decisions.extend(
                DefensePlanner.plan_defense(bot, village, personality, world)
            )
            
            all_decisions.extend(
                await LearningDiplomacyPlanner.plan_supports_with_memory(
                    bot, village, personality, world, memory
                )
            )
            
            # ✅ Advanced features (7 features + memory)
            all_decisions.extend(
                await AdvancedFeaturesIntegrator.run_advanced_features(
                    bot, village, personality, world, memory  # ← Memory param!
                )
            )
        
        # ==========================================
        # STEP 2.5: Apply guardrails BEFORE processing
        # ==========================================
        # ✅ NEW: Filter/shape decisions for human-like + fair-play
        guarded_decisions = GuardrailEnforcer.apply(
            bot=bot,
            decisions=all_decisions,
            world=world,
            config=config,
            personality=personality
        )
        
        logger.info("guardrails_applied",
                   bot=bot.name,
                   before=len(all_decisions),
                   after=len(guarded_decisions))
        
        # ==========================================
        # STEP 3: Post-process attack decisions
        # ==========================================
        processed_decisions = []
        for decision in guarded_decisions:  # ✅ Use guarded_decisions, not all_decisions
            # Apply defensive reserves, night bonus, etc.
            processed = AdvancedFeaturesIntegrator.adjust_attack_with_features(
                decision,
                bot.own_villages[0],  # Use first village for reference
                personality
            )
            processed_decisions.append(processed)
        
        # ==========================================
        # STEP 4: Resolve conflicts
        # ==========================================
        final_decisions = DecisionResolver.resolve_decisions(
            processed_decisions,
            bot,
            config
        )
        
        DecisionResolver.log_decision_summary(final_decisions, bot)
        
        # ==========================================
        # STEP 5: Execute decisions & LEARN from results
        # ==========================================
        for decision in final_decisions:
            try:
                # Execute via HTTP
                result = await execute_decision(decision, game_client, db)
                
                # ✅ CRITICAL: Write learning data
                if decision.action_type in ['attack', 'timed_attack']:
                    # Record attack outcome
                    await memory.record_attack_result(
                        bot_id=bot.player_id,
                        target_village_id=decision.details['to_village'],
                        loot=result.get('loot', {}),
                        losses=result.get('losses', {}),
                        success=result.get('success', False)
                    )
                    
                    # Update relations based on attack
                    target_owner = world.get_village(decision.details['to_village']).owner_id
                    if target_owner and target_owner != bot.player_id:
                        await memory.process_relation_event(
                            bot.player_id,
                            target_owner,
                            'received_attack' if result.get('success') else 'failed_attack'
                        )
                
                elif decision.action_type == 'support':
                    # Update relations for support
                    target_village = world.get_village(decision.details['to_village'])
                    if target_village and target_village.owner_id:
                        await memory.process_relation_event(
                            bot.player_id,
                            target_village.owner_id,
                            'sent_support'
                        )
                
                # Human-like delay between actions
                delay = random.uniform(
                    config.min_action_interval,
                    config.min_action_interval * 2
                )
                await asyncio.sleep(delay)
                
            except Exception as e:
                logger.error("decision_execution_failed",
                           bot=bot.name,
                           decision=decision.action_type,
                           error=str(e))
        
        # ==========================================
        # STEP 6: Update bot state
        # ==========================================
        bot.last_tick = datetime.now()
        bot.tick_count += 1
        
        logger.info("bot_tick_complete",
                   bot=bot.name,
                   decisions_executed=len(final_decisions))
        
    except Exception as e:
        logger.error("bot_tick_failed",
                    bot=bot.name,
                    error=str(e),
                    exc_info=True)


async def execute_decision(decision: Decision, game_client: GameClient, db: Database) -> dict:
    """
    Execute a single decision via HTTP
    Returns result dict with: success, loot, losses, etc.
    
    TODO: Implement actual HTTP calls per action type
    """
    result = {
        'success': False,
        'loot': {},
        'losses': {},
        'error': None
    }
    
    try:
        if decision.action_type == 'build':
            # POST /game.php?village=X&screen=main&action=upgrade_building
            # Data: building=barracks
            result['success'] = await game_client.build(
                village_id=decision.village_id,
                building=decision.details['building']
            )
        
        elif decision.action_type == 'recruit':
            # POST /game.php?village=X&screen=barracks&action=train
            # Data: spear=10
            result['success'] = await game_client.recruit(
                village_id=decision.village_id,
                units=decision.details['units']
            )
        
        elif decision.action_type in ['attack', 'timed_attack']:
            # POST /game.php?village=X&screen=place&try=confirm
            # Data: x=456, y=789, attack=true, axe=50
            attack_result = await game_client.send_attack(
                from_village=decision.village_id,
                to_village=decision.details['to_village'],
                units=decision.details['units']
            )
            
            result['success'] = attack_result.get('success', False)
            
            # Parse battle report (when it arrives)
            # This would come from DB reports table or report parsing
            result['loot'] = attack_result.get('loot', {'wood': 0, 'clay': 0, 'iron': 0})
            result['losses'] = attack_result.get('losses', {})
        
        elif decision.action_type == 'support':
            # Similar to attack but support=true
            result['success'] = await game_client.send_support(
                from_village=decision.village_id,
                to_village=decision.details['to_village'],
                units=decision.details['units']
            )
        
        elif decision.action_type == 'scout':
            # Send scouts
            result['success'] = await game_client.send_scout(
                from_village=decision.village_id,
                to_village=decision.details['to_village'],
                spy_count=decision.details['units']['spy']
            )
        
        elif decision.action_type == 'trade':
            # POST /game.php?village=X&screen=market&action=call_merchant
            result['success'] = await game_client.trade(
                village_id=decision.village_id,
                sell=decision.details['sell'],
                buy=decision.details['buy'],
                amount=decision.details['amount']
            )
        
        elif decision.action_type == 'send_resources':
            # POST /game.php?village=X&screen=market&action=send
            result['success'] = await game_client.send_resources(
                from_village=decision.village_id,
                to_village=decision.details['to_village'],
                resources=decision.details['resources']
            )
        
        logger.info("decision_executed",
                   action=decision.action_type,
                   success=result['success'])
        
    except Exception as e:
        result['error'] = str(e)
        logger.error("execute_decision_error",
                    action=decision.action_type,
                    error=str(e))
    
    return result


# ==========================================
# Usage in orchestrator_enhanced.py
# ==========================================

"""
Replace the existing run_bot_tick with this pattern:

async def main_orchestrator_loop():
    config = Config.from_env()
    db = Database(config)
    game_client = GameClient(config)
    memory = AIMemory(db)  # ← Create memory instance
    
    # Initialize memory schema
    await memory.initialize_schema()
    
    bots = await load_bots(db, config)
    
    while True:
        # Build world snapshot
        world = await WorldSnapshot.build(db)
        
        # Run all bots
        tasks = []
        for bot in bots:
            task = run_bot_tick_COMPLETE_EXAMPLE(
                bot, world, game_client, memory, db, config  # ← Pass memory!
            )
            tasks.append(task)
        
        await asyncio.gather(*tasks, return_exceptions=True)
        
        # Wait for next tick
        await asyncio.sleep(config.bot_tick_rate)
"""
