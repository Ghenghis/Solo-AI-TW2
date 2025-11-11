#!/usr/bin/env python3
"""
TWLan Port Manager - Intelligent Port Allocation System
Handles dynamic port assignment with conflict resolution and persistence
"""

import socket
import random
import json
import os
import time
import logging
from pathlib import Path
from typing import Dict, List, Tuple, Optional
from dataclasses import dataclass, asdict
from datetime import datetime

logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger('PortManager')

@dataclass
class PortAllocation:
    """Port allocation record"""
    service: str
    port: int
    allocated_at: str
    pid: Optional[int] = None
    container_id: Optional[str] = None
    
class IntelligentPortManager:
    """
    Robust port management with fallback strategies
    Features:
    - Dynamic port allocation
    - Conflict detection and resolution
    - Port persistence across restarts
    - Quick timeout and retry logic
    - Service-specific port ranges
    """
    
    def __init__(self, config_dir: str = "/opt/twlan/config"):
        self.config_dir = Path(config_dir)
        self.config_dir.mkdir(parents=True, exist_ok=True)
        self.allocation_file = self.config_dir / "port_allocations.json"
        self.lock_file = self.config_dir / ".port.lock"
        
        # Service-specific port ranges
        self.port_ranges = {
            'web': (8080, 8099),
            'mysql': (3306, 3320),
            'phpmyadmin': (8100, 8110),
            'api': (9000, 9020),
            'legacy': (8200, 8210),
            'modern': (8300, 8310),
            'admin': (8400, 8410)
        }
        
        # Preferred ports for each service
        self.preferred_ports = {
            'web': [8080, 8081, 8082],
            'mysql': [3306, 3307, 3308],
            'phpmyadmin': [8100, 8101],
            'api': [9000, 9001],
            'legacy': [8200],
            'modern': [8300],
            'admin': [8400]
        }
        
        self.allocations: Dict[str, PortAllocation] = {}
        self._load_allocations()
    
    def _load_allocations(self):
        """Load existing port allocations"""
        if self.allocation_file.exists():
            try:
                with open(self.allocation_file, 'r') as f:
                    data = json.load(f)
                    for service, alloc_data in data.items():
                        self.allocations[service] = PortAllocation(**alloc_data)
                logger.info(f"Loaded {len(self.allocations)} port allocations")
            except Exception as e:
                logger.error(f"Failed to load allocations: {e}")
                self.allocations = {}
    
    def _save_allocations(self):
        """Persist port allocations"""
        try:
            data = {
                service: asdict(alloc) 
                for service, alloc in self.allocations.items()
            }
            with open(self.allocation_file, 'w') as f:
                json.dump(data, f, indent=2)
            logger.debug("Saved port allocations")
        except Exception as e:
            logger.error(f"Failed to save allocations: {e}")
    
    def _is_port_available(self, port: int, timeout: float = 0.1) -> bool:
        """
        Quick check if port is available
        Uses minimal timeout for speed
        """
        sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        sock.settimeout(timeout)
        try:
            # Try to bind to the port
            result = sock.connect_ex(('127.0.0.1', port))
            sock.close()
            return result != 0  # Port is available if connection fails
        except:
            sock.close()
            return True
    
    def _find_available_port(self, 
                            service: str,
                            max_attempts: int = 50,
                            quick_timeout: float = 0.05) -> Optional[int]:
        """
        Find available port with intelligent strategies
        1. Try preferred ports first
        2. Try last successful port
        3. Random search in service range
        4. Expand search to wider range
        """
        attempts = 0
        tested_ports = set()
        
        # Strategy 1: Try preferred ports
        if service in self.preferred_ports:
            for port in self.preferred_ports[service]:
                if port not in tested_ports:
                    tested_ports.add(port)
                    attempts += 1
                    if self._is_port_available(port, quick_timeout):
                        logger.info(f"Found preferred port {port} for {service}")
                        return port
        
        # Strategy 2: Try last successful port
        if service in self.allocations:
            last_port = self.allocations[service].port
            if last_port not in tested_ports:
                tested_ports.add(last_port)
                attempts += 1
                if self._is_port_available(last_port, quick_timeout):
                    logger.info(f"Reusing last port {last_port} for {service}")
                    return last_port
        
        # Strategy 3: Random search in service range
        if service in self.port_ranges:
            start_port, end_port = self.port_ranges[service]
        else:
            start_port, end_port = 8000, 9999
        
        while attempts < max_attempts:
            port = random.randint(start_port, end_port)
            if port not in tested_ports:
                tested_ports.add(port)
                attempts += 1
                if self._is_port_available(port, quick_timeout):
                    logger.info(f"Found random port {port} for {service}")
                    return port
        
        # Strategy 4: Expand search range
        logger.warning(f"Expanding search range for {service}")
        emergency_start = 10000
        emergency_end = 20000
        
        for _ in range(20):  # Final attempts
            port = random.randint(emergency_start, emergency_end)
            if self._is_port_available(port, quick_timeout * 2):
                logger.info(f"Found emergency port {port} for {service}")
                return port
        
        return None
    
    def allocate_port(self, 
                      service: str,
                      container_id: Optional[str] = None,
                      force: bool = False) -> int:
        """
        Allocate port for service
        Returns allocated port or raises exception
        """
        # Check existing allocation
        if not force and service in self.allocations:
            existing_port = self.allocations[service].port
            if self._is_port_available(existing_port):
                logger.info(f"Port {existing_port} still allocated to {service}")
                return existing_port
        
        # Find new port
        port = self._find_available_port(service)
        if not port:
            raise RuntimeError(f"Could not allocate port for {service}")
        
        # Record allocation
        self.allocations[service] = PortAllocation(
            service=service,
            port=port,
            allocated_at=datetime.now().isoformat(),
            container_id=container_id,
            pid=os.getpid()
        )
        self._save_allocations()
        
        return port
    
    def release_port(self, service: str):
        """Release port allocation"""
        if service in self.allocations:
            port = self.allocations[service].port
            del self.allocations[service]
            self._save_allocations()
            logger.info(f"Released port {port} from {service}")
    
    def get_all_allocations(self) -> Dict[str, int]:
        """Get all current port allocations"""
        return {
            service: alloc.port 
            for service, alloc in self.allocations.items()
        }
    
    def generate_docker_env(self, output_file: str = ".env.ports"):
        """Generate Docker environment file with port allocations"""
        ports = {
            'TWLAN_WEB_PORT': self.allocate_port('web'),
            'TWLAN_MYSQL_PORT': self.allocate_port('mysql'),
            'TWLAN_PHPMYADMIN_PORT': self.allocate_port('phpmyadmin'),
            'TWLAN_API_PORT': self.allocate_port('api'),
            'TWLAN_LEGACY_PORT': self.allocate_port('legacy'),
            'TWLAN_MODERN_PORT': self.allocate_port('modern'),
            'TWLAN_ADMIN_PORT': self.allocate_port('admin')
        }
        
        with open(output_file, 'w') as f:
            for key, port in ports.items():
                f.write(f"{key}={port}\n")
        
        logger.info(f"Generated port configuration: {output_file}")
        return ports
    
    def health_check(self, service: str) -> bool:
        """Check if allocated port is responding"""
        if service not in self.allocations:
            return False
        
        port = self.allocations[service].port
        max_attempts = 30
        
        for attempt in range(max_attempts):
            sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
            sock.settimeout(0.5)
            try:
                result = sock.connect_ex(('localhost', port))
                sock.close()
                if result == 0:
                    logger.info(f"Service {service} responding on port {port}")
                    return True
            except:
                pass
            
            if attempt < max_attempts - 1:
                time.sleep(1)
        
        logger.warning(f"Service {service} not responding on port {port}")
        return False

