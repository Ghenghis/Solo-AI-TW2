"""
AI Personality System - 5 distinct play styles
Each personality makes different strategic decisions
"""

from abc import ABC, abstractmethod
from typing import Dict, List
from dataclasses import dataclass
import random


@dataclass
class Decision:
    """Represents a bot's decision to take action"""
    action_type: str  # 'build', 'recruit', 'attack', 'support', 'diplomacy'
    village_id: int
    priority: float
    details: Dict
    
    def to_dict(self):
        return {
            'action_type': self.action_type,
            'village_id': self.village_id,
            'priority': self.priority,
            **self.details
        }


class BasePersonality(ABC):
    """Base class for all AI personalities"""
    
    def __init__(self, config):
        self.config = config
        self.name = "base"
    
    @abstractmethod
    def prioritize_buildings(self) -> List[str]:
        """Return ordered list of building priorities"""
        pass
    
    @abstractmethod
    def decide_attack_targets(self, game_state: Dict) -> List[Decision]:
        """Decide which villages to attack"""
        pass
    
    @abstractmethod
    def decide_build_queue(self, game_state: Dict) -> List[Decision]:
        """Decide what to build next"""
        pass
    
    @abstractmethod
    def decide_recruitment(self, game_state: Dict) -> List[Decision]:
        """Decide which units to recruit"""
        pass
    
    def add_randomness(self, value: float) -> float:
        """Add human-like randomness to decisions"""
        variance = self.config.bot_randomness
        return value * random.uniform(1 - variance, 1 + variance)


class WarmongPersonality(BasePersonality):
    """Aggressive raider - focuses on military and attacks"""
    
    def __init__(self, config):
        super().__init__(config)
        self.name = "warmonger"
    
    def prioritize_buildings(self) -> List[str]:
        return [
            'barracks',    # Rush military
            'smithy',      # Upgrade weapons
            'stable',      # Cavalry for raids
            'rally_point', # Command center
            'wall',        # Basic defense
            'farm',        # Support population
            'timber',      # Resources for troops
            'clay',
            'iron',
            'warehouse',
        ]
    
    def decide_attack_targets(self, game_state: Dict) -> List[Decision]:
        """Aggressive targeting - high raid frequency"""
        decisions = []
        villages = game_state.get('villages', [])
        nearby = game_state.get('nearby_villages', [])
        
        for village in villages:
            units = village.get('units', {})
            
            # Only attack if we have offensive units
            offensive_power = units.get('axe', 0) + units.get('light', 0) * 2
            
            if offensive_power < 100:
                continue
            
            # Target weaker villages within range
            for target in nearby[:10]:  # Top 10 nearest
                if target.get('points', 9999) < village.get('points', 0):
                    decisions.append(Decision(
                        action_type='attack',
                        village_id=village['id_village'],
                        priority=self.add_randomness(0.9),
                        details={
                            'to_village': target['id_village'],
                            'units': {'axe': min(50, units.get('axe', 0))}
                        }
                    ))
        
        return decisions
    
    def decide_build_queue(self, game_state: Dict) -> List[Decision]:
        """Prioritize military buildings"""
        decisions = []
        priorities = self.prioritize_buildings()
        
        for village in game_state.get('villages', []):
            buildings = village.get('buildings', {})
            
            for building in priorities[:5]:  # Top 5 priority
                current_level = buildings.get(building, 0)
                
                if current_level < 20:  # Keep upgrading
                    decisions.append(Decision(
                        action_type='build',
                        village_id=village['id_village'],
                        priority=self.add_randomness(0.8),
                        details={'building': building}
                    ))
                    break  # One at a time per village
        
        return decisions
    
    def decide_recruitment(self, game_state: Dict) -> List[Decision]:
        """Recruit offensive units constantly"""
        decisions = []
        
        for village in game_state.get('villages', []):
            resources = village.get('resources', {})
            
            # Prefer axes (cheap offensive)
            if resources.get('wood', 0) > 500:
                decisions.append(Decision(
                    action_type='recruit',
                    village_id=village['id_village'],
                    priority=self.add_randomness(0.85),
                    details={'units': {'axe': 10}}
                ))
        
        return decisions


