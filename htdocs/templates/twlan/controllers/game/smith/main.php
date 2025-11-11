<?php
namespace Twlan;
use Twlan\framework\Time;
?>
<table class="vis tall" id="tech_list" style="width: 100%">
  <tbody>
    <tr>
      <th><?php l('buildingSmith.technology'); ?></th>
      <th colspan="3"><?php l('buildingSmith.costs'); ?></th>
      <th><?php l('game.unit.researchTime'); ?></th>
      <th><?php l('buildingSmith.research'); ?></th>
    </tr>
    <?php foreach($units as $unit) { $unit_level = $unit->getResearchLevel($this->village); 
      $reqMet = $unit->checkRequirements($village);
    ?>
    <tr>
        <td>
            <div class="float_left unit_sprite unit_sprite_smaller <?php echo $unit->id; 
            if(!$reqMet) echo '_cross'; else if($unit_level == 0) echo '_grey'; ?>"></div>
            &nbsp;
            <a href="#" class="unit_link" onclick="return UnitPopup.open(event, '<?php echo $unit->id; ?>')">
                <?php echo $unit->getLocalizedId(); ?> 
                <?php if($unit_level) echo '('.$unit_level.')'; ?>
            </a>
        </td>

        <?php
        $evt_queue = 0;
        foreach ($events['events'] as $event)
        {
            if ($event['technology'] != $unit->id) continue;
            $evt_queue++;
        }
        $unit_level += $evt_queue;
        //echo l('buildingSmith.researchConditionsUnmet');
        $_error = null;
        $_resStatus = $unit->isResearchable($this->village);
        if ($_resStatus["type"] == 0) {
            $_error = $_resStatus["error"];
        }
        if(isset($_error)) { ?>
            <td colspan="5" class="inactive">
                <?php echo $_error; ?>
            </td>
        <?php } else foreach($unit->getResearch($village) as $type => $value) { 
          if($type == 'min' || $type == 'max' || $type == 'time') continue; ?>
        <td>
            <span class="nowrap<?php if ($this->village->getRes($type) < $value) echo " warn"; ?>" id="<?php echo $unit->id; ?>_cost_<?php echo $type; ?>">
                <span class="icon header <?php echo $type; ?>"></span>
                <?php echo $value; ?>
            </span>
        </td>
        <?php } ?>

        <?php if(!isset($_error)) { ?>
        <td>
            <span class="icon header time"></span>
            <?php echo Time::date($unit->getResearchTime($village)); ?>
        </td>
        <td>
            <a class="btn btn-research" href="#" onclick="return BuildingSmith.research('<?php echo $unit->id; ?>');">
              <?php l('building.level', array('x' => $unit_level + 1)); ?>
            </a>
        </td>
        <?php } ?>
    </tr>
    <?php } ?>
    </tbody>
</table>