def main():
    """CLI interface for port management"""
    import argparse
    
    parser = argparse.ArgumentParser(description='TWLan Port Manager')
    parser.add_argument('command', choices=['allocate', 'release', 'list', 'check', 'env'])
    parser.add_argument('--service', help='Service name')
    parser.add_argument('--force', action='store_true', help='Force new allocation')
    parser.add_argument('--output', help='Output file for env command')
    
    args = parser.parse_args()
    
    manager = IntelligentPortManager()
    
    if args.command == 'allocate':
        if not args.service:
            print("Service name required")
            return 1
        port = manager.allocate_port(args.service, force=args.force)
        print(f"{port}")
        return 0
    
    elif args.command == 'release':
        if not args.service:
            print("Service name required")
            return 1
        manager.release_port(args.service)
        return 0
    
    elif args.command == 'list':
        allocations = manager.get_all_allocations()
        for service, port in allocations.items():
            print(f"{service}: {port}")
        return 0
    
    elif args.command == 'check':
        if not args.service:
            print("Service name required")
            return 1
        healthy = manager.health_check(args.service)
        return 0 if healthy else 1
    
    elif args.command == 'env':
        output_file = args.output or '.env.ports'
        ports = manager.generate_docker_env(output_file)
        for key, port in ports.items():
            print(f"{key}={port}")
        return 0

if __name__ == '__main__':
    exit(main() or 0)
