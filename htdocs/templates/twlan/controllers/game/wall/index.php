<?php
namespace Twlan;
?>
<table class="vis">
    <tbody>
        <tr>
            <th><?php l('building.level', ['x' => '']); ?></th>
            <th><?php l('buildingWall.basicDefense'); ?></th>
            <th><?php l('buildingWall.defensiveBonus'); ?></th>
        </tr>
        <tr>
            <td width="160"><?php l('game.current'); ?></td>
            <td width="160"><strong><?php echo $basic_defense; ?>
            </strong></td>
            <td width="160"><strong><?php echo round($bonus); ?>%</strong></td>
        </tr>
        <?php
        $level = $village->getBuilding($building->id);
        if($level < $building->maxLevel)
        {
        ?>
        <tr>
            <td><?php l('game.onLevel', ['level' => $level + 1]); ?></td>
            <td><strong><?php echo $basic_defense_next; ?></strong></td>
            <td><strong><?php echo round($bonus_next); ?>%</strong></td>
        </tr>
        <?php } ?>
    </tbody>
</table>
