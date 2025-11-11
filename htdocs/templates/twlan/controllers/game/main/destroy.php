<?php
namespace Twlan;
use Twlan\framework\Time;
?>
<table class="vis tall" width="100%" id="building_wrapper">
    <tr>
        <th><?php l('buildingMain.building');?></th>
        <th><?php l('buildingMain.destroyTime');?> <img src="graphic/questionmark.png" class="tooltip" title="hh:mm:ss" width="13" height="13" alt="" /></th>
        <th><?php l('game.population');?></th>
        <th><?php l('buildingMain.destroy');?></th>
    </tr>
    <?php foreach($buildings as $id => $lvl){$building = $this->world->buildings->get($id);?>
    <tr>
        <td>
            <a href="game.php?village=<?php echo $vid;?>&amp;screen=<?php echo $id;?>">
                <img src="graphic/buildings/mid/<?php echo $building->getImage($village->getBuilding($building->id)); ?>" alt="<?php echo $building->getLocalizedId();?>" />
                <?php echo $building->getLocalizedId();?>
            </a>
            (<?php $l = $village->getBuilding($id);echo $l > 0 ? ll('building.level', array('x'=>$l)) : ll('building.noLevel');?>)
        </td>
        <?php if($lvl <= $building->minLevel || (isset($building->destroyMin) && $building->destroyMin >= $lvl)){?>
        <td colspan="3" align="center" class="inactive"><?php l('buildingMain.fullyDestroyed');?></td>
        <?php }else{?>
        <td><span class="icon header time"></span><?php echo Time::date($building->getBuildTime($lvl, $main_lvl));?></td>
        <td>
            <span class="icon header population"></span><?php echo ($lvl == 1) ? $building->getBuildCost('population', $lvl) : ($building->getBuildCost('population', $lvl) - $building->getBuildCost('population', $lvl - 1));?>
        </td>
        <td>
            <a class="btn" href="#" onclick="return BuildingMain.destroy('<?php echo $id;?>')"><?php l('buildingMain.destroyLevel');?></a>
        </td>
        <?php }?>
    </tr>
    <?php }?>
</table>