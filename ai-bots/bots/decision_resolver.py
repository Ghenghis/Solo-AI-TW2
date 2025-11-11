"""
Decision Resolver - Conflict Resolution & Priority System
Prevents multiple planners from fighting each other
"""

from typing import List, Dict
from collections import defaultdict
import structlog

from bots.state import AIBotState, VillageState, Decision

logger = structlog.get_logger()


class DecisionResolver:
    """
    Resolves conflicts between multiple planners
    Enforces caps, validates resources, prioritizes decisions
    """
    
    @staticmethod
    def resolve_decisions(decisions: List[Decision], 
                         bot: AIBotState,
                         config) -> List[Decision]:
        """
        Process all decisions from all planners
        Returns final list to execute
        """
        if not decisions:
            return []
        
        # Step 1: Remove zero-priority decisions
        decisions = [d for d in decisions if d.priority > 0]
        
        # Step 2: Sort by priority (highest first)
        decisions.sort(key=lambda d: d.priority, reverse=True)
        
        # Step 3: Group by village
        by_village = defaultdict(list)
        for decision in decisions:
            by_village[decision.village_id].append(decision)
        
        # Step 4: Apply per-village constraints
        final_decisions = []
        
        for village_id, village_decisions in by_village.items():
            village = next((v for v in bot.own_villages if v.id == village_id), None)
            if not village:
                continue
            
            # Track used resources/units per village
            used_units = defaultdict(int)
            used_resources = {'wood': 0, 'clay': 0, 'iron': 0}
            
            for decision in village_decisions:
                # Validate decision
                if not DecisionResolver._validate_decision(
                    decision, village, used_units, used_resources
                ):
                    logger.debug("decision_invalid",
                               action=decision.action_type,
                               village=village_id)
                    continue
                
                # Apply decision (track usage)
                DecisionResolver._apply_decision(
                    decision, used_units, used_resources
                )
                
                final_decisions.append(decision)
        
        # Step 5: Global caps
        final_decisions = DecisionResolver._apply_global_caps(
            final_decisions, bot, config
        )
        
        logger.info("decisions_resolved",
                   bot=bot.name,
                   total_decisions=len(decisions),
                   final_decisions=len(final_decisions),
                   filtered=len(decisions) - len(final_decisions))
        
        return final_decisions
    
    @staticmethod
    def _validate_decision(decision: Decision, 
                          village: VillageState,
                          used_units: Dict[str, int],
                          used_resources: Dict[str, int]) -> bool:
        """
        Check if decision is valid given current state and usage
        ✅ FIXED: Handles trade resource validation
        """
        # Validate unit availability
        if decision.action_type in ['attack', 'timed_attack', 'support', 'scout']:
            units = decision.details.get('units', {})
            
            for unit_type, count in units.items():
                available = getattr(village.units, unit_type, 0)
                already_used = used_units.get(unit_type, 0)
                
                if count > (available - already_used):
                    logger.debug("insufficient_units",
                               unit=unit_type,
                               needed=count,
                               available=available - already_used)
                    return False
        
        # Validate resource availability
        wood_needed = 0
        clay_needed = 0
        iron_needed = 0
        
        if decision.action_type == 'build':
            # TODO: Get from costs.py when implemented
            building = decision.details.get('building', '')
            level = decision.details.get('level', 1)
            # Placeholder costs
            wood_needed = 100 * level
            clay_needed = 100 * level
            iron_needed = 50 * level
        
        elif decision.action_type == 'recruit':
            # TODO: Get from costs.py when implemented
            units = decision.details.get('units', {})
            # Placeholder costs per unit
            unit_costs = {'spear': 50, 'sword': 100, 'axe': 100, 'light': 250}
            for unit, count in units.items():
                cost = unit_costs.get(unit, 100)
                wood_needed += cost * count
                clay_needed += cost * count
                iron_needed += cost * count // 2
        
        elif decision.action_type == 'trade':
            # ✅ FIX: Handle trade resource validation
            sell_resource = decision.details.get('sell', '')
            amount = decision.details.get('amount', 0)
            
            if sell_resource == 'wood':
                wood_needed = amount
            elif sell_resource == 'clay':
                clay_needed = amount
            elif sell_resource == 'iron':
                iron_needed = amount
        
        elif decision.action_type == 'send_resources':
            resources = decision.details.get('resources', {})
            wood_needed = resources.get('wood', 0)
            clay_needed = resources.get('clay', 0)
            iron_needed = resources.get('iron', 0)
        
        # Check availability
        if wood_needed > (village.wood - used_resources['wood']):
            return False
        if clay_needed > (village.clay - used_resources['clay']):
            return False
        if iron_needed > (village.iron - used_resources['iron']):
            return False
        
        return True
    
    @staticmethod
    def _apply_decision(decision: Decision,
                       used_units: Dict[str, int],
                       used_resources: Dict[str, int]):
        """
        Mark resources/units as used after validating decision
        ✅ FIXED: Handles trade and build/recruit resource tracking
        """
        # Track unit usage
        if decision.action_type in ['attack', 'timed_attack', 'support', 'scout']:
            units = decision.details.get('units', {})
            for unit_type, count in units.items():
                used_units[unit_type] += count
        
        # Track resource usage
        if decision.action_type == 'send_resources':
            resources = decision.details.get('resources', {})
            used_resources['wood'] += resources.get('wood', 0)
            used_resources['clay'] += resources.get('clay', 0)
            used_resources['iron'] += resources.get('iron', 0)
        
        elif decision.action_type == 'trade':
            # ✅ FIX: Track trade resource usage
            sell_resource = decision.details.get('sell', '')
            amount = decision.details.get('amount', 0)
            
            if sell_resource == 'wood':
                used_resources['wood'] += amount
            elif sell_resource == 'clay':
                used_resources['clay'] += amount
            elif sell_resource == 'iron':
                used_resources['iron'] += amount
        
        elif decision.action_type == 'build':
            # Track build costs (using placeholder from validation)
            building = decision.details.get('building', '')
            level = decision.details.get('level', 1)
            used_resources['wood'] += 100 * level
            used_resources['clay'] += 100 * level
            used_resources['iron'] += 50 * level
        
        elif decision.action_type == 'recruit':
            # Track recruitment costs (using placeholder)
            units = decision.details.get('units', {})
            unit_costs = {'spear': 50, 'sword': 100, 'axe': 100, 'light': 250}
            for unit, count in units.items():
                cost = unit_costs.get(unit, 100)
                used_resources['wood'] += cost * count
                used_resources['clay'] += cost * count
                used_resources['iron'] += (cost * count) // 2
    
    @staticmethod
    def _apply_global_caps(decisions: List[Decision],
                          bot: AIBotState,
                          config) -> List[Decision]:
        """
        Apply global rate limits and caps
        ✅ FIXED: Guards against division errors and better per-bot limits
        """
        # Count attacks per hour
        attack_count = sum(
            1 for d in decisions 
            if d.action_type in ['attack', 'timed_attack']
        )
        
        # ✅ FIX: Cap attacks per tick with guards
        ticks_per_hour = max(1, 3600 // max(1, config.bot_tick_rate))
        max_attacks = max(1, config.max_attacks_per_hour // ticks_per_hour)
        
        if attack_count > max_attacks:
            # Keep highest priority attacks
            attacks = [d for d in decisions if d.action_type in ['attack', 'timed_attack']]
            non_attacks = [d for d in decisions if d.action_type not in ['attack', 'timed_attack']]
            
            attacks = sorted(attacks, key=lambda d: d.priority, reverse=True)[:max_attacks]
            decisions = non_attacks + attacks
        
        # ✅ FIX: Cap total decisions per bot (not scaled by global bot count)
        max_decisions_per_bot = getattr(config, 'max_decisions_per_bot', 10)
        if len(decisions) > max_decisions_per_bot:
            decisions = decisions[:max_decisions_per_bot]
        
        return decisions
    
    @staticmethod
    def log_decision_summary(decisions: List[Decision], bot: AIBotState):
        """
        Log summary of decisions for monitoring
        """
        by_type = defaultdict(int)
        for decision in decisions:
            by_type[decision.action_type] += 1
        
        logger.info("decision_summary",
                   bot=bot.name,
                   total=len(decisions),
                   breakdown=dict(by_type),
                   avg_priority=sum(d.priority for d in decisions) / max(1, len(decisions)))
