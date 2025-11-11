"""
Unit tests for cost calculations
"""

import pytest
from core.costs import UnitCosts, BuildingCosts, get_build_cost, get_recruit_cost


class TestUnitCosts:
    """Test unit cost calculations"""
    
    def test_spear_cost(self):
        """Test spearman costs"""
        wood, clay, iron = UnitCosts.get_cost('spear')
        assert wood == 50
        assert clay == 30
        assert iron == 10
    
    def test_noble_cost(self):
        """Test noble (expensive) costs"""
        wood, clay, iron = UnitCosts.get_cost('snob')
        assert wood == 40000
        assert clay == 50000
        assert iron == 50000
    
    def test_invalid_unit_raises_error(self):
        """Test invalid unit type raises ValueError"""
        with pytest.raises(ValueError):
            UnitCosts.get_cost('invalid_unit')
    
    def test_get_population(self):
        """Test population cost retrieval"""
        assert UnitCosts.get_population('spear') == 1
        assert UnitCosts.get_population('light') == 4
        assert UnitCosts.get_population('snob') == 100
    
    def test_get_build_time(self):
        """Test build time retrieval"""
        assert UnitCosts.get_build_time('spear') == 600
        assert UnitCosts.get_build_time('catapult') == 3600


class TestBuildingCosts:
    """Test building cost calculations"""
    
    def test_barracks_level_1(self):
        """Test barracks level 1 costs"""
        wood, clay, iron = BuildingCosts.get_cost('barracks', 1)
        assert wood == 200
        assert clay == 170
        assert iron == 90
    
    def test_cost_scaling(self):
        """Test exponential cost scaling with levels"""
        wood1, clay1, iron1 = BuildingCosts.get_cost('main', 1)
        wood2, clay2, iron2 = BuildingCosts.get_cost('main', 2)
        
        # Level 2 should cost more than level 1
        assert wood2 > wood1
        assert clay2 > clay1
        assert iron2 > iron1
        
        # Should be roughly 1.26x (multiplier)
        assert wood2 == pytest.approx(wood1 * 1.26, rel=0.01)
    
    def test_high_level_costs(self):
        """Test costs scale appropriately at high levels"""
        wood10, clay10, iron10 = BuildingCosts.get_cost('main', 10)
        wood1, clay1, iron1 = BuildingCosts.get_cost('main', 1)
        
        # Level 10 should be significantly more expensive
        assert wood10 > wood1 * 5
    
    def test_invalid_building_raises_error(self):
        """Test invalid building type raises ValueError"""
        with pytest.raises(ValueError):
            BuildingCosts.get_cost('invalid_building', 1)
    
    def test_invalid_level_raises_error(self):
        """Test invalid level raises ValueError"""
        with pytest.raises(ValueError):
            BuildingCosts.get_cost('main', 0)
        
        with pytest.raises(ValueError):
            BuildingCosts.get_cost('main', 31)
    
    def test_build_time_calculation(self):
        """Test build time calculation"""
        time = BuildingCosts.get_build_time('barracks', 1, main_level=1)
        assert time > 0
        
        # Higher HQ level should reduce time
        time_hq10 = BuildingCosts.get_build_time('barracks', 1, main_level=10)
        assert time_hq10 < time


class TestCostHelpers:
    """Test convenience helper functions"""
    
    def test_get_build_cost(self):
        """Test get_build_cost helper"""
        wood, clay, iron = get_build_cost('barracks', 5)
        assert wood > 0
        assert clay > 0
        assert iron > 0
    
    def test_get_recruit_cost_single_unit(self):
        """Test recruit cost for single unit type"""
        units = {'spear': 10}
        wood, clay, iron = get_recruit_cost(units)
        assert wood == 500  # 50 * 10
        assert clay == 300  # 30 * 10
        assert iron == 100  # 10 * 10
    
    def test_get_recruit_cost_multiple_units(self):
        """Test recruit cost for multiple unit types"""
        units = {
            'spear': 10,
            'sword': 5,
            'axe': 3
        }
        wood, clay, iron = get_recruit_cost(units)
        
        # 10*50 + 5*30 + 3*60 = 830
        assert wood == 500 + 150 + 180
        # 10*30 + 5*30 + 3*30 = 540
        assert clay == 300 + 150 + 90
        # 10*10 + 5*70 + 3*40 = 570
        assert iron == 100 + 350 + 120
    
    def test_get_recruit_cost_empty_units(self):
        """Test recruit cost with no units"""
        units = {}
        wood, clay, iron = get_recruit_cost(units)
        assert wood == 0
        assert clay == 0
        assert iron == 0
