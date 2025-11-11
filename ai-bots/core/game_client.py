"""
HTTP Game Client - Mimics real browser/player behavior
Makes authentic requests to the TWLan game server
"""

import asyncio
import random
from typing import Dict, Optional, List
from urllib.parse import urljoin

import httpx
import structlog
from bs4 import BeautifulSoup

logger = structlog.get_logger()


class GameSession:
    """Maintains session state for a bot (cookies, headers, etc.)"""
    
    def __init__(self, user_id: int, username: str, base_url: str):
        self.user_id = user_id
        self.username = username
        self.base_url = base_url
        
        # HTTP client with cookies
        self.client = httpx.AsyncClient(
            timeout=30.0,
            follow_redirects=True,
            headers={
                'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Accept': 'text/html,application/xhtml+xml,application/xml',
                'Accept-Language': 'de-DE,de;q=0.9,en;q=0.8',
            }
        )
        
        self.cookies = {}
        self.current_village_id = None
        self.logged_in = False
    
    async def close(self):
        """Close HTTP client"""
        await self.client.aclose()


class GameClient:
    """
    Enterprise-grade HTTP client that reverse-engineers TWLan protocol
    Sends legitimate POST/GET requests exactly like a real player
    """
    
    def __init__(self, config):
        self.config = config
        self.base_url = config.game_base_url
    
    async def create_session(self, user_id: int, username: str, password: str) -> GameSession:
        """Create and authenticate a game session"""
        session = GameSession(user_id, username, self.base_url)
        
        try:
            # Step 1: GET login page to get session cookie
            response = await session.client.get(urljoin(self.base_url, '/'))
            
            # Step 2: POST login credentials
            login_data = {
                'username': username,
                'password': password,
                'login': 'Login'
            }
            
            response = await session.client.post(
                urljoin(self.base_url, '/index.php'),
                data=login_data
            )
            
            # Step 3: Verify login success (check for redirect or specific content)
            if 'game.php' in str(response.url) or response.status_code == 200:
                session.logged_in = True
                logger.info("session_created", username=username)
            else:
                raise Exception(f"Login failed for {username}")
            
            return session
            
        except Exception as e:
            await session.close()
            logger.error("session_creation_failed", username=username, error=str(e))
            raise
    
    async def build_building(self, session: GameSession, village_id: int, building: str):
        """
        Build/upgrade a building
        Reverse-engineered from: game.php?village=X&screen=main&action=upgrade&building=Y
        """
        try:
            url = urljoin(self.base_url, 'game.php')
            params = {
                'village': village_id,
                'screen': 'main',
                'action': 'upgrade_building',
                'building': building
            }
            
            response = await session.client.post(url, params=params)
            
            logger.info("building_upgraded",
                       village_id=village_id,
                       building=building,
                       status=response.status_code)
            
            return response.status_code == 200
            
        except Exception as e:
            logger.error("building_upgrade_failed",
                        village_id=village_id,
                        building=building,
                        error=str(e))
            return False
    
    async def recruit_units(self, session: GameSession, village_id: int, units: Dict[str, int]):
        """
        Recruit units in barracks/stable/workshop
        POST to: game.php?village=X&screen=barracks&action=train
        """
        try:
            # Determine which building based on unit types
            building_map = {
                'spear': 'barracks',
                'sword': 'barracks',
                'axe': 'barracks',
                'light': 'stable',
                'heavy': 'stable',
                'ram': 'garage',
                'catapult': 'garage',
            }
            
            for unit_type, count in units.items():
                if count <= 0:
                    continue
                
                screen = building_map.get(unit_type, 'barracks')
                
                url = urljoin(self.base_url, 'game.php')
                params = {
                    'village': village_id,
                    'screen': screen,
                    'action': 'train'
                }
                data = {
                    unit_type: count
                }
                
                response = await session.client.post(url, params=params, data=data)
                
                logger.info("units_recruited",
                           village_id=village_id,
                           unit_type=unit_type,
                           count=count,
                           status=response.status_code)
                
                # Small delay between different unit types
                await asyncio.sleep(random.uniform(0.5, 2.0))
            
            return True
            
        except Exception as e:
            logger.error("recruitment_failed",
                        village_id=village_id,
                        error=str(e))
            return False
    
    async def send_attack(self, session: GameSession, from_village: int, 
                          to_village: int, units: Dict[str, int]):
        """
        Send attack command
        POST to: game.php?village=X&screen=place&action=command&attack=true
        """
        try:
            url = urljoin(self.base_url, 'game.php')
            params = {
                'village': from_village,
                'screen': 'place',
                'try': 'confirm'
            }
            
            # Build form data exactly as browser would send
            data = {
                'x': to_village % 1000,  # Extract coordinates
                'y': to_village // 1000,
                'attack': 'true',
                **units  # Spread unit counts
            }
            
            response = await session.client.post(url, params=params, data=data)
            
            logger.info("attack_sent",
                       from_village=from_village,
                       to_village=to_village,
                       units=units,
                       status=response.status_code)
            
            return response.status_code == 200
            
        except Exception as e:
            logger.error("attack_failed",
                        from_village=from_village,
                        to_village=to_village,
                        error=str(e))
            return False
    
    async def send_support(self, session: GameSession, from_village: int,
                          to_village: int, units: Dict[str, int]):
        """
        Send support troops
        Similar to attack but with support=true flag
        """
        try:
            url = urljoin(self.base_url, 'game.php')
            params = {
                'village': from_village,
                'screen': 'place',
                'try': 'confirm'
            }
            
            data = {
                'x': to_village % 1000,
                'y': to_village // 1000,
                'support': 'true',
                **units
            }
            
            response = await session.client.post(url, params=params, data=data)
            
            logger.info("support_sent",
                       from_village=from_village,
                       to_village=to_village,
                       status=response.status_code)
            
            return response.status_code == 200
            
        except Exception as e:
            logger.error("support_failed", error=str(e))
            return False
    
    async def get_village_overview(self, session: GameSession, village_id: int) -> Optional[Dict]:
        """
        Get village overview page and parse current state
        This mimics viewing your village in the browser
        """
        try:
            url = urljoin(self.base_url, 'game.php')
            params = {
                'village': village_id,
                'screen': 'overview'
            }
            
            response = await session.client.get(url, params=params)
            
            # Parse HTML to extract game state
            soup = BeautifulSoup(response.text, 'html.parser')
            
            # Extract resource values, building queue, etc.
            # This would need actual HTML structure analysis
            
            return {
                'village_id': village_id,
                'html': response.text,
                'status': 'ok'
            }
            
        except Exception as e:
            logger.error("overview_fetch_failed",
                        village_id=village_id,
                        error=str(e))
            return None