class TurtlePersonality(BasePersonality):
    """Defensive economist - maxes resources and defense"""
    
    def __init__(self, config):
        super().__init__(config)
        self.name = "turtle"
    
    def prioritize_buildings(self) -> List[str]:
        return [
            'timber',      # Max resources first
            'clay',
            'iron',
            'warehouse',   # Storage
            'farm',
            'wall',        # Heavy defense
            'barracks',    # Defensive units
            'smithy',      # Defensive tech
        ]
    
    def decide_attack_targets(self, game_state: Dict) -> List[Decision]:
        """Rarely attacks - only barbarian villages"""
        decisions = []
        villages = game_state.get('villages', [])
        nearby = game_state.get('nearby_villages', [])
        
        for village in villages:
            # Only attack if very safe and target is barbarian
            for target in nearby:
                if target.get('id_user') is None:  # Barbarian
                    decisions.append(Decision(
                        action_type='attack',
                        village_id=village['id_village'],
                        priority=self.add_randomness(0.3),  # Low priority
                        details={
                            'to_village': target['id_village'],
                            'units': {'spear': 30, 'sword': 20}
                        }
                    ))
                    break
        
        return decisions
    
    def decide_build_queue(self, game_state: Dict) -> List[Decision]:
        """Max out resource production"""
        decisions = []
        
        for village in game_state.get('villages', []):
            buildings = village.get('buildings', {})
            
            # Level resources evenly
            resource_buildings = ['timber', 'clay', 'iron']
            min_level = min(buildings.get(b, 0) for b in resource_buildings)
            
            for building in resource_buildings:
                if buildings.get(building, 0) == min_level:
                    decisions.append(Decision(
                        action_type='build',
                        village_id=village['id_village'],
                        priority=self.add_randomness(0.9),
                        details={'building': building}
                    ))
                    break
        
        return decisions
    
    def decide_recruitment(self, game_state: Dict) -> List[Decision]:
        """Recruit defensive units"""
        decisions = []
        
        for village in game_state.get('villages', []):
            units = village.get('units', {})
            
            # Build defensive wall of spears
            if units.get('spear', 0) < 200:
                decisions.append(Decision(
                    action_type='recruit',
                    village_id=village['id_village'],
                    priority=self.add_randomness(0.7),
                    details={'units': {'spear': 15}}
                ))
        
        return decisions


class BalancedPersonality(BasePersonality):
    """Standard player - balanced approach"""
    
    def __init__(self, config):
        super().__init__(config)
        self.name = "balanced"
    
    def prioritize_buildings(self) -> List[str]:
        return [
            'headquarters',
            'barracks',
            'timber', 'clay', 'iron',
            'farm',
            'warehouse',
            'stable',
            'smithy',
            'wall',
        ]
    
    def decide_attack_targets(self, game_state: Dict) -> List[Decision]:
        """Moderate raiding - balanced risk"""
        decisions = []
        villages = game_state.get('villages', [])
        nearby = game_state.get('nearby_villages', [])
        
        for village in villages:
            units = village.get('units', {})
            points = village.get('points', 0)
            
            # Attack targets slightly weaker
            for target in nearby[:5]:
                if target.get('points', 9999) < points * 0.8:
                    decisions.append(Decision(
                        action_type='attack',
                        village_id=village['id_village'],
                        priority=self.add_randomness(0.6),
                        details={
                            'to_village': target['id_village'],
                            'units': {'axe': 30, 'light': 10}
                        }
                    ))
        
        return decisions
    
    def decide_build_queue(self, game_state: Dict) -> List[Decision]:
        """Follow recommended build order"""
        decisions = []
        priorities = self.prioritize_buildings()
        
        for village in game_state.get('villages', []):
            buildings = village.get('buildings', {})
            
            for building in priorities:
                if buildings.get(building, 0) < 15:
                    decisions.append(Decision(
                        action_type='build',
                        village_id=village['id_village'],
                        priority=self.add_randomness(0.7),
                        details={'building': building}
                    ))
                    break
        
        return decisions
    
    def decide_recruitment(self, game_state: Dict) -> List[Decision]:
        """Mix of offensive and defensive"""
        decisions = []
        
        for village in game_state.get('villages', []):
            decisions.append(Decision(
                action_type='recruit',
                village_id=village['id_village'],
                priority=self.add_randomness(0.6),
                details={'units': {'spear': 5, 'axe': 5, 'light': 2}}
            ))
        
        return decisions


