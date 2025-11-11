"""
Prometheus Metrics for AI Bot Orchestrator
Enterprise-grade observability with detailed metrics tracking
"""

from prometheus_client import Counter, Histogram, Gauge, Info, Summary
from typing import Dict
import time
from functools import wraps


# ===========================================
# DECISION METRICS
# ===========================================

decisions_made_total = Counter(
    'ai_decisions_made_total',
    'Total decisions made by AI bots',
    ['bot_id', 'action_type', 'personality']
)

decisions_blocked_total = Counter(
    'ai_decisions_blocked_total',
    'Decisions blocked by guardrails',
    ['bot_id', 'reason']
)

decisions_executed_total = Counter(
    'ai_decisions_executed_total',
    'Decisions successfully executed',
    ['bot_id', 'action_type']
)

decisions_failed_total = Counter(
    'ai_decisions_failed_total',
    'Decisions that failed during execution',
    ['bot_id', 'action_type', 'error_type']
)


# ===========================================
# GUARDRAIL METRICS
# ===========================================

guardrail_sleep_blocks = Counter(
    'guardrail_sleep_window_blocks_total',
    'Decisions blocked by sleep windows',
    ['bot_id']
)

guardrail_spam_blocks = Counter(
    'guardrail_spam_blocks_total',
    'Decisions blocked by spam prevention',
    ['bot_id', 'spam_type']
)

guardrail_harassment_blocks = Counter(
    'guardrail_harassment_blocks_total',
    'Decisions blocked by harassment prevention',
    ['bot_id', 'target_player']
)

guardrail_dogpile_adjustments = Counter(
    'guardrail_dogpile_adjustments_total',
    'Priority reductions due to dogpile',
    ['bot_id', 'target_player']
)

guardrail_session_cooldowns = Counter(
    'guardrail_session_cooldowns_total',
    'Bots in session cooldown',
    ['bot_id']
)


# ===========================================
# PERFORMANCE METRICS
# ===========================================

tick_duration_seconds = Histogram(
    'tick_duration_seconds',
    'Time taken to process a bot tick',
    ['bot_id'],
    buckets=(0.1, 0.5, 1.0, 2.0, 5.0, 10.0, 30.0, 60.0)
)

decision_generation_duration = Histogram(
    'decision_generation_duration_seconds',
    'Time to generate decisions',
    ['planner_type'],
    buckets=(0.01, 0.05, 0.1, 0.5, 1.0, 2.0)
)

guardrail_enforcement_duration = Histogram(
    'guardrail_enforcement_duration_seconds',
    'Time to apply guardrails',
    buckets=(0.001, 0.01, 0.05, 0.1, 0.5)
)

database_query_duration = Summary(
    'database_query_duration_seconds',
    'Database query duration',
    ['query_type']
)


# ===========================================
# SYSTEM STATE METRICS
# ===========================================

active_bots_gauge = Gauge(
    'active_bots',
    'Number of currently active bots'
)

bots_in_sleep_gauge = Gauge(
    'bots_in_sleep_window',
    'Number of bots currently in sleep window'
)

bots_in_cooldown_gauge = Gauge(
    'bots_in_session_cooldown',
    'Number of bots in session cooldown'
)

total_attacks_this_minute = Gauge(
    'total_attacks_this_minute',
    'Current attack count for circuit breaker'
)

global_targets_tracked = Gauge(
    'global_targets_tracked',
    'Number of players being tracked for dogpile'
)


# ===========================================
# MEMORY/LEARNING METRICS
# ===========================================

memory_relations_updated = Counter(
    'memory_relations_updated_total',
    'AI relation updates',
    ['bot_id', 'target_player', 'change_type']
)

memory_target_stats_updated = Counter(
    'memory_target_stats_updated_total',
    'Target stats learning events',
    ['bot_id']
)

memory_strategy_learned = Counter(
    'memory_strategy_learned_total',
    'Strategy performance learning events',
    ['bot_id', 'strategy_type']
)


# ===========================================
# RESOURCE METRICS
# ===========================================

resource_shortage_blocks = Counter(
    'resource_shortage_blocks_total',
    'Decisions blocked due to resource shortage',
    ['bot_id', 'resource_type']
)

unit_shortage_blocks = Counter(
    'unit_shortage_blocks_total',
    'Decisions blocked due to unit shortage',
    ['bot_id', 'unit_type']
)


# ===========================================
# ERROR METRICS
# ===========================================

http_errors_total = Counter(
    'http_errors_total',
    'HTTP errors when communicating with game',
    ['error_code', 'endpoint']
)

database_errors_total = Counter(
    'database_errors_total',
    'Database errors',
    ['error_type']
)

orchestrator_errors_total = Counter(
    'orchestrator_errors_total',
    'Orchestrator-level errors',
    ['error_type']
)


# ===========================================
# INFO METRICS
# ===========================================

system_info = Info(
    'ai_orchestrator_info',
    'AI Orchestrator system information'
)


# ===========================================
# METRIC HELPERS
# ===========================================

def track_tick_duration(bot_id: str):
    """Decorator to track tick duration"""
    def decorator(func):
        @wraps(func)
        async def wrapper(*args, **kwargs):
            start = time.time()
            try:
                result = await func(*args, **kwargs)
                return result
            finally:
                duration = time.time() - start
                tick_duration_seconds.labels(bot_id=bot_id).observe(duration)
        return wrapper
    return decorator


def track_planner_duration(planner_type: str):
    """Decorator to track planner execution time"""
    def decorator(func):
        @wraps(func)
        async def wrapper(*args, **kwargs):
            start = time.time()
            try:
                result = await func(*args, **kwargs)
                return result
            finally:
                duration = time.time() - start
                decision_generation_duration.labels(planner_type=planner_type).observe(duration)
        return wrapper
    return decorator


def track_database_query(query_type: str):
    """Decorator to track database query time"""
    def decorator(func):
        @wraps(func)
        async def wrapper(*args, **kwargs):
            start = time.time()
            try:
                result = await func(*args, **kwargs)
                return result
            finally:
                duration = time.time() - start
                database_query_duration.labels(query_type=query_type).observe(duration)
        return wrapper
    return decorator


# ===========================================
# METRICS SERVER
# ===========================================

def initialize_metrics(config: Dict):
    """Initialize system info metrics"""
    system_info.info({
        'version': '1.0.0',
        'environment': config.get('environment', 'production'),
        'bot_count': str(config.get('bot_count', 0)),
        'guardrails_enabled': 'true'
    })


def update_system_state(guardrail_stats: Dict):
    """Update system state gauges from guardrail stats"""
    if 'bots_in_sleep' in guardrail_stats:
        bots_in_sleep_gauge.set(guardrail_stats['bots_in_sleep'])
    
    if 'active_sessions' in guardrail_stats:
        bots_in_cooldown_gauge.set(guardrail_stats['active_sessions'])
    
    if 'system_attack_count_this_minute' in guardrail_stats:
        total_attacks_this_minute.set(guardrail_stats['system_attack_count_this_minute'])
    
    if 'global_targets_tracked' in guardrail_stats:
        global_targets_tracked.set(guardrail_stats['global_targets_tracked'])
