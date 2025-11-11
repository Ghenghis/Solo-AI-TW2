"""
Unit and Building Cost Definitions for TWLan
Based on standard Tribal Wars mechanics
"""

from typing import Dict, Tuple


class UnitCosts:
    """Production costs and stats for all unit types"""
    
    # Format: (wood, clay, iron, population, build_time_seconds)
    UNITS: Dict[str, Tuple[int, int, int, int, int]] = {
        'spear': (50, 30, 10, 1, 600),      # Spearman
        'sword': (30, 30, 70, 1, 900),      # Swordsman
        'axe': (60, 30, 40, 1, 840),        # Axeman
        'archer': (100, 30, 60, 1, 1080),   # Archer
        'spy': (50, 50, 20, 2, 1080),       # Scout
        'light': (125, 100, 250, 4, 1800),  # Light Cavalry
        'marcher': (250, 100, 150, 5, 2100), # Mounted Archer
        'heavy': (200, 150, 600, 6, 2700),  # Heavy Cavalry
        'ram': (300, 200, 200, 5, 2400),    # Ram
        'catapult': (320, 400, 100, 8, 3600), # Catapult
        'knight': (0, 0, 0, 10, 0),         # Paladin (special)
        'snob': (40000, 50000, 50000, 100, 0), # Noble (special)
    }
    
    @classmethod
    def get_cost(cls, unit_type: str) -> Tuple[int, int, int]:
        """Get (wood, clay, iron) cost for a unit"""
        if unit_type not in cls.UNITS:
            raise ValueError(f"Unknown unit type: {unit_type}")
        wood, clay, iron, _, _ = cls.UNITS[unit_type]
        return (wood, clay, iron)
    
    @classmethod
    def get_population(cls, unit_type: str) -> int:
        """Get population cost for a unit"""
        if unit_type not in cls.UNITS:
            raise ValueError(f"Unknown unit type: {unit_type}")
        return cls.UNITS[unit_type][3]
    
    @classmethod
    def get_build_time(cls, unit_type: str) -> int:
        """Get build time in seconds for a unit"""
        if unit_type not in cls.UNITS:
            raise ValueError(f"Unknown unit type: {unit_type}")
        return cls.UNITS[unit_type][4]


class BuildingCosts:
    """Construction costs for all building types"""
    
    # Base costs at level 1 (wood, clay, iron)
    BASE_COSTS: Dict[str, Tuple[int, int, int]] = {
        'main': (90, 80, 70),
        'barracks': (200, 170, 90),
        'stable': (270, 240, 260),
        'garage': (300, 240, 260),
        'church': (0, 0, 0),  # Special building
        'snob': (15000, 25000, 10000),
        'smith': (220, 180, 240),
        'place': (10, 40, 30),
        'statue': (0, 0, 0),  # Cannot be built
        'market': (100, 100, 100),
        'wood': (50, 60, 40),
        'stone': (65, 50, 40),
        'iron': (75, 65, 70),
        'farm': (60, 50, 40),
        'storage': (60, 50, 40),
        'hide': (50, 60, 50),
        'wall': (50, 100, 20),
    }
    
    # Cost multiplier per level (exponential growth)
    LEVEL_MULTIPLIER = 1.26
    
    @classmethod
    def get_cost(cls, building_type: str, level: int) -> Tuple[int, int, int]:
        """
        Get (wood, clay, iron) cost to upgrade building to specified level.
        
        Formula: base_cost * (multiplier ^ (level - 1))
        """
        if building_type not in cls.BASE_COSTS:
            raise ValueError(f"Unknown building type: {building_type}")
        
        if level < 1 or level > 30:
            raise ValueError(f"Invalid building level: {level}")
        
        base_wood, base_clay, base_iron = cls.BASE_COSTS[building_type]
        multiplier = cls.LEVEL_MULTIPLIER ** (level - 1)
        
        wood = int(base_wood * multiplier)
        clay = int(base_clay * multiplier)
        iron = int(base_iron * multiplier)
        
        return (wood, clay, iron)
    
    @classmethod
    def get_build_time(cls, building_type: str, level: int, main_level: int = 1) -> int:
        """
        Get build time in seconds for a building upgrade.
        
        Build time affected by HQ level (reduces time).
        """
        wood, clay, iron = cls.get_cost(building_type, level)
        total_resources = wood + clay + iron
        
        # Base formula: total_resources / 2.5 seconds
        # Reduced by HQ level
        base_time = total_resources / 2.5
        reduction = 1 - (main_level * 0.05)  # 5% per HQ level
        reduction = max(0.5, reduction)  # Max 50% reduction
        
        return int(base_time * reduction)


class ResearchCosts:
    """Technology research costs in the smithy"""
    
    # Format: (wood, clay, iron, research_time_base_seconds)
    RESEARCH: Dict[str, Dict[int, Tuple[int, int, int, int]]] = {
        'spear': {
            1: (800, 840, 820, 1800),
            2: (1000, 1050, 1025, 2160),
            3: (1260, 1320, 1290, 2592),
        },
        'sword': {
            1: (950, 850, 1100, 2160),
            2: (1187, 1062, 1375, 2592),
            3: (1484, 1328, 1719, 3110),
        },
        'axe': {
            1: (900, 800, 900, 2160),
            2: (1125, 1000, 1125, 2592),
            3: (1406, 1250, 1406, 3110),
        },
        # Add more units as needed
    }
    
    @classmethod
    def get_cost(cls, unit_type: str, level: int) -> Tuple[int, int, int]:
        """Get (wood, clay, iron) cost for research level"""
        if unit_type not in cls.RESEARCH:
            raise ValueError(f"Unit type {unit_type} cannot be researched")
        
        if level not in cls.RESEARCH[unit_type]:
            raise ValueError(f"Research level {level} not defined for {unit_type}")
        
        wood, clay, iron, _ = cls.RESEARCH[unit_type][level]
        return (wood, clay, iron)


# Convenience functions for decision_resolver.py
def get_build_cost(building: str, level: int) -> Tuple[int, int, int]:
    """Get building upgrade cost"""
    return BuildingCosts.get_cost(building, level)


def get_recruit_cost(units: Dict[str, int]) -> Tuple[int, int, int]:
    """Get total recruitment cost for multiple units"""
    total_wood = 0
    total_clay = 0
    total_iron = 0
    
    for unit_type, quantity in units.items():
        wood, clay, iron = UnitCosts.get_cost(unit_type)
        total_wood += wood * quantity
        total_clay += clay * quantity
        total_iron += iron * quantity
    
    return (total_wood, total_clay, total_iron)


def get_research_cost(unit_type: str, level: int) -> Tuple[int, int, int]:
    """Get research cost"""
    return ResearchCosts.get_cost(unit_type, level)
