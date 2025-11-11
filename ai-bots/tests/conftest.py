"""
PyTest configuration and fixtures for AI bot system tests
"""

import pytest
import asyncio
from typing import Dict, List
from datetime import datetime

# Test fixtures

@pytest.fixture
def mock_config():
    """Mock configuration for testing"""
    from core.config import Config
    
    return Config(
        # Database
        db_host='localhost',
        db_port=3306,
        db_name='twlan_test',
        db_user='test',
        db_password='test',
        
        # Game Server
        game_base_url='http://localhost:8200',
        game_session_timeout=3600,
        
        # Bot Configuration
        bot_count=10,
        bot_tick_rate=60,
        bot_randomness=0.3,
        
        # Personalities
        personality_warmonger=20,
        personality_turtle=20,
        personality_balanced=30,
        personality_diplomat=15,
        personality_chaos=15,
        
        # Performance
        max_concurrent_bots=5,
        request_timeout=30,
        db_pool_size=10,
        cache_ttl=300,
        
        # Logging
        log_level='DEBUG',
        log_format='json',
        metrics_port=9090,
        
        # Feature Flags
        enable_metrics=False,  # Disable for tests
        enable_auto_alliance=True,
        enable_night_bonus=True,
        enable_adaptive_strategy=True,
        
        # Safety Limits
        max_attacks_per_hour=50,
        max_build_queue_length=5,
        min_action_interval=5,
        
        # Guardrails
        enable_sleep_windows=False,  # Disable for tests
        min_sleep_hours=3,
        max_sleep_hours=5,
        max_attacks_per_village_per_tick=2,
        max_attacks_per_player_per_tick=4,
        max_attacks_per_player_per_hour=10,
        harassment_window_hours=1,
        max_attacks_per_player_per_harassment_window=5,
        dogpile_threshold=5,
        min_reaction_delay_minutes=0,  # Disable for tests
        max_reaction_delay_minutes=0,
        enable_session_rhythm=False,  # Disable for tests
        min_session_duration_minutes=10,
        max_session_duration_minutes=30,
        min_session_cooldown_minutes=5,
        max_session_cooldown_minutes=15,
        failed_attack_cooldown_minutes=15,
        max_system_attacks_per_minute=100,
        turtle_attack_multiplier=0.4,
        diplomat_attack_multiplier=0.6,
        warmonger_attack_multiplier=1.15,
        diplomat_support_multiplier=1.3,
    )


@pytest.fixture
def mock_bot_state():
    """Mock bot state for testing"""
    from bots.state import AIBotState
    
    return AIBotState(
        player_id=1,
        name='TestBot',
        tribe_id=None,
        villages=[100, 101],
        personality_type='balanced',
        created_at=datetime.utcnow()
    )


@pytest.fixture
def mock_world_snapshot():
    """Mock world snapshot for testing"""
    from core.world import WorldSnapshot
    
    return WorldSnapshot(
        villages={
            100: {
                'id': 100,
                'player_id': 1,
                'name': 'TestVillage1',
                'x': 500,
                'y': 500,
                'wood': 1000,
                'clay': 1000,
                'iron': 1000,
                'population': 10,
                'units': {'spear': 50, 'sword': 30}
            },
            101: {
                'id': 101,
                'player_id': 1,
                'name': 'TestVillage2',
                'x': 501,
                'y': 500,
                'wood': 2000,
                'clay': 2000,
                'iron': 2000,
                'population': 15,
                'units': {'spear': 100, 'sword': 50}
            },
            200: {
                'id': 200,
                'player_id': 2,
                'name': 'TargetVillage',
                'x': 510,
                'y': 510,
                'wood': 500,
                'clay': 500,
                'iron': 500,
                'population': 8,
                'units': {'spear': 20}
            }
        },
        players={
            1: {'id': 1, 'name': 'TestPlayer', 'tribe_id': None, 'points': 1000},
            2: {'id': 2, 'name': 'TargetPlayer', 'tribe_id': None, 'points': 500}
        },
        tribes={},
        timestamp=datetime.utcnow()
    )


@pytest.fixture
def event_loop():
    """Create event loop for async tests"""
    loop = asyncio.get_event_loop_policy().new_event_loop()
    yield loop
    loop.close()
