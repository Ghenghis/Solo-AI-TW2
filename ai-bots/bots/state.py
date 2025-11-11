"""
AI Bot State Management - Concrete data structures
Tracks relations, known world state, dynamic flags
"""

from dataclasses import dataclass, field
from typing import Dict, List, Optional
from datetime import datetime
from enum import Enum


class GamePhase(Enum):
    """Game progression phases"""
    EARLY = "early"      # <500 points, focus econ
    MID = "mid"          # 500-3000 points, balanced
    LATE = "late"        # 3000+ points, nobles & conquest


class RelationType(Enum):
    """Relation categories based on score"""
    ALLY = "ally"        # >= 40
    NEUTRAL = "neutral"  # -10 to 40
    ENEMY = "enemy"      # <= -10


@dataclass
class Relation:
    """Relationship tracking between bot and another player"""
    player_id: int
    score: float            # -100 to 100
    last_update: datetime
    events: List[str] = field(default_factory=list)  # Recent event log
    
    @property
    def relation_type(self) -> RelationType:
        """Categorize relation based on score"""
        if self.score >= 40:
            return RelationType.ALLY
        elif self.score <= -10:
            return RelationType.ENEMY
        else:
            return RelationType.NEUTRAL
    
    def apply_event(self, event_type: str, value: float, timestamp: datetime):
        """Update relation score based on event"""
        self.score += value
        self.score = max(-100, min(100, self.score))  # Clamp
        self.last_update = timestamp
        self.events.append(f"{timestamp.isoformat()}: {event_type} ({value:+.1f})")
        
        # Keep only last 20 events
        if len(self.events) > 20:
            self.events = self.events[-20:]


@dataclass
class VillageInfo:
    """Known information about a village"""
    id: int
    name: str
    x: int
    y: int
    points: int
    owner_id: Optional[int]  # None = barbarian
    owner_name: Optional[str]
    tribe_id: Optional[int]
    
    # Computed properties
    is_barb: bool
    is_bot: bool
    distance: float = 0.0  # Calculated relative to bot's village
    last_scouted: Optional[datetime] = None
    last_attacked: Optional[datetime] = None
    estimated_units: Dict[str, int] = field(default_factory=dict)
    
    def __hash__(self):
        return hash(self.id)


@dataclass
class UnitComposition:
    """Army composition for a village"""
    spear: int = 0
    sword: int = 0
    axe: int = 0
    archer: int = 0
    spy: int = 0
    light: int = 0
    marcher: int = 0
    heavy: int = 0
    ram: int = 0
    catapult: int = 0
    knight: int = 0
    snob: int = 0
    
    @property
    def offensive_power(self) -> int:
        """Rough offensive strength"""
        return (self.axe * 40 + self.light * 130 + self.marcher * 120 + 
                self.ram * 5 + self.catapult * 10)
    
    @property
    def defensive_power(self) -> int:
        """Rough defensive strength"""
        return (self.spear * 20 + self.sword * 50 + self.heavy * 200 + 
                self.archer * 25)
    
    @property
    def total_pop(self) -> int:
        """Total population used"""
        return (self.spear + self.sword + self.axe + self.archer + 
                self.spy + self.light + self.marcher + self.heavy + 
                self.ram * 5 + self.catapult * 8 + self.knight + self.snob * 100)


