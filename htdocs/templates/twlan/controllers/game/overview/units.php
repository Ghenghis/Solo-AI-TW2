<?php namespace Twlan; ?>
<table class="vis" width="100%">
    <?php
    $army = $this->village->getAggregatedArmy();
    foreach ($this->world->units->getAll() as $unitInstance) { 
    $amount = $army->getUnit($unitInstance->id);
    if ($amount == 0) continue;
    ?>
    <tr>
        <td>
            <img src="graphic/unit/unit_<?php echo $unitInstance->id; ?>.png" alt="" />
            <strong><?php echo $amount; ?></strong>
            <?php echo $unitInstance->getLocalizedId(); ?>
        </td>
    </tr>
    <?php } ?>
    <tr>
        <td>
            <a href="game.php?village=<?php echo $vid;?>&amp;screen=train"><?php l('game.overview.recruit'); ?></a>
        </td>
    </tr>
</table>