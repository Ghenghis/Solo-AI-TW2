"""
Unit tests for GuardrailEnforcer
Tests all 4 layers of protection
"""

import pytest
from datetime import datetime, timedelta
from bots.state import Decision
from core.guardrails import GuardrailEnforcer
from bots.personalities_enhanced import PersonalityProfile


class TestGuardrailEnforcer:
    """Test suite for guardrail enforcement"""
    
    def test_sleep_window_blocks_attacks(self, mock_config, mock_bot_state, mock_world_snapshot):
        """Test that sleep windows block attacks but allow passive actions"""
        # Enable sleep windows
        mock_config.enable_sleep_windows = True
        
        decisions = [
            Decision(
                action_type='attack',
                priority=0.8,
                village_id=100,
                target_village_id=200,
                details={}
            ),
            Decision(
                action_type='build',
                priority=0.6,
                village_id=100,
                details={'building': 'barracks'}
            )
        ]
        
        personality = PersonalityProfile(
            name='balanced',
            aggression=0.5,
            economy_focus=0.5,
            diplomacy_focus=0.5
        )
        
        # Simulate bot in sleep window
        now = datetime.utcnow()
        GuardrailEnforcer._sleep_windows[mock_bot_state.player_id] = (now.hour, (now.hour + 1) % 24)
        
        filtered = GuardrailEnforcer.apply(
            mock_bot_state,
            decisions,
            mock_world_snapshot,
            mock_config,
            personality
        )
        
        # Should filter out attack, keep build
        assert len(filtered) == 1
        assert filtered[0].action_type == 'build'
    
    def test_per_target_spam_limits(self, mock_config, mock_bot_state, mock_world_snapshot):
        """Test per-target spam prevention"""
        decisions = [
            Decision(
                action_type='attack',
                priority=0.8,
                village_id=100,
                target_village_id=200,
                details={}
            ),
            Decision(
                action_type='attack',
                priority=0.7,
                village_id=101,
                target_village_id=200,
                details={}
            ),
            Decision(
                action_type='attack',
                priority=0.6,
                village_id=100,
                target_village_id=200,
                details={}
            )
        ]
        
        personality = PersonalityProfile(
            name='warmonger',
            aggression=0.9,
            economy_focus=0.3,
            diplomacy_focus=0.2
        )
        
        # Config: max 2 attacks per village per tick
        mock_config.max_attacks_per_village_per_tick = 2
        
        filtered = GuardrailEnforcer.apply(
            mock_bot_state,
            decisions,
            mock_world_snapshot,
            mock_config,
            personality
        )
        
        # Should keep max 2 attacks to village 200
        attack_count = sum(1 for d in filtered if d.action_type == 'attack' and d.target_village_id == 200)
        assert attack_count <= 2
    
    def test_personality_scaling_warmonger(self, mock_config, mock_bot_state, mock_world_snapshot):
        """Test warmonger gets 1.15x attack priority"""
        decisions = [
            Decision(
                action_type='attack',
                priority=0.5,
                village_id=100,
                target_village_id=200,
                details={}
            )
        ]
        
        personality = PersonalityProfile(
            name='warmonger',
            aggression=0.9,
            economy_focus=0.3,
            diplomacy_focus=0.2
        )
        
        mock_config.warmonger_attack_multiplier = 1.15
        
        filtered = GuardrailEnforcer.apply(
            mock_bot_state,
            decisions,
            mock_world_snapshot,
            mock_config,
            personality
        )
        
        # Priority should be scaled up
        assert filtered[0].priority > 0.5
        assert filtered[0].priority == pytest.approx(0.575, rel=0.01)
    
    def test_personality_scaling_turtle(self, mock_config, mock_bot_state, mock_world_snapshot):
        """Test turtle gets 0.4x attack priority"""
        decisions = [
            Decision(
                action_type='attack',
                priority=0.8,
                village_id=100,
                target_village_id=200,
                details={}
            )
        ]
        
        personality = PersonalityProfile(
            name='turtle',
            aggression=0.2,
            economy_focus=0.7,
            diplomacy_focus=0.5
        )
        
        mock_config.turtle_attack_multiplier = 0.4
        
        filtered = GuardrailEnforcer.apply(
            mock_bot_state,
            decisions,
            mock_world_snapshot,
            mock_config,
            personality
        )
        
        # Priority should be scaled down significantly
        assert filtered[0].priority < 0.8
        assert filtered[0].priority == pytest.approx(0.32, rel=0.01)
    
    def test_anti_dogpile_reduces_priority(self, mock_config, mock_bot_state, mock_world_snapshot):
        """Test anti-dogpile when 5+ bots attack same player"""
        decisions = [
            Decision(
                action_type='attack',
                priority=0.8,
                village_id=100,
                target_village_id=200,
                details={}
            )
        ]
        
        personality = PersonalityProfile(
            name='balanced',
            aggression=0.5,
            economy_focus=0.5,
            diplomacy_focus=0.5
        )
        
        # Simulate 5 bots already attacking player 2
        GuardrailEnforcer._global_attack_targets[2] = 5
        mock_config.dogpile_threshold = 5
        
        filtered = GuardrailEnforcer.apply(
            mock_bot_state,
            decisions,
            mock_world_snapshot,
            mock_config,
            personality
        )
        
        # Priority should be reduced due to dogpile
        assert filtered[0].priority < 0.8
    
    def test_reset_global_state(self):
        """Test global state reset functionality"""
        GuardrailEnforcer._global_attack_targets[1] = 10
        GuardrailEnforcer._global_attack_targets[2] = 5
        
        GuardrailEnforcer.reset_global_state()
        
        assert len(GuardrailEnforcer._global_attack_targets) == 0
    
    def test_empty_decisions_returns_empty(self, mock_config, mock_bot_state, mock_world_snapshot):
        """Test that empty decision list returns empty"""
        personality = PersonalityProfile(
            name='balanced',
            aggression=0.5,
            economy_focus=0.5,
            diplomacy_focus=0.5
        )
        
        filtered = GuardrailEnforcer.apply(
            mock_bot_state,
            [],
            mock_world_snapshot,
            mock_config,
            personality
        )
        
        assert len(filtered) == 0
