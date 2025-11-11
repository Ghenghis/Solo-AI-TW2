<?php
namespace Twlan;
use Twlan\framework\Text;
?>
<table id="combined_table" class="vis overview_table" width="100%" style="white-space: nowrap;">
    <tr>
        <th>
            <span class="note-icon"></span>
        </th>
        <th style="text-align: left;">
            <a href="game.php?village=<?php echo $vid;?>
                &amp;screen=overview_villages&amp;mode=combined&amp;group=<?php echo $group; ?>&amp;page=<?php echo $page; ?>&amp;order=name&amp;dir=desc">
                    <?php ll('game.title.village'); ?>
            </a>
            (<?php echo count($villages);?>)
        </th>
        <th>
            <img src="graphic/overview/main.png" title="<?php ll('buildingMain.name'); ?>" alt="" />
        </th>
        <th>
            <img src="graphic/overview/barracks.png" title="<?php ll('buildingBarracks.name'); ?>" alt="" />
        </th>
        <th>
            <img src="graphic/overview/stable.png" title="<?php ll('buildingStable.name'); ?>" alt="" />
        </th>
        <th>
            <img src="graphic/overview/garage.png" title="<?php ll('buildingGarage.name'); ?>" alt="" />
        </th>
        <th>
            <img src="graphic/overview/smith.png" title="<?php ll('buildingSmith.name'); ?>" alt="" />
        </th>
        <th>
            <a href="game.php?village=<?php echo $vid;?>&amp;screen=overview_villages&amp;mode=combined&amp;group=<?php echo $group; ?>&amp;page=<?php echo $page; ?>&amp;order=pop_available&amp;dir=desc">
                <img src="graphic/overview/farm.png" title="<?php ll('buildingFarm.name'); ?>" alt="" />
            </a>
        </th>
        <?php foreach($this->world->units->getAll() as $unit) { if(isset($unit->hideinoverviews) && $unit->hideinoverviews) continue; ?>
        <th style="text-align: center">
            <a href="game.php?village=<?php echo $vid;?>&amp;screen=overview_villages&amp;mode=combined&amp;group=<?php echo $group; ?>&amp;page=<?php echo $page; ?>&amp;order=spear&amp;dir=desc">
                <img src="graphic/unit/unit_<?php echo $unit->id; ?>.png" alt="<?php echo $unit->getLocalizedId();?>" title="<?php echo $unit->getLocalizedId();?>" />
            </a>
        </th>
        <?php } ?>
        <th>
            <a href="game.php?village=<?php echo $vid;?>&amp;screen=overview_villages&amp;mode=combined&amp;group=<?php echo $group; ?>&amp;page=<?php echo $page; ?>&amp;order=trader_current&amp;dir=desc">
                <img src="graphic/overview/trader.png" title="<?php ll('game.market.traders');?>" alt="" />
            </a>
        </th>
    </tr>
    <?php foreach($villages as $vil) { $cid = $vil->id_village; ?>
    <tr class="nowrap row_a">
        <td></td>
        <td>
            <span id="label_<?php echo $cid;?>">
                <a href="game.php?village=<?php echo $cid;?>&amp;screen=overview">
                    <span id="label_text_<?php echo $cid;?>">
                        <?php echo $vil->getDisplayName(); ?>
                    </span>
                </a>
                <a class="rename-icon" href="javascript:editToggle('label_<?php echo $cid;?>', 'edit_<?php echo $cid;?>')">&nbsp;</a>
            </span>
            <span id="edit_<?php echo $cid;?>" style="display: none">
                <input id="edit_input_<?php echo $cid;?>" value="<?php echo Text::formatAll($vil->name);?>" onkeydown="if (event.keyCode == 13) { $(this).next().click(); return false; }" />
                <input type="button" value="OK" onclick="editSubmitNew('label_<?php echo $cid;?>', 'label_text_<?php echo $cid;?>', 'edit_<?php echo $cid;?>', 'edit_input_<?php echo $cid;?>', 'game.php?village=<?php echo $cid;?>&amp;screen=main&amp;ajaxaction=change_named');"/>
            </span>
        </td>
        <td>
            <a href="game.php?village=<?php echo $cid;?>&amp;screen=main">
                <img src="graphic/overview/prod_avail.png" title="<?php l('game.overviews.noprod'); ?>" alt="" class="status-icon" />
            </a>
        </td>
        <td>
            <a href="game.php?village=<?php echo $cid;?>&amp;screen=barracks">
                <img src="graphic/overview/prod_avail.png" title="<?php l('game.overviews.norec'); ?>" alt="" class="status-icon" />
            </a>
        </td>
        <td>
            <a href="game.php?village=<?php echo $cid;?>&amp;screen=stable">
                <img src="graphic/overview/prod_avail.png" title="<?php l('game.overviews.norec'); ?>" alt="" class="status-icon" />
            </a>
        </td>
        <td>
            <a href="game.php?village=<?php echo $cid;?>&amp;screen=garage">
                <img src="graphic/overview/prod_avail.png" title="<?php l('game.overviews.norec'); ?>" alt="" class="status-icon" />
            </a>
        </td>
        <td>
            <a href="game.php?village=<?php echo $cid;?>&amp;screen=smith">
                <img src="graphic/overview/prod_finish.png" title="<?php l('game.overviews.teccomplete'); ?>" alt="" class="status-icon" />
            </a>
        </td>
        <td>
            <a href="game.php?village=<?php echo $vid;?>&amp;screen=farm"><?php echo $vil->getMaxRes("population") - $vil->getPopulation();?> (<?php echo $vil->getBuilding('farm'); ?>)</a>
        </td>
        <?php
        $vilUnits = $vil->getOwnArmy()->getUnits();
        foreach($this->world->units->getAll() as $unit) {
            if (isset($unit->hideinoverviews) && $unit->hideinoverviews) continue;
            $unitAmount = $vilUnits[$unit->id];
        ?>
        <td class="unit-item<?php if(!$unitAmount)
            echo " hidden"; ?>">
            <?php if(isset($unit->linkinoverviews) && $unit->linkinoverviews) { 
                echo "<a href=\"game.php?village=".$vid."&amp;screen=".$unit->getRecruitBuilding()."\">".$unitAmount."</a>"; 
            } else echo $unitAmount; ?>
        </td>
        <?php }?>
        <td>
            <a href="game.php?village=<?php echo $cid;?>&amp;screen=market"><?php echo $vil->getPopulation(); ?>/<?php echo $vil->getMaxRes('population'); ?></a>
        </td>
    </tr>
    <?php }?>
</table>
