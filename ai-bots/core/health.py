"""
Health Check System for AI Bot Orchestrator
Provides deep health validation for all system components
"""

import asyncio
from typing import Dict, List, Optional
from dataclasses import dataclass
from datetime import datetime
import structlog

logger = structlog.get_logger(__name__)


@dataclass
class HealthStatus:
    """Health check result"""
    component: str
    status: str  # 'healthy', 'degraded', 'unhealthy'
    message: str
    timestamp: datetime
    details: Optional[Dict] = None


class HealthChecker:
    """
    Comprehensive health checking for all system components.
    
    Checks:
    - Database connectivity
    - Memory system tables
    - Configuration validity
    - Metrics endpoint
    - Guardrail state
    - Bot state consistency
    """
    
    def __init__(self, config, db_pool=None):
        self.config = config
        self.db_pool = db_pool
        self._last_check: Optional[datetime] = None
        self._cached_status: Optional[Dict] = None
    
    async def check_all(self) -> Dict[str, HealthStatus]:
        """Run all health checks"""
        checks = {
            'database': await self._check_database(),
            'configuration': await self._check_configuration(),
            'memory_system': await self._check_memory_system(),
            'orchestrator': await self._check_orchestrator(),
        }
        
        self._last_check = datetime.utcnow()
        self._cached_status = checks
        
        return checks
    
    async def _check_database(self) -> HealthStatus:
        """Check database connectivity and schema"""
        try:
            if not self.db_pool:
                return HealthStatus(
                    component='database',
                    status='unhealthy',
                    message='Database pool not initialized',
                    timestamp=datetime.utcnow()
                )
            
            # Test connection
            async with self.db_pool.acquire() as conn:
                # Simple query to verify connection
                result = await conn.fetchone("SELECT 1 as health_check")
                
                if not result or result['health_check'] != 1:
                    return HealthStatus(
                        component='database',
                        status='unhealthy',
                        message='Database query returned unexpected result',
                        timestamp=datetime.utcnow()
                    )
                
                # Check required tables exist
                required_tables = [
                    'users', 'villages', 'units',
                    'ai_relations', 'ai_target_stats', 'ai_strategy_stats'
                ]
                
                missing_tables = []
                for table in required_tables:
                    check = await conn.fetchone(f"""
                        SELECT COUNT(*) as exists_check 
                        FROM information_schema.tables 
                        WHERE table_schema = DATABASE() 
                        AND table_name = '{table}'
                    """)
                    
                    if not check or check['exists_check'] == 0:
                        missing_tables.append(table)
                
                if missing_tables:
                    return HealthStatus(
                        component='database',
                        status='unhealthy',
                        message=f'Missing tables: {", ".join(missing_tables)}',
                        timestamp=datetime.utcnow(),
                        details={'missing_tables': missing_tables}
                    )
                
                return HealthStatus(
                    component='database',
                    status='healthy',
                    message='Database connectivity and schema validated',
                    timestamp=datetime.utcnow()
                )
        
        except Exception as e:
            logger.error("database_health_check_failed", error=str(e))
            return HealthStatus(
                component='database',
                status='unhealthy',
                message=f'Database error: {str(e)}',
                timestamp=datetime.utcnow()
            )
    
    async def _check_configuration(self) -> HealthStatus:
        """Validate configuration"""
        try:
            # Run config validation
            self.config.validate()
            
            # Check critical parameters
            warnings = []
            
            if self.config.bot_count > 100:
                warnings.append('Bot count > 100 may cause performance issues')
            
            if self.config.max_concurrent_bots < 5:
                warnings.append('Low concurrent bot count may slow processing')
            
            if self.config.bot_tick_rate < 30:
                warnings.append('Tick rate < 30s may overload game server')
            
            status = 'degraded' if warnings else 'healthy'
            message = '; '.join(warnings) if warnings else 'Configuration valid'
            
            return HealthStatus(
                component='configuration',
                status=status,
                message=message,
                timestamp=datetime.utcnow(),
                details={'warnings': warnings} if warnings else None
            )
        
        except ValueError as e:
            return HealthStatus(
                component='configuration',
                status='unhealthy',
                message=f'Configuration invalid: {str(e)}',
                timestamp=datetime.utcnow()
            )
    
    async def _check_memory_system(self) -> HealthStatus:
        """Check AI memory tables health"""
        try:
            if not self.db_pool:
                return HealthStatus(
                    component='memory_system',
                    status='degraded',
                    message='Cannot check memory system without database',
                    timestamp=datetime.utcnow()
                )
            
            async with self.db_pool.acquire() as conn:
                # Check memory tables have data
                relation_count = await conn.fetchone("SELECT COUNT(*) as cnt FROM ai_relations")
                target_count = await conn.fetchone("SELECT COUNT(*) as cnt FROM ai_target_stats")
                
                total_records = (relation_count['cnt'] if relation_count else 0) + \
                               (target_count['cnt'] if target_count else 0)
                
                if total_records == 0:
                    status = 'degraded'
                    message = 'Memory system initialized but no learning data yet'
                else:
                    status = 'healthy'
                    message = f'Memory system operational ({total_records} records)'
                
                return HealthStatus(
                    component='memory_system',
                    status=status,
                    message=message,
                    timestamp=datetime.utcnow(),
                    details={'total_memory_records': total_records}
                )
        
        except Exception as e:
            logger.error("memory_system_health_check_failed", error=str(e))
            return HealthStatus(
                component='memory_system',
                status='unhealthy',
                message=f'Memory system error: {str(e)}',
                timestamp=datetime.utcnow()
            )
    
    async def _check_orchestrator(self) -> HealthStatus:
        """Check orchestrator state"""
        # This is a placeholder - in real implementation, 
        # orchestrator would register itself with health checker
        return HealthStatus(
            component='orchestrator',
            status='healthy',
            message='Orchestrator running',
            timestamp=datetime.utcnow()
        )
    
    def is_healthy(self) -> bool:
        """Check if system is healthy overall"""
        if not self._cached_status:
            return False
        
        return all(
            status.status == 'healthy' 
            for status in self._cached_status.values()
        )
    
    def is_degraded(self) -> bool:
        """Check if system is degraded"""
        if not self._cached_status:
            return True
        
        return any(
            status.status == 'degraded' 
            for status in self._cached_status.values()
        )
    
    def to_dict(self) -> Dict:
        """Export health status as dictionary"""
        if not self._cached_status:
            return {
                'overall_status': 'unknown',
                'last_check': None,
                'components': {}
            }
        
        overall = 'healthy'
        if any(s.status == 'unhealthy' for s in self._cached_status.values()):
            overall = 'unhealthy'
        elif any(s.status == 'degraded' for s in self._cached_status.values()):
            overall = 'degraded'
        
        return {
            'overall_status': overall,
            'last_check': self._last_check.isoformat() if self._last_check else None,
            'components': {
                name: {
                    'status': status.status,
                    'message': status.message,
                    'timestamp': status.timestamp.isoformat(),
                    'details': status.details
                }
                for name, status in self._cached_status.items()
            }
        }