@dataclass
class VillageState:
    """Current state of bot's own village"""
    id: int
    name: str
    x: int
    y: int
    points: int
    
    # Resources
    wood: int = 0
    clay: int = 0
    iron: int = 0
    storage: int = 1000
    
    # Production
    wood_prod: int = 25
    clay_prod: int = 25
    iron_prod: int = 25
    
    # Population
    pop: int = 0
    pop_max: int = 24
    
    # Buildings
    buildings: Dict[str, int] = field(default_factory=dict)
    
    # Military
    units: UnitComposition = field(default_factory=UnitComposition)
    units_traveling: UnitComposition = field(default_factory=UnitComposition)
    
    # Queues
    build_queue: List[str] = field(default_factory=list)
    recruit_queue: Dict[str, int] = field(default_factory=dict)
    
    # Threats
    incoming_attacks: int = 0
    incoming_supports: int = 0
    
    @property
    def resources_capped(self) -> bool:
        """Are resources hitting storage limit?"""
        return (self.wood >= self.storage * 0.95 or 
                self.clay >= self.storage * 0.95 or 
                self.iron >= self.storage * 0.95)
    
    @property
    def can_recruit(self) -> bool:
        """Has population space for recruitment"""
        return self.pop < self.pop_max - 5
    
    @property
    def role(self) -> str:
        """Village role based on units"""
        if self.units.offensive_power > self.units.defensive_power * 2:
            return "offense"
        elif self.units.defensive_power > self.units.offensive_power * 2:
            return "defense"
        else:
            return "balanced"


@dataclass
class AIBotState:
    """Complete state for an AI bot player"""
    player_id: int
    name: str
    username: str
    personality: str
    
    # Tribe membership
    tribe_id: Optional[int] = None
    tribe_name: Optional[str] = None
    tribe_rank: Optional[str] = None
    
    # Relations to other players
    relations: Dict[int, Relation] = field(default_factory=dict)
    
    # Known world state
    known_villages: Dict[int, VillageInfo] = field(default_factory=dict)
    own_villages: List[VillageState] = field(default_factory=list)
    
    # Session & timing
    session_cookie: Optional[str] = None
    last_tick: Optional[datetime] = None
    tick_count: int = 0
    
    # Dynamic decision flags
    threat_level: float = 0.0          # 0-100, how threatened we feel
    expansion_urgency: float = 0.0     # 0-100, desire to expand
    eco_score: float = 0.0             # Computed economy strength
    military_score: float = 0.0        # Computed military strength
    
    # Strategy state
    current_targets: List[int] = field(default_factory=list)  # Village IDs we're focusing
    farm_rotation: List[int] = field(default_factory=list)    # Barb villages to farm
    farm_index: int = 0
    
    # Learning / memory
    successful_farms: Dict[int, int] = field(default_factory=dict)  # village_id -> success_count
    failed_attacks: Dict[int, int] = field(default_factory=dict)     # village_id -> fail_count
    
    @property
    def total_points(self) -> int:
        """Sum of all village points"""
        return sum(v.points for v in self.own_villages)
    
    @property
    def game_phase(self) -> GamePhase:
        """Determine game phase based on points"""
        if self.total_points < 500:
            return GamePhase.EARLY
        elif self.total_points < 3000:
            return GamePhase.MID
        else:
            return GamePhase.LATE
    
    @property
    def village_count(self) -> int:
        """Number of villages owned"""
        return len(self.own_villages)
    
    def get_relation(self, player_id: int) -> Relation:
        """Get or create relation to another player"""
        if player_id not in self.relations:
            self.relations[player_id] = Relation(
                player_id=player_id,
                score=0.0,
                last_update=datetime.now(),
                events=[]
            )
        return self.relations[player_id]
    
    def update_relation(self, player_id: int, event_type: str, value: float):
        """Apply relation change"""
        rel = self.get_relation(player_id)
        rel.apply_event(event_type, value, datetime.now())
    
    def get_allies(self) -> List[int]:
        """Get all allied player IDs"""
        return [pid for pid, rel in self.relations.items() 
                if rel.relation_type == RelationType.ALLY]
    
    def get_enemies(self) -> List[int]:
        """Get all enemy player IDs"""
        return [pid for pid, rel in self.relations.items() 
                if rel.relation_type == RelationType.ENEMY]
    
    def is_ally(self, player_id: int) -> bool:
        """Check if player is ally"""
        return self.get_relation(player_id).relation_type == RelationType.ALLY
    
    def is_enemy(self, player_id: int) -> bool:
        """Check if player is enemy"""
        return self.get_relation(player_id).relation_type == RelationType.ENEMY
