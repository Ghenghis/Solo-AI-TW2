"""
Enhanced Personality System - Numeric profiles (not vibes!)
Each personality is a concrete set of weights that drive decisions
"""

from dataclasses import dataclass
from typing import Dict, List


@dataclass
class PersonalityProfile:
    """
    Concrete personality configuration with numeric weights
    All values 0.0 - 1.0 unless specified
    """
    name: str
    
    # Core focuses
    eco_focus: float          # 0=ignore economy, 1=max economy
    military_focus: float     # 0=minimal army, 1=huge army
    defense_bias: float       # 0=pure offense, 1=pure defense
    
    # Behavior traits
    aggression: float         # Attack frequency & risk tolerance
    ally_loyalty: float       # Honor alliances vs betray
    opportunism: float        # Exploit weakness vs stick to plan
    randomness: float         # Decision variance (human-like)
    
    # Social
    tribe_focus: float        # Desire to join/form tribes
    diplomacy_weight: float   # Time spent on relations
    
    # Specific behaviors
    farm_intensity: float     # How aggressively farm barbs
    expansion_drive: float    # Desire to conquer new villages
    support_allies: float     # Send support to allies
    
    # Risk tolerance
    risk_tolerance: float     # Accept risky attacks
    retreat_threshold: float  # When to pull back (0=never, 1=always)
    
    def get_build_priorities(self, game_phase: str) -> List[str]:
        """Return building priority list based on personality & phase"""
        # Base priorities
        if game_phase == "early":
            if self.eco_focus > 0.7:
                return ['timber', 'clay', 'iron', 'warehouse', 'farm', 'headquarters', 'barracks']
            elif self.military_focus > 0.7:
                return ['barracks', 'timber', 'clay', 'iron', 'smithy', 'stable', 'farm']
            else:
                return ['headquarters', 'barracks', 'timber', 'clay', 'iron', 'farm', 'warehouse']
        
        elif game_phase == "mid":
            if self.defense_bias > 0.6:
                return ['wall', 'warehouse', 'farm', 'timber', 'clay', 'iron', 'barracks', 'smithy']
            elif self.aggression > 0.7:
                return ['stable', 'smithy', 'barracks', 'workshop', 'farm', 'warehouse']
            else:
                return ['warehouse', 'farm', 'stable', 'barracks', 'smithy', 'timber', 'clay', 'iron']
        
        else:  # late
            if self.expansion_drive > 0.7:
                return ['academy', 'smithy', 'headquarters', 'stable', 'barracks', 'farm']
            else:
                return ['farm', 'warehouse', 'headquarters', 'smithy', 'stable', 'wall']
    
    def get_unit_composition_target(self, game_phase: str, village_role: str) -> Dict[str, int]:
        """
        Return target unit counts based on personality, phase, and village role
        """
        if game_phase == "early":
            if self.military_focus > 0.6:
                return {'spear': 50, 'axe': 30, 'light': 5}
            else:
                return {'spear': 30, 'axe': 10}
        
        elif game_phase == "mid":
            if village_role == "offense" or self.aggression > 0.6:
                return {
                    'axe': int(100 * self.aggression),
                    'light': int(50 * self.aggression),
                    'ram': int(10 * self.aggression) if self.aggression > 0.5 else 0,
                    'spear': int(50 * (1 - self.aggression))
                }
            elif village_role == "defense" or self.defense_bias > 0.6:
                return {
                    'spear': int(200 * self.defense_bias),
                    'sword': int(100 * self.defense_bias),
                    'heavy': int(20 * self.defense_bias),
                    'axe': int(30 * (1 - self.defense_bias))
                }
            else:
                return {'spear': 100, 'sword': 50, 'axe': 50, 'light': 20}
        
        else:  # late
            if village_role == "offense":
                return {
                    'axe': 300,
                    'light': 150,
                    'ram': 50,
                    'catapult': 30,
                    'snob': 1 if self.expansion_drive > 0.5 else 0
                }
            elif village_role == "defense":
                return {
                    'spear': 400,
                    'sword': 200,
                    'heavy': 50,
                    'archer': 100 if self.defense_bias > 0.7 else 0
                }
            else:
                return {'spear': 200, 'sword': 100, 'axe': 150, 'light': 50}


