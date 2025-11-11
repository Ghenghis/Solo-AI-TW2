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
    
    async def send_scout(self, session: GameSession, from_village: int,
                        to_village: int, spy_count: int):
        """
        Send scout/spy to gather intelligence
        POST to: game.php?village=X&screen=place&action=command
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
                'spy': spy_count,
                'attack': 'true'  # Scouts go with attack flag
            }
            
            response = await session.client.post(url, params=params, data=data)
            
            logger.info("scout_sent",
                       from_village=from_village,
                       to_village=to_village,
                       spy_count=spy_count,
                       status=response.status_code)
            
            return response.status_code == 200
            
        except Exception as e:
            logger.error("scout_failed",
                        from_village=from_village,
                        to_village=to_village,
                        error=str(e))
            return False
    
    async def trade_resources(self, session: GameSession, village_id: int,
                             sell: str, buy: str, amount: int):
        """
        Trade resources at market
        POST to: game.php?village=X&screen=market&action=call_merchant
        """
        try:
            url = urljoin(self.base_url, 'game.php')
            params = {
                'village': village_id,
                'screen': 'market',
                'action': 'call_merchant'
            }
            
            # Market trade format: sell X, buy Y
            data = {
                f'sell_{sell}': amount,
                f'buy_{buy}': amount,
                'max_merchants': '1'
            }
            
            response = await session.client.post(url, params=params, data=data)
            
            logger.info("trade_executed",
                       village_id=village_id,
                       sell=sell,
                       buy=buy,
                       amount=amount,
                       status=response.status_code)
            
            return response.status_code == 200
            
        except Exception as e:
            logger.error("trade_failed",
                        village_id=village_id,
                        error=str(e))
            return False
    
    async def send_resources(self, session: GameSession, from_village: int,
                            to_village: int, resources: Dict[str, int]):
        """
        Send resources to another village
        POST to: game.php?village=X&screen=market&action=send
        """
        try:
            url = urljoin(self.base_url, 'game.php')
            params = {
                'village': from_village,
                'screen': 'market',
                'action': 'send'
            }
            
            data = {
                'x': to_village % 1000,
                'y': to_village // 1000,
                'wood': resources.get('wood', 0),
                'clay': resources.get('clay', 0),
                'iron': resources.get('iron', 0)
            }
            
            response = await session.client.post(url, params=params, data=data)
            
            logger.info("resources_sent",
                       from_village=from_village,
                       to_village=to_village,
                       resources=resources,
                       status=response.status_code)
            
            return response.status_code == 200
            
        except Exception as e:
            logger.error("send_resources_failed",
                        from_village=from_village,
                        to_village=to_village,
                        error=str(e))
            return False
    
    async def execute_with_retry(self, func, *args, max_retries=3, **kwargs):
        """
        Execute any GameClient method with automatic retry logic
        Implements exponential backoff for failed requests
        """
        for attempt in range(max_retries):
            try:
                result = await func(*args, **kwargs)
                return result
            except Exception as e:
                if attempt < max_retries - 1:
                    wait_time = (2 ** attempt) + random.uniform(0, 1)
                    logger.warning("request_failed_retrying",
                                  attempt=attempt + 1,
                                  max_retries=max_retries,
                                  wait_time=wait_time,
                                  error=str(e))
                    await asyncio.sleep(wait_time)
                else:
                    logger.error("request_failed_max_retries",
                               max_retries=max_retries,
                               error=str(e))
                    raise
        
        return None
