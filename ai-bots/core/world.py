"""
World Snapshot - Builds in-memory view of game state
Periodically queries DB to understand players, tribes, villages, positions
"""

import asyncio
from typing import Dict, List, Optional, Set
from dataclasses import dataclass, field
from datetime import datetime
import structlog

from bots.state import VillageInfo, GamePhase

logger = structlog.get_logger()


@dataclass
class TribeInfo:
    """Information about a tribe/alliance"""
    id: int
    name: str
    tag: str
    member_count: int
    total_points: int
    rank: int
    members: Set[int] = field(default_factory=set)  # player_ids


@dataclass
class PlayerInfo:
    """Information about a player"""
    id: int
    username: str
    tribe_id: Optional[int]
    tribe_name: Optional[str]
    points: int
    village_count: int
    rank: int
    is_bot: bool


class WorldSnapshot:
    """
    In-memory snapshot of the game world
    Refreshed periodically from database
    """
    
    def __init__(self):
        self.timestamp: datetime = datetime.now()
        self.players: Dict[int, PlayerInfo] = {}
        self.tribes: Dict[int, TribeInfo] = {}
        self.villages: Dict[int, VillageInfo] = {}
        self.barbarian_villages: List[VillageInfo] = []
        
        # Indexed lookups
        self.villages_by_player: Dict[int, List[VillageInfo]] = {}
        self.players_by_tribe: Dict[int, List[PlayerInfo]] = {}
        
        # World statistics
        self.total_players: int = 0
        self.total_villages: int = 0
        self.total_tribes: int = 0
        self.world_phase: GamePhase = GamePhase.EARLY
    
    @classmethod
    async def build(cls, db) -> 'WorldSnapshot':
        """
        Build world snapshot from database
        This is the main entry point - called each orchestrator tick
        """
        snapshot = cls()
        snapshot.timestamp = datetime.now()
        
        logger.info("building_world_snapshot")
        
        # Load all players
        players_data = await db.fetch_all("""
            SELECT 
                id_user, username, id_ally, points, village_count, rank,
                is_bot
            FROM users
            WHERE deleted = 0
            ORDER BY points DESC
        """)
        
        for row in players_data:
            player = PlayerInfo(
                id=row['id_user'],
                username=row['username'],
                tribe_id=row.get('id_ally'),
                tribe_name=None,  # Will fill in after loading tribes
                points=row.get('points', 0),
                village_count=row.get('village_count', 0),
                rank=row.get('rank', 999),
                is_bot=row.get('is_bot', False)
            )
            snapshot.players[player.id] = player
        
        # Load all tribes
        tribes_data = await db.fetch_all("""
            SELECT 
                id_ally, name, tag, member_count, points, rank
            FROM alliances
            WHERE deleted = 0
        """)
        
        for row in tribes_data:
            tribe = TribeInfo(
                id=row['id_ally'],
                name=row['name'],
                tag=row['tag'],
                member_count=row.get('member_count', 0),
                total_points=row.get('points', 0),
                rank=row.get('rank', 999)
            )
            snapshot.tribes[tribe.id] = tribe
        
        # Link players to tribes
        for player in snapshot.players.values():
            if player.tribe_id and player.tribe_id in snapshot.tribes:
                tribe = snapshot.tribes[player.tribe_id]
                tribe.members.add(player.id)
                player.tribe_name = tribe.name
                
                if player.tribe_id not in snapshot.players_by_tribe:
                    snapshot.players_by_tribe[player.tribe_id] = []
                snapshot.players_by_tribe[player.tribe_id].append(player)
        
        # Load all villages
        villages_data = await db.fetch_all("""
            SELECT 
                id_village, name, x, y, points, id_user
            FROM villages
            WHERE deleted = 0
        """)
        
        for row in villages_data:
            owner_id = row.get('id_user')
            owner = snapshot.players.get(owner_id) if owner_id else None
            
            village = VillageInfo(
                id=row['id_village'],
                name=row['name'],
                x=row['x'],
                y=row['y'],
                points=row['points'],
                owner_id=owner_id,
                owner_name=owner.username if owner else None,
                tribe_id=owner.tribe_id if owner else None,
                is_barb=(owner_id is None),
                is_bot=owner.is_bot if owner else False
            )
            
            snapshot.villages[village.id] = village
            
            if village.is_barb:
                snapshot.barbarian_villages.append(village)
            
            if owner_id:
                if owner_id not in snapshot.villages_by_player:
                    snapshot.villages_by_player[owner_id] = []
                snapshot.villages_by_player[owner_id].append(village)
        
        # Calculate world statistics
        snapshot.total_players = len(snapshot.players)
        snapshot.total_villages = len(snapshot.villages)
        snapshot.total_tribes = len(snapshot.tribes)
        
        # Determine world phase based on average points
        avg_points = sum(p.points for p in snapshot.players.values()) / max(1, len(snapshot.players))
        if avg_points < 500:
            snapshot.world_phase = GamePhase.EARLY
        elif avg_points < 3000:
            snapshot.world_phase = GamePhase.MID
        else:
            snapshot.world_phase = GamePhase.LATE
        
        logger.info("world_snapshot_complete",
                   players=snapshot.total_players,
                   villages=snapshot.total_villages,
                   tribes=snapshot.total_tribes,
                   barbarians=len(snapshot.barbarian_villages),
                   phase=snapshot.world_phase.value)
        
        return snapshot
    
    def get_player(self, player_id: int) -> Optional[PlayerInfo]:
        """Get player by ID"""
        return self.players.get(player_id)
    
    def get_village(self, village_id: int) -> Optional[VillageInfo]:
        """Get village by ID"""
        return self.villages.get(village_id)
    
    def get_player_villages(self, player_id: int) -> List[VillageInfo]:
        """Get all villages owned by a player"""
        return self.villages_by_player.get(player_id, [])
    
    def get_tribe_members(self, tribe_id: int) -> List[PlayerInfo]:
        """Get all players in a tribe"""
        return self.players_by_tribe.get(tribe_id, [])
    
    def get_nearby_villages(self, x: int, y: int, radius: int = 20, 
                           exclude_player: Optional[int] = None) -> List[VillageInfo]:
        """Find villages within radius"""
        nearby = []
        
        for village in self.villages.values():
            if exclude_player and village.owner_id == exclude_player:
                continue
            
            distance = ((village.x - x) ** 2 + (village.y - y) ** 2) ** 0.5
            
            if distance <= radius:
                village.distance = distance
                nearby.append(village)
        
        # Sort by distance
        nearby.sort(key=lambda v: v.distance)
        return nearby
    
    def get_farmable_barbs(self, x: int, y: int, radius: int = 30, 
                           max_points: int = 200) -> List[VillageInfo]:
        """Find barbarian villages suitable for farming"""
        farmable = []
        
        for village in self.barbarian_villages:
            if village.points > max_points:
                continue
            
            distance = ((village.x - x) ** 2 + (village.y - y) ** 2) ** 0.5
            
            if distance <= radius:
                village.distance = distance
                farmable.append(village)
        
        # Sort by distance then points (closer and weaker first)
        farmable.sort(key=lambda v: (v.distance, v.points))
        return farmable[:50]  # Limit to top 50
    
    def are_allies(self, player_id1: int, player_id2: int) -> bool:
        """Check if two players are in same tribe"""
        p1 = self.players.get(player_id1)
        p2 = self.players.get(player_id2)
        
        if not p1 or not p2:
            return False
        
        return p1.tribe_id and p1.tribe_id == p2.tribe_id
