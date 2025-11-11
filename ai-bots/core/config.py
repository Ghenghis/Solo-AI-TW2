"""
Configuration management for AI Bot Orchestrator
Loads from environment variables with validation
"""

import os
from dataclasses import dataclass
from typing import Optional
from dotenv import load_dotenv


@dataclass
class Config:
    """Enterprise-grade configuration with validation"""
    
    # Database
    db_host: str
    db_port: int
    db_name: str
    db_user: str
    db_password: str
    
    # Game Server
    game_base_url: str
    game_session_timeout: int
    
    # Bot Configuration
    bot_count: int
    bot_tick_rate: int
    bot_randomness: float
    
    # Personality Distribution
    personality_warmonger: int
    personality_turtle: int
    personality_balanced: int
    personality_diplomat: int
    personality_chaos: int
    
    # Performance
    max_concurrent_bots: int
    request_timeout: int
    db_pool_size: int
    cache_ttl: int
    
    # Logging & Metrics
    log_level: str
    log_format: str
    metrics_port: int
    
    # Feature Flags
    enable_metrics: bool
    enable_auto_alliance: bool
    enable_night_bonus: bool
    enable_adaptive_strategy: bool
    
    # Safety Limits
    max_attacks_per_hour: int
    max_build_queue_length: int
    min_action_interval: int
    
    # Guardrails Configuration
    enable_sleep_windows: bool
    min_sleep_hours: int
    max_sleep_hours: int
    max_attacks_per_village_per_tick: int
    max_attacks_per_player_per_tick: int
    max_attacks_per_player_per_hour: int
    harassment_window_hours: int
    max_attacks_per_player_per_harassment_window: int
    dogpile_threshold: int
    min_reaction_delay_minutes: int
    max_reaction_delay_minutes: int
    enable_session_rhythm: bool
    min_session_duration_minutes: int
    max_session_duration_minutes: int
    min_session_cooldown_minutes: int
    max_session_cooldown_minutes: int
    failed_attack_cooldown_minutes: int
    max_system_attacks_per_minute: int
    turtle_attack_multiplier: float
    diplomat_attack_multiplier: float
    warmonger_attack_multiplier: float
    diplomat_support_multiplier: float
    
    @classmethod
    def from_env(cls) -> 'Config':
        """Load configuration from environment variables"""
        load_dotenv()
        
        # Validate personality distribution
        personalities = {
            'warmonger': int(os.getenv('PERSONALITY_WARMONGER', 20)),
            'turtle': int(os.getenv('PERSONALITY_TURTLE', 20)),
            'balanced': int(os.getenv('PERSONALITY_BALANCED', 30)),
            'diplomat': int(os.getenv('PERSONALITY_DIPLOMAT', 15)),
            'chaos': int(os.getenv('PERSONALITY_CHAOS', 15)),
        }
        
        total = sum(personalities.values())
        if total != 100:
            raise ValueError(f"Personality distribution must sum to 100, got {total}")
        
        return cls(
            # Database
            db_host=os.getenv('DB_HOST', 'twlan-db'),
            db_port=int(os.getenv('DB_PORT', 3306)),
            db_name=os.getenv('DB_NAME', 'twlan'),
            db_user=os.getenv('DB_USER', 'twlan'),
            db_password=os.getenv('DB_PASSWORD', 'twlan_password'),
            
            # Game Server
            game_base_url=os.getenv('GAME_BASE_URL', 'http://twlan:80'),
            game_session_timeout=int(os.getenv('GAME_SESSION_TIMEOUT', 3600)),
            
            # Bot Configuration
            bot_count=int(os.getenv('BOT_COUNT', 50)),
            bot_tick_rate=int(os.getenv('BOT_TICK_RATE', 60)),
            bot_randomness=float(os.getenv('BOT_RANDOMNESS', 0.3)),
            
            # Personalities
            personality_warmonger=personalities['warmonger'],
            personality_turtle=personalities['turtle'],
            personality_balanced=personalities['balanced'],
            personality_diplomat=personalities['diplomat'],
            personality_chaos=personalities['chaos'],
            
            # Performance
            max_concurrent_bots=int(os.getenv('MAX_CONCURRENT_BOTS', 10)),
            request_timeout=int(os.getenv('REQUEST_TIMEOUT', 30)),
            db_pool_size=int(os.getenv('DB_POOL_SIZE', 20)),
            cache_ttl=int(os.getenv('CACHE_TTL', 300)),
            
            # Logging
            log_level=os.getenv('LOG_LEVEL', 'INFO'),
            log_format=os.getenv('LOG_FORMAT', 'json'),
            metrics_port=int(os.getenv('METRICS_PORT', 9090)),
            
            # Feature Flags
            enable_metrics=os.getenv('ENABLE_METRICS', 'true').lower() == 'true',
            enable_auto_alliance=os.getenv('ENABLE_AUTO_ALLIANCE', 'true').lower() == 'true',
            enable_night_bonus=os.getenv('ENABLE_NIGHT_BONUS', 'true').lower() == 'true',
            enable_adaptive_strategy=os.getenv('ENABLE_ADAPTIVE_STRATEGY', 'true').lower() == 'true',
            
            # Safety Limits
            max_attacks_per_hour=int(os.getenv('MAX_ATTACKS_PER_HOUR', 50)),
            max_build_queue_length=int(os.getenv('MAX_BUILD_QUEUE_LENGTH', 5)),
            min_action_interval=int(os.getenv('MIN_ACTION_INTERVAL', 5)),
            
            # Guardrails
            enable_sleep_windows=os.getenv('ENABLE_SLEEP_WINDOWS', 'true').lower() == 'true',
            min_sleep_hours=int(os.getenv('MIN_SLEEP_HOURS', 3)),
            max_sleep_hours=int(os.getenv('MAX_SLEEP_HOURS', 5)),
            max_attacks_per_village_per_tick=int(os.getenv('MAX_ATTACKS_PER_VILLAGE_PER_TICK', 2)),
            max_attacks_per_player_per_tick=int(os.getenv('MAX_ATTACKS_PER_PLAYER_PER_TICK', 4)),
            max_attacks_per_player_per_hour=int(os.getenv('MAX_ATTACKS_PER_PLAYER_PER_HOUR', 10)),
            harassment_window_hours=int(os.getenv('HARASSMENT_WINDOW_HOURS', 1)),
            max_attacks_per_player_per_harassment_window=int(os.getenv('MAX_ATTACKS_PER_PLAYER_PER_HARASSMENT_WINDOW', 5)),
            dogpile_threshold=int(os.getenv('DOGPILE_THRESHOLD', 5)),
            min_reaction_delay_minutes=int(os.getenv('MIN_REACTION_DELAY_MINUTES', 5)),
            max_reaction_delay_minutes=int(os.getenv('MAX_REACTION_DELAY_MINUTES', 15)),
            enable_session_rhythm=os.getenv('ENABLE_SESSION_RHYTHM', 'true').lower() == 'true',
            min_session_duration_minutes=int(os.getenv('MIN_SESSION_DURATION_MINUTES', 10)),
            max_session_duration_minutes=int(os.getenv('MAX_SESSION_DURATION_MINUTES', 30)),
            min_session_cooldown_minutes=int(os.getenv('MIN_SESSION_COOLDOWN_MINUTES', 5)),
            max_session_cooldown_minutes=int(os.getenv('MAX_SESSION_COOLDOWN_MINUTES', 15)),
            failed_attack_cooldown_minutes=int(os.getenv('FAILED_ATTACK_COOLDOWN_MINUTES', 15)),
            max_system_attacks_per_minute=int(os.getenv('MAX_SYSTEM_ATTACKS_PER_MINUTE', 100)),
            turtle_attack_multiplier=float(os.getenv('TURTLE_ATTACK_MULTIPLIER', 0.4)),
            diplomat_attack_multiplier=float(os.getenv('DIPLOMAT_ATTACK_MULTIPLIER', 0.6)),
            warmonger_attack_multiplier=float(os.getenv('WARMONGER_ATTACK_MULTIPLIER', 1.15)),
            diplomat_support_multiplier=float(os.getenv('DIPLOMAT_SUPPORT_MULTIPLIER', 1.3)),
        )
    
    @property
    def db_url(self) -> str:
        """Construct database connection URL"""
        return f"mysql+mariadb://{self.db_user}:{self.db_password}@{self.db_host}:{self.db_port}/{self.db_name}"
    
    def validate(self):
        """Validate configuration values"""
        errors = []
        
        if self.bot_count < 1:
            errors.append("BOT_COUNT must be at least 1")
        
        if self.bot_tick_rate < 10:
            errors.append("BOT_TICK_RATE must be at least 10 seconds")
        
        if not (0 <= self.bot_randomness <= 1):
            errors.append("BOT_RANDOMNESS must be between 0 and 1")
        
        if self.max_concurrent_bots < 1:
            errors.append("MAX_CONCURRENT_BOTS must be at least 1")
        
        if errors:
            raise ValueError("Configuration validation failed:\n" + "\n".join(errors))