# ============================================
# Concrete Personality Definitions
# ============================================

WARMONGER = PersonalityProfile(
    name="warmonger",
    eco_focus=0.4,           # Minimal economy
    military_focus=0.95,     # Heavy military investment
    defense_bias=0.2,        # Mostly offense
    aggression=0.9,          # Very aggressive
    ally_loyalty=0.4,        # Will betray for advantage
    opportunism=0.85,        # Exploits weakness
    randomness=0.3,
    tribe_focus=0.7,         # Joins tribes for power
    diplomacy_weight=0.3,
    farm_intensity=0.9,      # Farms constantly
    expansion_drive=0.85,    # Wants to expand
    support_allies=0.3,      # Rarely supports
    risk_tolerance=0.8,      # Accepts high risk
    retreat_threshold=0.2,   # Rarely retreats
)

TURTLE = PersonalityProfile(
    name="turtle",
    eco_focus=0.9,           # Maximum economy
    military_focus=0.6,      # Moderate military
    defense_bias=0.9,        # Heavy defense
    aggression=0.1,          # Rarely attacks
    ally_loyalty=0.85,       # Very loyal
    opportunism=0.2,         # Sticks to plan
    randomness=0.2,
    tribe_focus=0.6,
    diplomacy_weight=0.6,
    farm_intensity=0.3,      # Light farming
    expansion_drive=0.2,     # Content with what they have
    support_allies=0.9,      # Heavily supports allies
    risk_tolerance=0.2,      # Very risk-averse
    retreat_threshold=0.8,   # Pulls back easily
)

BALANCED = PersonalityProfile(
    name="balanced",
    eco_focus=0.6,
    military_focus=0.6,
    defense_bias=0.5,
    aggression=0.5,
    ally_loyalty=0.6,
    opportunism=0.5,
    randomness=0.3,
    tribe_focus=0.7,
    diplomacy_weight=0.5,
    farm_intensity=0.6,
    expansion_drive=0.6,
    support_allies=0.6,
    risk_tolerance=0.5,
    retreat_threshold=0.5,
)

DIPLOMAT = PersonalityProfile(
    name="diplomat",
    eco_focus=0.8,
    military_focus=0.4,
    defense_bias=0.7,
    aggression=0.2,
    ally_loyalty=0.95,       # Extremely loyal
    opportunism=0.3,
    randomness=0.25,
    tribe_focus=0.9,         # Very tribe-focused
    diplomacy_weight=0.9,    # Spends time on relations
    farm_intensity=0.4,
    expansion_drive=0.3,
    support_allies=0.95,     # Always helps allies
    risk_tolerance=0.3,
    retreat_threshold=0.7,
)

CHAOS = PersonalityProfile(
    name="chaos",
    eco_focus=0.5,
    military_focus=0.7,
    defense_bias=0.4,
    aggression=0.7,
    ally_loyalty=0.3,        # Unreliable ally
    opportunism=0.9,         # Extreme opportunist
    randomness=0.8,          # Very random
    tribe_focus=0.5,
    diplomacy_weight=0.4,
    farm_intensity=0.7,
    expansion_drive=0.7,
    support_allies=0.4,
    risk_tolerance=0.9,      # Accepts crazy risks
    retreat_threshold=0.3,
)


# Personality registry
PERSONALITIES: Dict[str, PersonalityProfile] = {
    "warmonger": WARMONGER,
    "turtle": TURTLE,
    "balanced": BALANCED,
    "diplomat": DIPLOMAT,
    "chaos": CHAOS,
}


def get_personality(name: str) -> PersonalityProfile:
    """Get personality profile by name"""
    return PERSONALITIES.get(name, BALANCED)