class DiplomatPersonality(BasePersonality):
    """Support player - helps allies, forms alliances"""
    
    def __init__(self, config):
        super().__init__(config)
        self.name = "diplomat"
    
    def prioritize_buildings(self) -> List[str]:
        return [
            'farm',
            'warehouse',
            'timber', 'clay', 'iron',
            'market',       # For resource trading
            'rally_point',
            'barracks',
        ]
    
    def decide_attack_targets(self, game_state: Dict) -> List[Decision]:
        """Rarely attacks - defensive only"""
        return []  # Diplomats don't initiate attacks
    
    def decide_build_queue(self, game_state: Dict) -> List[Decision]:
        """Focus on economy and support"""
        decisions = []
        
        for village in game_state.get('villages', []):
            buildings = village.get('buildings', {})
            
            if buildings.get('warehouse', 0) < buildings.get('timber', 0):
                decisions.append(Decision(
                    action_type='build',
                    village_id=village['id_village'],
                    priority=self.add_randomness(0.8),
                    details={'building': 'warehouse'}
                ))
        
        return decisions
    
    def decide_recruitment(self, game_state: Dict) -> List[Decision]:
        """Light defensive force"""
        decisions = []
        
        for village in game_state.get('villages', []):
            units = village.get('units', {})
            
            if units.get('spear', 0) < 50:
                decisions.append(Decision(
                    action_type='recruit',
                    village_id=village['id_village'],
                    priority=self.add_randomness(0.5),
                    details={'units': {'spear': 5}}
                ))
        
        return decisions


class ChaosPersonality(BasePersonality):
    """Random/unpredictable - weird strategies"""
    
    def __init__(self, config):
        super().__init__(config)
        self.name = "chaos"
    
    def prioritize_buildings(self) -> List[str]:
        buildings = ['barracks', 'stable', 'workshop', 'timber', 'clay', 'iron', 
                    'farm', 'warehouse', 'wall', 'smithy']
        random.shuffle(buildings)
        return buildings
    
    def decide_attack_targets(self, game_state: Dict) -> List[Decision]:
        """Random attacks at weird times"""
        decisions = []
        villages = game_state.get('villages', [])
        nearby = game_state.get('nearby_villages', [])
        
        if random.random() < 0.4:  # 40% chance each cycle
            for village in villages:
                if nearby:
                    target = random.choice(nearby)
                    decisions.append(Decision(
                        action_type='attack',
                        village_id=village['id_village'],
                        priority=random.random(),
                        details={
                            'to_village': target['id_village'],
                            'units': {'axe': random.randint(10, 50)}
                        }
                    ))
        
        return decisions
    
    def decide_build_queue(self, game_state: Dict) -> List[Decision]:
        """Random building choices"""
        decisions = []
        
        for village in game_state.get('villages', []):
            if random.random() < 0.6:
                building = random.choice(self.prioritize_buildings())
                decisions.append(Decision(
                    action_type='build',
                    village_id=village['id_village'],
                    priority=random.random(),
                    details={'building': building}
                ))
        
        return decisions
    
    def decide_recruitment(self, game_state: Dict) -> List[Decision]:
        """Random unit mix"""
        decisions = []
        unit_types = ['spear', 'sword', 'axe', 'light', 'heavy']
        
        for village in game_state.get('villages', []):
            if random.random() < 0.7:
                unit = random.choice(unit_types)
                count = random.randint(5, 20)
                decisions.append(Decision(
                    action_type='recruit',
                    village_id=village['id_village'],
                    priority=random.random(),
                    details={'units': {unit: count}}
                ))
        
        return decisions


class PersonalityFactory:
    """Factory to create personality instances"""
    
    @staticmethod
    def create(personality_type: str, config) -> BasePersonality:
        personalities = {
            'warmonger': WarmongPersonality,
            'turtle': TurtlePersonality,
            'balanced': BalancedPersonality,
            'diplomat': DiplomatPersonality,
            'chaos': ChaosPersonality,
        }
        
        personality_class = personalities.get(personality_type, BalancedPersonality)
        return personality_class(config)
