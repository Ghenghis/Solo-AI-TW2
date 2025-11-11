<?php
namespace Twlan;
use Twlan\framework\Text;
?>
<script type="text/javascript">
    //<![CDATA[
    confirm_queue = false;
    $(document).ready(function ()
    {
        UI.ToolTip($('.upgrade_level'));
    });
    function upgrade_building(building)
    {
        var url = 'game.php?village=<?php echo $vid;?>&screen=main&ajaxaction=upgrade_building&type=class';
        var handleUpgradeBuilding = function ()
        {
            $.ajax(
            {
                async: false, // prevent fast clicking issues
                dataType: 'json',
                url: url,
                data: {
                    id: building,
                    force: 1,
                    source: game_data.village.id
                },
                success: function (build_ret)
                {
                    if(build_ret.error)
                    {
                        UI.InfoMessage(build_ret.error, null, true);
                    }
                    else if(build_ret.success)
                    {
                        //update resources
                        <?php $c = 0; foreach($this->world->getPhysicalResources() as $res) { ?>
                        $('#<?php echo $res; ?>').removeClass('warn').html(build_ret.resources[<?php echo $c; ?>]);
                        <?php ++$c; } ?>
                        $('#pop_current').html(build_ret.population);
                        startTimer();
                        //increase upgrade level
                        var upgrade_level_span = $('#order_level_' + building);
                        var old_upgrade_level = parseInt(upgrade_level_span.text(), 10) || 0;
                        upgrade_level_span.text("+" + (old_upgrade_level + 1));
                        //update links
                        $.getJSON('game.php?village=<?php echo $vid;?>&screen=main&ajax=get_possible_building_upgrades',
                        {}, function (ret)
                        {
                            var new_buildings = ret.buildings, new_title;
                            $('.upgrade_level').hide();
                            $.each(new_buildings, function (building_id, needed_resources)
                            {
                                new_title = generateResourcesLabel(needed_resources);
                                $("#upgrade_level_" + building_id).show().attr('title', new_title);
                            });
                            UI.ToolTip($('.upgrade_level'));
                        });
                        confirm_queue = build_ret.confirm_queue;
                    }
                }
            });
        }
        if(!confirm_queue)
        {
            handleUpgradeBuilding();
            return false;
        }
        var msg = "<?php l('buildingMain.queueBuild');?>";
        var buttons = [
        {
            text: "OK",
            callback: handleUpgradeBuilding,
            confirm: true
        }];
        UI.ConfirmationBox(msg, buttons);
    }
    function generateResourcesLabel(needed_resources)
    {
        return s("<?php l('game.wood');?>: %1, <?php l('game.stone');?>: %2, <?php l('game.iron');?>: %3, <br/> <?php l('game.population');?>: %4", needed_resources.wood, needed_resources.stone, needed_resources.iron, needed_resources.population);
    }
    //]]>
</script>
<?php $canBuild = array();
foreach($this->world->buildings->getAll() as $building)
{
    if($building->existsInVillage($village) && $building->canBuild($village, $levels[$building->id] + 1, $queueFactor) === TRUE)
    {
        $canBuild[] = $building->id;
    }
}?>
<table class="vis" width="100%">
    <?php foreach($this->world->buildings->getAll() as $building){if($building->existsInVillage($village)){$id = $building->id;?>
    <tr id="l_<?php echo $id;?>">
        <?php if(count($canBuild) > 0){?>
        <td>
            <?php if(in_array($id, $canBuild)){?>
            <a class="upgrade_level" id="upgrade_level_<?php echo $id;?>" href="#" onclick="upgrade_building('<?php echo $id;?>'); return false;" title="<?php foreach(array('wood', 'stone', 'iron') as $res){l('game.'.$res);?>: <?php echo $building->getBuildCost($res, $levels[$id] + 1);?>, <?php }?>&lt;br/&gt;<?php l('game.population');?>: <?php echo $building->getBuildCost('population', $levels[$id] + 1);?> &lt;br/&gt;<?php l('buildingMain.buildTime');?>: <?php echo $building->getBuildTime('wood', $levels[$id] + 1);?>">
                <img src="graphic/overview/build.png" alt="<?php l('buildingMain.build');?>" />
            </a>
            <?php }?>
        </td>
        <?php }?>
        <td width="240">
            <a href="game.php?village=<?php echo $vid;?>&amp;screen=<?php echo $id;?>">
                <img src="graphic/buildings/<?php echo $id;?>.png" alt="" />
                <?php echo $building->getLocalizedId(); ?>
            </a>
            <?php l('game.overview.level', array('x'=>$village->getBuilding($id), 'extra'=>'<small id="order_level_'.$id.'">'.(Text::absInt($levels[$id] - $village->getBuilding($id))).'</small>'));?>
        </td>
        <td class="building_extra">
            <?php echo $building->getExtraInfo($village, 1);?>
        </td>
    </tr>
    <?php }}?>
</table>
<div class="vis_item">
    <a href="game.php?village=<?php echo $vid;?>&amp;screen=overview&amp;action=set_visual&amp;visual=1"><?php l('game.overview.graphicalOverview');?></a>
</div>