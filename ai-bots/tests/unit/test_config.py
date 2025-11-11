"""
Unit tests for configuration management
"""

import pytest
import os
from core.config import Config


class TestConfig:
    """Test configuration validation and loading"""
    
    def test_personality_distribution_validation(self):
        """Test that personality percentages must sum to 100"""
        with pytest.raises(ValueError, match="must sum to 100"):
            Config(
                db_host='localhost', db_port=3306, db_name='test',
                db_user='test', db_password='test',
                game_base_url='http://test', game_session_timeout=3600,
                bot_count=10, bot_tick_rate=60, bot_randomness=0.3,
                personality_warmonger=30,  # These sum to 110, should fail
                personality_turtle=30,
                personality_balanced=30,
                personality_diplomat=10,
                personality_chaos=10,
                max_concurrent_bots=10, request_timeout=30,
                db_pool_size=10, cache_ttl=300,
                log_level='INFO', log_format='json', metrics_port=9090,
                enable_metrics=True, enable_auto_alliance=True,
                enable_night_bonus=True, enable_adaptive_strategy=True,
                max_attacks_per_hour=50, max_build_queue_length=5,
                min_action_interval=5,
                enable_sleep_windows=True, min_sleep_hours=3, max_sleep_hours=5,
                max_attacks_per_village_per_tick=2,
                max_attacks_per_player_per_tick=4,
                max_attacks_per_player_per_hour=10,
                harassment_window_hours=1,
                max_attacks_per_player_per_harassment_window=5,
                dogpile_threshold=5,
                min_reaction_delay_minutes=5, max_reaction_delay_minutes=15,
                enable_session_rhythm=True,
                min_session_duration_minutes=10, max_session_duration_minutes=30,
                min_session_cooldown_minutes=5, max_session_cooldown_minutes=15,
                failed_attack_cooldown_minutes=15,
                max_system_attacks_per_minute=100,
                turtle_attack_multiplier=0.4, diplomat_attack_multiplier=0.6,
                warmonger_attack_multiplier=1.15, diplomat_support_multiplier=1.3
            )
            # This should raise ValueError from from_env() validation
    
    def test_validate_bot_count(self, mock_config):
        """Test bot count validation"""
        mock_config.bot_count = 0
        with pytest.raises(ValueError, match="BOT_COUNT"):
            mock_config.validate()
    
    def test_validate_tick_rate(self, mock_config):
        """Test tick rate validation"""
        mock_config.bot_tick_rate = 5
        with pytest.raises(ValueError, match="BOT_TICK_RATE"):
            mock_config.validate()
    
    def test_validate_randomness_range(self, mock_config):
        """Test randomness must be 0-1"""
        mock_config.bot_randomness = 1.5
        with pytest.raises(ValueError, match="BOT_RANDOMNESS"):
            mock_config.validate()
        
        mock_config.bot_randomness = -0.1
        with pytest.raises(ValueError, match="BOT_RANDOMNESS"):
            mock_config.validate()
    
    def test_validate_concurrent_bots(self, mock_config):
        """Test concurrent bots validation"""
        mock_config.max_concurrent_bots = 0
        with pytest.raises(ValueError, match="MAX_CONCURRENT_BOTS"):
            mock_config.validate()
    
    def test_db_url_construction(self, mock_config):
        """Test database URL construction"""
        url = mock_config.db_url
        assert 'mysql+mariadb://' in url
        assert mock_config.db_user in url
        assert mock_config.db_host in url
        assert str(mock_config.db_port) in url
        assert mock_config.db_name in url
    
    def test_valid_config_passes(self, mock_config):
        """Test that valid config passes validation"""
        # Should not raise
        mock_config.validate()
