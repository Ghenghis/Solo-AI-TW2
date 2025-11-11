<?php
namespace Twlan;
use Twlan\framework\Text;
?>
<script type="text/javascript">
    //<![CDATA[
    $(document).ready(function(){ UpgradeBuilding.init('game.php?village=<?php echo $vid;?>&screen=main&ajaxaction=get_possible_building_upgrades', 'game.php?village=<?php echo $vid;?>&screen=main&ajaxaction=upgrade_building&type=vis'); });
    //]]>
</script>
<table width="100%">
    <tr>
        <td>
            <a href="game.php?village=<?php echo $vid;?>&amp;screen=overview&amp;action=set_labels&amp;labels=<?php echo $village->show_levels ? '0">'.ll('game.overview.hideLevels') : '1">'.ll('game.overview.showLevels');?></a>
        </td>
        <td style="text-align: center">
            <a href="#" onclick="UpgradeBuilding.show_upgrade_buildings();return false;"><?php l('game.overview.build');?></a>
        </td>
        <td align="right">
            <a href="game.php?village=<?php echo $vid;?>&amp;screen=overview&amp;action=set_visual&amp;visual=0"><?php l('game.overview.classicalOverview');?></a>
        </td>
    </tr>
</table>

<div class="widget_content" style="display: block;">
<div id="buildings_visual" style="position:relative; margin: 5px; width: 600px;">
    <img width="600" height="418" src="graphic/<?php echo $visual;?>/back_none.jpg" alt="" />
    <?php if(!in_array('church_f', $this->world->buildings->getAllIds()) && !in_array('church', $this->world->buildings->getAllIds())){?>
    <img class="p_church" src="graphic/<?php echo $visual;?>/church_disabled.png" alt="" />
    <?php }if(date('m') == 12 && date('d') >= 23){?>
    <img class="christmas_tree" src="graphic/<?php echo $visual;?>/christmas_tree.png" alt="" />
    <?php }foreach($this->world->buildings->getAll() as $building){
        foreach($building->getVisual($village) as $k => $v){ ?>
    <img class="<?php echo $k;?>" src="graphic/<?php echo $v;?>" alt="<?php echo $building->getLocalizedId();?>" />
    <?php }}?>
    <?php foreach(array('juggler', 'guard', 'conversation') as $value){if(rand(0,5) == 0){?>
    <img class="npc_<?php echo $value;?>" src="graphic/<?php echo $visual;?>/<?php echo $value;?>.gif" alt="" />
    <?php }}?>
    <img class="empty" src="graphic/map/empty.png" alt="" usemap="#map" />
    <map name="map" id="map">
        <?php foreach($this->world->buildings->getAll() as $building){if($village->getBuilding($building->id) <= 0){continue;}$id = $building->id;?>
        <area id="map_<?php echo $id;?>" shape="poly" coords="<?php echo $building->shape;?>" href="game.php?village=<?php echo $vid;?>&amp;screen=<?php echo $id;?>" alt="<?php echo $building->getLocalizedId(); ?>" title="<?php echo $building->getLocalizedId(); ?>" />
        <?php }?>
    </map>
    <?php foreach($this->world->buildings->getAll() as $building){$id = $building->id;?>
    <div id="l_<?php echo $id;?>" class="l_<?php echo $id;?>" style="display: inline" title="<?php echo $building->getLocalizedId(); ?>">
        <?php
        $labels = [];
        if($village->show_levels) {
            if(TWLan::isNight($this->world)) $labels[] = "label_night"; else $labels[] = "label";
            if(!$building->existsInVillage($village)) $labels[] = "label_no_lvl";
        ?>
        <div class="<?php echo implode(" ", $labels); ?>">
            <?php if($village->getBuilding($id) > 0){?>
            <a href="game.php?village=<?php echo $vid;?>&amp;screen=<?php echo $id;?>">
                <img src="graphic/buildings/<?php echo $id;?>.png" class="middle" alt="<?php echo $building->getLocalizedId(); ?>" />
                <?php if($village->show_levels) { ?>
                    <?php echo $village->getBuilding($id); ?>
                    <span class="building_order_level"><?php if(isset($levels[$id])) echo Text::absInt($levels[$id] - $village->getBuilding($id));?></span>
                <?php } ?>
            </a>
            <?php } if($village->show_levels) { ?>
                <br />
                <span class="building_extra" style="font-size:8px !important; font-weight:bold"><?php echo $building->getExtraInfo($village, 0);?></span>
            <?php } ?>
        </div>
        <?php } ?>
    </div>
    <?php }?>
</div>
</div>
