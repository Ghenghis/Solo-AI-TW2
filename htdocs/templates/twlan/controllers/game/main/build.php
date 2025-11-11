<?php
namespace Twlan;
use Twlan\framework\Time;
?>
<div id="building_wrapper">
    <table id="buildings" class="vis nowrap" width="100%">
        <tr>
            <th width="220"><?php l('buildingMain.building');?></th>
            <th colspan="4"><?php l('buildingMain.costs');?></th>
            <th width="100"><?php l('buildingMain.buildTime');?> <img src="graphic/questionmark.png" class="tooltip" title="hh:mm:ss" width="13" height="13" alt="" /></th>
            <th style="width:200px;"><?php l('buildingMain.build');?></th>
        </tr>
        <?php foreach($buildings['active'] as $id => $lvl){$building = $this->world->buildings->get($id);?>
        <tr id="main_buildrow_<?php echo $id;?>"<?php if($lvl >= $building->maxLevel){echo ' class="completed"';}?>>
            <td>
                <a href="game.php?village=<?php echo $vid;?>&amp;screen=<?php echo $id;?>">
                    <img src="graphic/buildings/mid/<?php echo $building->getImage($village->getBuilding($building->id)); ?>" title="<?php echo $building->getLocalizedId();?>" alt="" class="bmain_list_img" />
                    <?php echo $building->getLocalizedId();?>
                </a>
                <br>
                <span style="font-size: 0.9em"><?php $l = $village->getBuilding($id);echo $l > 0 ? ll('building.level', array('x'=>$l)) : ll('building.noLevel');?></span>
            </td>
            <?php if($lvl >= $building->maxLevel){?>
            <td colspan="6" align="center" class="inactive"><?php l('buildingMain.fullyBuilt');?></td>
            <?php }else{?>
            <?php foreach($this->world->getPhysicalResources() as $res) { $cost = $building->getBuildCost($res, $lvl + 1); ?>
            <td class="cost_<?php echo $res; ?><?php if ($this->village->getRes($res) < $cost) echo " warn"; ?>" data-cost="<?php echo $cost; ?>">
                <span class="icon header <?php echo $res; ?>"></span><?php echo $cost; ?>
            </td>
            <?php }?>
            <td><span class="icon header population"></span><?php echo ($lvl == 0) ? $building->getBuildCost('population', $lvl + 1) : ($building->getBuildCost('population', $lvl + 1) - $building->getBuildCost('population', $lvl));?></td>
            <td><span class="icon header time"></span><?php echo Time::date($building->getBuildTime($lvl + 1, $main_lvl));?></td>
            <td class="build_options" style="white-space: normal; width: 300px">
                <?php $state = $building->canBuild($village, $lvl + 1, $queueFactor); ?>
                <a class="btn btn-build"
                <?php if($state !== true) echo " style=\"display:none\""; ?>
                data-building="<?php echo $building->id; ?>"
                id="main_buildlink_<?php echo $id;?>" href="#" 
            >
                    <?php echo $lvl == 0 ? ll('buildingMain.build') : ll('buildingMain.buildTo', array('x'=>$lvl + 1));?>
                </a>
                <span class="inactive"<?php if($state === true) echo " style=\"display:none\""; ?>><?php echo $state;?></span>
            </td>
            <?php }?>
        </tr>
        <?php }?>
    </table>
    <br>
    <?php if(count($buildings['inactive']) > 0){?>
    <table id="buildings_unmet" class="vis nowrap tall" style="width: 100%">
		<tbody>
            <tr>
                <th style="width: 23%"><?php l('buildingMain.unmet'); ?></th>
                <th><?php l('buildingMain.requirements'); ?></th>
            </tr>
            <?php foreach($buildings['inactive'] as $id => $lvl) { $building = $this->world->buildings->get($id); ?>
            <tr>
                <td>
                    <a href="game.php?village=<?php echo $vid; ?>&amp;screen=<?php echo $id; ?>">
                        <img src="graphic/buildings/mid/grey/<?php echo $building->getImage($village->getBuilding($building->id)); ?>" title="" class="bmain_list_img" alt="">
                    </a>
                    <a href="game.php?village=<?php echo $vid; ?>&amp;screen=<?php echo $id; ?>"><?php echo $building->getLocalizedId();?></a>
                </td>
                <td>
                    <div class="unmet_req">
                            <?php $c = 3; foreach($building->requirements as $bid => $req) { $r_building = $this->world->buildings->get($bid); --$c; ?>
                            <span>
                            <span>
                                <?php  $met = $r_building->getLevel($village) >= $req; ?>
                                <img src="graphic/buildings/mid/<?php if(!$met) echo 'grey/'; echo $r_building->getImage($village->getBuilding($r_building->id)); ?>" style="vertical-align: middle" alt="">
                                <span<?php if(!$met) echo ' class="inactive"';?>><?php echo $r_building->getLocalizedId(); ?> (<?php echo $req;?>)</span>
                            </span>
                            </span>
                            <?php } for(;$c>0;--$c) { echo '<span></span>'; } ?>
                    </div>
                </td>
            </tr>
            <?php }?>
        </tbody>
    </table>
    <br>
    <?php }?>
</div>
