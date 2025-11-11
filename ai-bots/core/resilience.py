"""
Error Recovery and Resilience Mechanisms
Implements retry logic, circuit breakers, and graceful degradation
"""

import asyncio
from typing import Callable, Any, Optional
from functools import wraps
import time
import structlog

logger = structlog.get_logger(__name__)


class CircuitBreaker:
    """
    Circuit breaker pattern implementation.
    
    States:
    - CLOSED: Normal operation, requests pass through
    - OPEN: Too many failures, requests fail fast
    - HALF_OPEN: Testing if service recovered
    """
    
    def __init__(self, failure_threshold: int = 5, timeout: int = 60):
        self.failure_threshold = failure_threshold
        self.timeout = timeout
        self.failures = 0
        self.last_failure_time: Optional[float] = None
        self.state = 'CLOSED'
    
    def call(self, func: Callable, *args, **kwargs) -> Any:
        """Execute function with circuit breaker protection"""
        if self.state == 'OPEN':
            if time.time() - self.last_failure_time > self.timeout:
                self.state = 'HALF_OPEN'
                logger.info("circuit_breaker_half_open")
            else:
                raise Exception("Circuit breaker is OPEN")
        
        try:
            result = func(*args, **kwargs)
            if self.state == 'HALF_OPEN':
                self.state = 'CLOSED'
                self.failures = 0
                logger.info("circuit_breaker_closed")
            return result
        
        except Exception as e:
            self.failures += 1
            self.last_failure_time = time.time()
            
            if self.failures >= self.failure_threshold:
                self.state = 'OPEN'
                logger.warning("circuit_breaker_open", failures=self.failures)
            
            raise e


def retry_with_backoff(
    max_attempts: int = 3,
    initial_delay: float = 1.0,
    max_delay: float = 60.0,
    exponential_base: float = 2.0
):
    """
    Decorator for retry logic with exponential backoff.
    
    Args:
        max_attempts: Maximum number of retry attempts
        initial_delay: Initial delay in seconds
        max_delay: Maximum delay between retries
        exponential_base: Base for exponential backoff
    """
    def decorator(func):
        @wraps(func)
        async def wrapper(*args, **kwargs):
            delay = initial_delay
            last_exception = None
            
            for attempt in range(max_attempts):
                try:
                    return await func(*args, **kwargs)
                
                except Exception as e:
                    last_exception = e
                    
                    if attempt < max_attempts - 1:
                        logger.warning(
                            "retry_attempt",
                            function=func.__name__,
                            attempt=attempt + 1,
                            max_attempts=max_attempts,
                            delay=delay,
                            error=str(e)
                        )
                        
                        await asyncio.sleep(delay)
                        delay = min(delay * exponential_base, max_delay)
                    else:
                        logger.error(
                            "retry_exhausted",
                            function=func.__name__,
                            attempts=max_attempts,
                            error=str(e)
                        )
            
            raise last_exception
        
        return wrapper
    return decorator


class RateLimiter:
    """
    Token bucket rate limiter.
    Prevents system overload by limiting operation rate.
    """
    
    def __init__(self, rate: int, per: float = 1.0):
        """
        Args:
            rate: Number of operations allowed
            per: Time period in seconds
        """
        self.rate = rate
        self.per = per
        self.allowance = rate
        self.last_check = time.time()
    
    async def acquire(self):
        """Wait until rate limit allows operation"""
        current = time.time()
        time_passed = current - self.last_check
        self.last_check = current
        
        self.allowance += time_passed * (self.rate / self.per)
        if self.allowance > self.rate:
            self.allowance = self.rate
        
        if self.allowance < 1.0:
            sleep_time = (1.0 - self.allowance) * (self.per / self.rate)
            logger.debug("rate_limit_sleep", sleep_time=sleep_time)
            await asyncio.sleep(sleep_time)
            self.allowance = 0.0
        else:
            self.allowance -= 1.0


class GracefulDegradation:
    """
    Implements graceful degradation strategies.
    System continues operating with reduced functionality when components fail.
    """
    
    @staticmethod
    async def execute_with_fallback(
        primary: Callable,
        fallback: Callable,
        fallback_condition: Callable[[Exception], bool] = lambda e: True
    ) -> Any:
        """
        Execute primary function, fall back to alternative on failure.
        
        Args:
            primary: Primary function to try
            fallback: Fallback function if primary fails
            fallback_condition: Condition to determine if fallback should be used
        """
        try:
            return await primary()
        
        except Exception as e:
            if fallback_condition(e):
                logger.warning(
                    "graceful_degradation_fallback",
                    primary=primary.__name__,
                    fallback=fallback.__name__,
                    error=str(e)
                )
                return await fallback()
            else:
                raise e
    
    @staticmethod
    async def skip_on_failure(func: Callable, *args, **kwargs) -> Optional[Any]:
        """
        Execute function, return None on failure instead of crashing.
        Useful for non-critical operations.
        """
        try:
            return await func(*args, **kwargs)
        except Exception as e:
            logger.warning(
                "non_critical_operation_failed",
                function=func.__name__,
                error=str(e)
            )
            return None


class HealthBasedExecution:
    """
    Execute operations based on system health status.
    Reduces load when system is unhealthy.
    """
    
    def __init__(self, health_checker):
        self.health_checker = health_checker
    
    async def execute_if_healthy(self, func: Callable, *args, **kwargs) -> Optional[Any]:
        """Only execute if system is healthy"""
        if self.health_checker and await self.health_checker.is_healthy():
            return await func(*args, **kwargs)
        else:
            logger.warning(
                "operation_skipped_unhealthy",
                function=func.__name__
            )
            return None
    
    async def reduce_load_if_degraded(self, func: Callable, reduction_factor: float = 0.5) -> Any:
        """Reduce operation load if system is degraded"""
        is_degraded = self.health_checker and await self.health_checker.is_degraded()
        
        if is_degraded:
            logger.info(
                "reducing_load",
                function=func.__name__,
                factor=reduction_factor
            )
            # Implementation-specific: might reduce batch size, increase delay, etc.
        
        return await func()
