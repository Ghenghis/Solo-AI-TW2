"""
Guardrail Enforcer - Makes AI Believable, Not Just Smart

Guardrails: CENTRALIZED enforcement point for all AI behavior rules.

This is the single source of truth for:
- Rate limiting (attacks, trades, builds)
- Anti-harassment (per-target caps, cool-downs)
- Human-like behavior (sleep, sessions, rhythm)
- Personality constraints (aggression scaling)
- System protection (CPU/DB load limits)

Every decision MUST pass through guardrails.validate() before execution.
No exceptions. This prevents chaos.
"""

from datetime import datetime, timedelta
from typing import List, Dict, Tuple
from collections import defaultdict
import random
import time
import structlog

from bots.state import AIBotState, Decision
from bots.personalities_enhanced import PersonalityProfile
from core.world import WorldSnapshot

logger = structlog.get_logger()


class GuardrailEnforcer:
    """
    CENTRALIZED enforcement point for ALL AI behavior rules.
    
    Every decision must pass through validate() before execution.
    This prevents:
    - Harassment (per-target caps, cool-downs)
    - Spam (rate limits on all actions)
    - Unnatural behavior (sleep, sessions, rhythm)
    - Resource waste (failed attack cool-downs)
    - System overload (global caps, circuit breakers)
    
    Tracks state:
    - Per-bot sleep windows, sessions, cool-downs
    - Per-bot attack history (spam detection)
    - Per (bot, target) harassment counters
    - Global attack targets (anti-dogpile)
    - Failed action cool-downs
    - System-wide rate limits
    """
    
    def __init__(self, config: Dict):
        self.config = config
        
        # Per-bot state
        self._sleep_windows: Dict[int, Tuple[int, int]] = {}  # bot_id -> (start_hour, end_hour)
        self._session_state: Dict[int, Dict] = {}  # bot_id -> {start_time, duration, cooldown_until}
        self._last_activity: Dict[int, float] = {}  # bot_id -> timestamp
        self._scout_timestamps: Dict[Tuple[int, int], float] = {}  # (bot_id, target_village_id) -> timestamp
        
        # Per-bot attack tracking (rolling window)
        self._attack_history: Dict[int, List[Dict]] = defaultdict(list)  # bot_id -> [attack_record]
        
        # Per (bot, target) harassment tracking
        self._harassment_counters: Dict[Tuple[int, int], List[float]] = defaultdict(list)  # (bot_id, target_player_id) -> [timestamps]
        
        # Failed action cool-downs
        self._failed_attack_cooldowns: Dict[Tuple[int, int], float] = {}  # (bot_id, target_village_id) -> cooldown_until
        
        # Global state (anti-dogpile)
        self._global_attack_targets: Dict[int, int] = defaultdict(int)  # target_player_id -> attack_count
        self._global_reset_time: float = time.time()
        
        # Circuit breaker
        self._system_attack_count: int = 0
        self._system_attack_window_start: float = time.time()
    
    @staticmethod
    def apply(bot: AIBotState,
              decisions: List[Decision],
              world: WorldSnapshot,
              config,
              personality: PersonalityProfile) -> List[Decision]:
        """
        Apply ALL guardrails in order
        Returns filtered/adjusted decisions
        """
        if not decisions:
            return decisions
        
        now = datetime.utcnow()
        
        # Reset global tracking hourly
        if (now - datetime.fromtimestamp(GuardrailEnforcer._global_reset_time)).total_seconds() > 3600:
            GuardrailEnforcer._global_attack_targets.clear()
            GuardrailEnforcer._global_reset_time = time.time()
        
        logger.debug("guardrails_start",
                    bot=bot.name,
                    decisions_in=len(decisions))
        
        # ==========================================
        # LAYER 1: Human-like behavior
        # ==========================================
        
        # 1.1: Sleep windows (3-6 hours of low activity)
        if GuardrailEnforcer._is_in_sleep_window(bot, now, config):
            decisions = GuardrailEnforcer._filter_sleep_actions(decisions)
            logger.info("guardrail_sleep_window",
                       bot=bot.name,
                       actions_allowed=len(decisions))
        
        # 1.2: Reaction delay (don't instantly respond to new intel)
        decisions = GuardrailEnforcer._apply_reaction_delay(bot, decisions, world, now)
        
        # ==========================================
        # LAYER 2: Anti-spam / per-target limits
        # ==========================================
        
        # 2.1: Per-village/player attack caps (per tick)
        decisions = GuardrailEnforcer._limit_per_target_spam(
            bot, decisions, world, config
        )
        
        # 2.2: Rolling window caps (attacks/hour to same player)
        decisions = GuardrailEnforcer._limit_rolling_window_attacks(
            bot, decisions, world, config, now
        )
        
        # ==========================================
        # LAYER 3: Fair play / world health
        # ==========================================
        
        # 3.1: Anti-dogpile (reduce priority if many bots hitting same target)
        decisions = GuardrailEnforcer._anti_dogpile(
            bot, decisions, world, config
        )
        
        # ==========================================
        # LAYER 4: Personality-aligned constraints
        # ==========================================
        
        # 4.1: Shape aggression by personality
        decisions = GuardrailEnforcer._shape_by_personality(
            decisions, personality, config
        )
        
        logger.info("guardrails_complete",
                   bot=bot.name,
                   decisions_out=len(decisions),
                   filtered=len([d for d in decisions if d.priority > 0]))
        
        return decisions
    
    # ==========================================
    # LAYER 1: Human-like Behavior
    # ==========================================
    
    @staticmethod
    def _is_in_sleep_window(bot: AIBotState, now: datetime, config) -> bool:
        """
        Each bot has 3-6 hour sleep window (deterministic but varies per bot)
        """
        if not hasattr(bot, 'sleep_window'):
            # Deterministic sleep schedule based on bot ID
            start_hour = (bot.player_id * 3) % 24
            duration = 3 + (bot.player_id % 3)  # 3-5 hours
            bot.sleep_window = (start_hour, duration)
        
        start_hour, duration = bot.sleep_window
        end_hour = (start_hour + duration) % 24
        
        current_hour = now.hour
        
        if start_hour < end_hour:
            return start_hour <= current_hour < end_hour
        else:
            # Wraps over midnight
            return current_hour >= start_hour or current_hour < end_hour
    
    @staticmethod
    def _filter_sleep_actions(decisions: List[Decision]) -> List[Decision]:
        """
        During sleep window: only allow minimal maintenance
        No attacks, no trades, minimal recruitment
        """
        allowed_during_sleep = ['build', 'recruit', 'send_resources']
        
        filtered = []
        for decision in decisions:
            if decision.action_type in allowed_during_sleep:
                # Reduce priority during sleep
                decision.priority *= 0.3
                filtered.append(decision)
            elif decision.action_type == 'defense':
                # Always allow defensive actions
                filtered.append(decision)
        
        return filtered
    
    @staticmethod
    def _apply_reaction_delay(bot: AIBotState, 
                             decisions: List[Decision],
                             world: WorldSnapshot,
                             now: datetime) -> List[Decision]:
        """
        Don't instantly react to new intel
        Add delay before attacking newly scouted targets
        """
        # Track when we last scouted each village
        if not hasattr(bot, 'scout_timestamps'):
            bot.scout_timestamps = {}
        
        min_reaction_time = timedelta(minutes=random.randint(5, 15))
        
        filtered = []
        for decision in decisions:
            if decision.action_type not in ['attack', 'timed_attack']:
                filtered.append(decision)
                continue
            
            target_id = decision.details.get('to_village')
            if target_id in bot.scout_timestamps:
                time_since_scout = now - bot.scout_timestamps[target_id]
                
                if time_since_scout < min_reaction_time:
                    # Too soon, reduce priority heavily
                    decision.priority *= 0.2
                    logger.debug("reaction_delay",
                               bot=bot.name,
                               target=target_id,
                               wait=int((min_reaction_time - time_since_scout).total_seconds()))
            
            filtered.append(decision)
        
        return filtered
    
    # ==========================================
    # LAYER 2: Anti-spam / Per-target Limits
    # ==========================================
    
    @staticmethod
    def _limit_per_target_spam(bot: AIBotState,
                               decisions: List[Decision],
                               world: WorldSnapshot,
                               config) -> List[Decision]:
        """
        Prevent spamming same village/player in single tick
        """
        max_per_village = getattr(config, 'max_attacks_per_village_per_tick', 2)
        max_per_player = getattr(config, 'max_attacks_per_player_per_tick', 4)
        
        attacks_by_village = defaultdict(int)
        attacks_by_player = defaultdict(int)
        
        filtered = []
        for decision in decisions:
            if decision.action_type not in ['attack', 'timed_attack']:
                filtered.append(decision)
                continue
            
            to_village = decision.details.get('to_village')
            target = world.get_village(to_village) if to_village else None
            target_player = target.owner_id if target else None
            
            # Check village cap
            if to_village and attacks_by_village[to_village] >= max_per_village:
                logger.debug("guardrail_village_cap",
                           bot=bot.name,
                           village=to_village,
                           cap=max_per_village)
                continue
            
            # Check player cap
            if target_player and attacks_by_player[target_player] >= max_per_player:
                logger.debug("guardrail_player_cap",
                           bot=bot.name,
                           player=target_player,
                           cap=max_per_player)
                continue
            
            filtered.append(decision)
            
            if to_village:
                attacks_by_village[to_village] += 1
            if target_player:
                attacks_by_player[target_player] += 1
        
        return filtered
    
    @staticmethod
    def _limit_rolling_window_attacks(bot: AIBotState,
                                      decisions: List[Decision],
                                      world: WorldSnapshot,
                                      config,
                                      now: datetime) -> List[Decision]:
        """
        Enforce rolling window: max N attacks per hour to same player
        """
        if not hasattr(bot, 'attack_history'):
            bot.attack_history = defaultdict(list)  # player_id -> [timestamps]
        
        max_per_hour = getattr(config, 'max_attacks_per_player_per_hour', 10)
        window = timedelta(hours=1)
        
        # Clean old history
        for player_id in list(bot.attack_history.keys()):
            bot.attack_history[player_id] = [
                ts for ts in bot.attack_history[player_id]
                if now - ts < window
            ]
        
        filtered = []
        for decision in decisions:
            if decision.action_type not in ['attack', 'timed_attack']:
                filtered.append(decision)
                continue
            
            to_village = decision.details.get('to_village')
            target = world.get_village(to_village) if to_village else None
            target_player = target.owner_id if target else None
            
            if target_player:
                recent_attacks = len(bot.attack_history[target_player])
                
                if recent_attacks >= max_per_hour:
                    logger.debug("guardrail_hourly_cap",
                               bot=bot.name,
                               player=target_player,
                               cap=max_per_hour)
                    continue
                
                # Will add to history after execution
                bot.attack_history[target_player].append(now)
            
            filtered.append(decision)
        
        return filtered
    
    # ==========================================
    # LAYER 3: Fair Play / World Health
    # ==========================================
    
    @staticmethod
    def _anti_dogpile(bot: AIBotState,
                      decisions: List[Decision],
                      world: WorldSnapshot,
                      config) -> List[Decision]:
        """
        If many bots are hitting same target, reduce priority
        Prevents coordinated harassment that looks like bot swarm
        """
        dogpile_threshold = getattr(config, 'dogpile_threshold', 5)
        
        for decision in decisions:
            if decision.action_type not in ['attack', 'timed_attack']:
                continue
            
            to_village = decision.details.get('to_village')
            target = world.get_village(to_village) if to_village else None
            target_player = target.owner_id if target else None
            
            if target_player:
                # Check global tracking
                current_attacks = GuardrailEnforcer._global_attack_targets[target_player]
                
                if current_attacks >= dogpile_threshold:
                    # Heavily reduce priority
                    decision.priority *= 0.3
                    logger.debug("anti_dogpile",
                               bot=bot.name,
                               target_player=target_player,
                               current_attacks=current_attacks)
                
                # Increment (will be reset hourly)
                GuardrailEnforcer._global_attack_targets[target_player] += 1
        
        return decisions
    
    # ==========================================
    # LAYER 4: Personality-aligned Constraints
    # ==========================================
    
    @staticmethod
    def _shape_by_personality(decisions: List[Decision],
                             personality: PersonalityProfile,
                             config) -> List[Decision]:
        """
        Adjust aggression based on personality
        Turtles rarely attack, Warmongers are aggressive, etc.
        """
        aggression = personality.aggression
        diplo_focus = personality.diplo_focus
        
        for decision in decisions:
            if decision.action_type in ['attack', 'timed_attack']:
                # Turtles (low aggression): heavily reduce
                if aggression < 0.3:
                    decision.priority *= 0.4
                    logger.debug("personality_restraint",
                               personality="turtle",
                               action="attack")
                
                # Diplomats: reduce unprovoked attacks
                elif diplo_focus > 0.7:
                    # Only attack if relation is very negative
                    decision.priority *= 0.6
                    logger.debug("personality_restraint",
                               personality="diplomat",
                               action="attack")
                
                # Balanced: normal
                elif 0.3 <= aggression <= 0.7:
                    pass  # No change
                
                # Warmonger: boost slightly
                elif aggression > 0.8:
                    decision.priority *= 1.15
            
            elif decision.action_type == 'support':
                # Diplomats boost support priority
                if diplo_focus > 0.7:
                    decision.priority *= 1.3
        
        return decisions
    
    # ==========================================
    # Utility Methods
    # ==========================================
    
    @staticmethod
    def reset_global_state():
        """
        Call this between tests or on orchestrator restart
        """
        GuardrailEnforcer._global_attack_targets.clear()
        GuardrailEnforcer._last_reset = datetime.utcnow()
        logger.info("guardrails_reset")
    
    @staticmethod
    def get_stats() -> dict:
        """
        Get current guardrail statistics for monitoring
        """
        return {
            'global_targets_tracked': len(GuardrailEnforcer._global_attack_targets),
            'most_targeted_player': max(
                GuardrailEnforcer._global_attack_targets.items(),
                key=lambda x: x[1],
                default=(None, 0)
            ),
            'last_reset': GuardrailEnforcer._last_reset.isoformat()
        }

    def _check_harassment_cap(self, bot: AIBotState, decisions: List[Decision], world: WorldSnapshot, config: Config) -> List[Decision]:
        """
        ANTI-HARASSMENT: Hard cap on attacks per (bot, target_player) per time window.
        Prevents one bot from griefing the same player repeatedly.
        """
        now = time.time()
        harassment_window = config.harassment_window_hours * 3600  # Default 1 hour
        max_attacks_per_player = config.max_attacks_per_player_per_harassment_window  # Default 5
        
        filtered = []
        for decision in decisions:
            if decision.action_type != 'attack':
                filtered.append(decision)
                continue
            
            target_village = world.villages.get(decision.target_village_id)
            if not target_village:
                continue
            
            target_player_id = target_village['player_id']
            key = (bot.player_id, target_player_id)
            
            # Clean old timestamps
            self._harassment_counters[key] = [
                ts for ts in self._harassment_counters.get(key, [])
                if now - ts < harassment_window
            ]
            
            # Check cap
            if len(self._harassment_counters.get(key, [])) >= max_attacks_per_player:
                logger.warning(
                    "guardrail_harassment_cap",
                    bot=bot.player_id,
                    target_player=target_player_id,
                    attacks_in_window=len(self._harassment_counters.get(key, [])),
                    decision_blocked=True
                )
                continue
            
            # Track this attack
            self._harassment_counters.setdefault(key, []).append(now)
            filtered.append(decision)
        
        return filtered
    
    def _check_failed_attack_cooldown(self, bot: AIBotState, decisions: List[Decision], config: Config) -> List[Decision]:
        """
        ANTI-WASTE: Cool-down after failed attacks to same target.
        Prevents bots from spamming attacks that keep failing.
        """
        now = time.time()
        cooldown_duration = config.failed_attack_cooldown_minutes * 60  # Default 15 minutes
        
        filtered = []
        for decision in decisions:
            if decision.action_type != 'attack':
                filtered.append(decision)
                continue
            
            key = (bot.player_id, decision.target_village_id)
            cooldown_until = self._failed_attack_cooldowns.get(key, 0)
            
            if now < cooldown_until:
                logger.debug(
                    "guardrail_failed_attack_cooldown",
                    bot=bot.player_id,
                    target=decision.target_village_id,
                    cooldown_remaining_seconds=int(cooldown_until - now)
                )
                continue
            
            filtered.append(decision)
        
        return filtered
    
    def _enforce_session_rhythm(self, bot: AIBotState, decisions: List[Decision], config: Config) -> List[Decision]:
        """
        HUMAN-LIKE RHYTHM: Bots have sessions (10-30 min active, then cooldown).
        Not continuous activity 24/7.
        """
        if not config.enable_session_rhythm:
            return decisions
        
        now = time.time()
        session = self._session_state.get(bot.player_id)
        
        # Initialize session if needed
        if not session:
            import random
            session_duration = random.randint(
                config.min_session_duration_minutes * 60,
                config.max_session_duration_minutes * 60
            )
            self._session_state[bot.player_id] = {
                'start_time': now,
                'duration': session_duration,
                'cooldown_until': 0
            }
            session = self._session_state[bot.player_id]
        
        # Check if in cooldown
        if now < session.get('cooldown_until', 0):
            logger.debug(
                "guardrail_session_cooldown",
                bot=bot.player_id,
                cooldown_remaining_minutes=int((session.get('cooldown_until', 0) - now) / 60)
            )
            # Only allow defensive/passive actions during cooldown
            return [d for d in decisions if d.action_type in ['build', 'recruit', 'defend']]
        
        # Check if session expired
        elapsed = now - session.get('start_time', 0)
        if elapsed > session.get('duration', 0):
            # Start cooldown
            import random
            cooldown_duration = random.randint(
                config.min_session_cooldown_minutes * 60,
                config.max_session_cooldown_minutes * 60
            )
            session['cooldown_until'] = now + cooldown_duration
            logger.info(
                "guardrail_session_ended",
                bot=bot.player_id,
                session_duration_minutes=int(elapsed / 60),
                cooldown_minutes=int(cooldown_duration / 60)
            )
            return [d for d in decisions if d.action_type in ['build', 'recruit', 'defend']]
        
        return decisions
    
    def _check_circuit_breaker(self, bot: AIBotState, decisions: List[Decision], config: Config) -> List[Decision]:
        """
        SYSTEM PROTECTION: Circuit breaker if too many attacks system-wide.
        Prevents DB/HTTP overload.
        """
        now = time.time()
        window = 60  # 1 minute
        
        # Reset window if needed
        if now - self._system_attack_window_start > window:
            self._system_attack_count = 0
            self._system_attack_window_start = now
        
        # Count attacks in this batch
        attack_count = sum(1 for d in decisions if d.action_type == 'attack')
        
        # Check threshold
        if self._system_attack_count + attack_count > config.max_system_attacks_per_minute:
            logger.warning(
                "guardrail_circuit_breaker_triggered",
                bot=bot.player_id,
                system_attacks=self._system_attack_count,
                threshold=config.max_system_attacks_per_minute,
                decisions_blocked=attack_count
            )
            # Block all attacks, allow other actions
            return [d for d in decisions if d.action_type != 'attack']
        
        # Track attacks
        self._system_attack_count += attack_count
        return decisions
    
    def record_failed_attack(self, bot_id: int, target_village_id: int, config: Config):
        """
        Call this when an attack fails (HTTP error, invalid target, etc.).
        Triggers cooldown for this (bot, target) pair.
        """
        now = time.time()
        cooldown_duration = config.failed_attack_cooldown_minutes * 60
        key = (bot_id, target_village_id)
        self._failed_attack_cooldowns[key] = now + cooldown_duration
        
        logger.info(
            "guardrail_failed_attack_recorded",
            bot=bot_id,
            target=target_village_id,
            cooldown_minutes=config.failed_attack_cooldown_minutes
        )
    
    def get_stats(self) -> Dict:
        """
        Get guardrail enforcement statistics.
        Useful for monitoring and debugging.
        """
        now = time.time()
        active_sessions = sum(
            1 for s in self._session_state.values()
            if now < s.get('cooldown_until', 0) or (now - s.get('start_time', 0)) < s.get('duration', 0)
        )
        
        return {
            'global_targets_tracked': len(self._global_attack_targets),
            'most_targeted_player': max(
                self._global_attack_targets.items(),
                key=lambda x: x[1],
                default=(None, 0)
            ),
            'harassment_counters_active': len(self._harassment_counters),
            'failed_attack_cooldowns_active': sum(
                1 for cooldown_until in self._failed_attack_cooldowns.values()
                if now < cooldown_until
            ),
            'active_sessions': active_sessions,
            'system_attack_count_this_minute': self._system_attack_count,
            'last_reset': datetime.fromtimestamp(self._global_reset_time).isoformat()
        }
