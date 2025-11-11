<?php
    namespace Twlan;
    use Twlan\framework\Text;
    $level = $village->getBuilding($building->id);
?>
<table class="vis">
    <tbody>
        <tr>
            <th colspan="2"><?php l('buildingHide.size'); ?></th>
        </tr>
        <tr>
            <td width="200"><?php l('buildingHide.currentSize'); ?></td>
            <td><b><?php echo Text::formatInt(round($building->getResHide($level)), '.'); ?></b> <?php l('buildingStorage.amountPerRes'); ?></td>
        </tr>
        <?php
        if($level < $building->maxLevel)
        {
        ?>
        <tr>
            <td><?php l('buildingHide.sizeAtLevel', array('lvl' => $level + 1)); ?></td>
            <td><b><?php echo Text::formatInt(round($building->getResHide($level + 1)), '.'); ?></b> <?php l('buildingStorage.amountPerRes'); ?></td>
        </tr>
        <?php } ?>
        <tr>
            <td><span><?php l('buildingHide.plunderRes'); ?></span></td>
            <td>
                <span>
                    <?php foreach($this->world->getPhysicalResources() as $res) { ?>
                    <span class="icon header <?php echo $res; ?>">
                    </span>
                    <?php echo ($pl = round($village->getRes($res) - $building->getResHide($building->getLevel($village)))) < 0 ? 0 : $pl;
                    } ?>
                </span>
            </td>
        </tr>
        <tr>
            <td colspan="2"><?php l('buildingHide.marketPlunder'); ?></td>
        </tr>
    </tbody>
</table>
