<?php
    namespace Twlan;
    use Twlan\framework\Text;
    $level = $village->getBuilding($building->id);
    if($level < $building->maxLevel)
    {
        $nextLevel = $level + 1;
    }
?>
<table class="vis" cellspacing="1" cellpadding="3">
    <tbody>
        <tr>
            <th><?php l('buildingRes.prod'); ?></th>
            <th><?php l('buildingRes.amountPerHour'); ?></th>
            <?php
            if(isset($nextLevel))
            {
            ?>
            <th><?php l('buildingRes.amountPerHourLvl', array('lvl' => $nextLevel)); ?></th>
            <?php } ?>
        </tr>
        <?php
            foreach($building->getProducedRes() as $res)
            {
                $localProduction = $building->getResProduction($res, $level);
                $totalProduction = $village->getResProduction($res);
                if(isset($nextLevel))
                {
                    $nextLocalProduction = $building->getResProduction($res, $nextLevel);
                    // simulate new village
                    $nextVillage = clone $village;
                    $nextVillage->setBuilding($building->id, $nextLevel);
                    $nextTotalProduction = $nextVillage->getResProduction($res);
                }
        ?>
        <tr>
            <td><img src="graphic/<?php echo $res; ?>.png" title="<?php l('game.'.$res); ?>" alt="" class=""> <?php l('buildingRes.baseProd'); ?></td>
            <td><?php echo Text::formatInt(round($localProduction), ll('game.thousandDelimeter')); ?></td>
            <?php
            if(isset($nextLevel))
            {
            ?>
            <td class="inactive"><?php echo Text::formatInt(round($nextLocalProduction), '.'); ?></td>
            <?php } ?>
        </tr>
        <tr>
            <td><img src="graphic/<?php echo $res; ?>.png" title="<?php l('game.'.$res); ?>" alt="" class=""> <?php l('buildingRes.currentProd'); ?></td>
            <td><b><?php echo Text::formatInt(round($totalProduction), ll('game.thousandDelimeter')); ?></b></td>
            <?php
            if(isset($nextLevel))
            {
            ?>
            <td class="inactive"><?php echo Text::formatInt(round($nextTotalProduction), '.'); ?></td>
            <?php } ?>
        </tr>
        <?php } ?>
    </tbody>
</table>
