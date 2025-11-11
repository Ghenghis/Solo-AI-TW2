<?php
    namespace Twlan;
    use Twlan\framework\Text;
    use Twlan\framework\Time;
?>
<br/>
<table class="vis " width="600px">
    <tr><th><?php l('buildingStorage.capacity'); ?></th><th><?php l('buildingStorage.amountPerRes'); ?></th></tr>
    <tr>
        <td><?php l('buildingStorage.currentCapacity'); ?></td>
        <td width="160px"><b><?php echo Text::formatInt($building->getMaxRes('wood', $building_level), ll('game.thousandDelimeter')); ?></b></td>
    </tr>
    <?php
    if($building_level < $building->maxLevel)
    {
    ?>
    <tr>
        <td><?php echo ll('buildingStorage.capacityAtLevel').' '.($building_level + 1); ?> </td>
        <td><b><?php echo Text::formatInt($building->getMaxRes('wood', $building_level + 1), ll('game.thousandDelimeter')); ?></b></td>
    </tr>
    <?php } ?>
</table>

<br />
<table class="vis " width="600px">
    <tr>
        <th width="150" colspan="2"><?php l('buildingStorage.tableHead'); ?></th>
        <th><?php l('buildingStorage.timeDescription'); ?></th>
    </tr>
            <?php foreach($building->getManagedRes() as $res) {
                $prod = $village->getResProduction($res);
                $l = ($village->getMaxRes($res) - $village->getRes($res)) * 3600 / $prod;
                $class = $l <= 0 ? 'warn' : 'timer';
                $text = array($l <= 0 ? ll('buildingStorage.full') : Time::onTime($l + time()), $l <= 0 ? ll('buildingStorage.full') : Time::date($l));
                $data = array('<strong'.($l <= 0 ? ' class="warn"' : '').'>'.$text[0].'</strong>', '<span class="'.$class.'">'.$text[1].'</span>');
            ?>
            <tr>
                <td <?php if($class != 'timer') { ?> colspan="3" class="error" <?php } ?>>
                    <img src="graphic/<?php echo $res; ?>.png" title="<?php l('game.'.$res); ?>" alt="" class="" />
                <?php if($class == 'timer') { ?>
                    </td>
                    <td><?php echo $data[0]; ?></td>
                    <td width="160px"><?php echo $data[1]; ?></td>
                <?php } else { ?>
                        <?php l('buildingStorage.isFull'); ?>
                    </td>
                <?php } ?>
            </tr>
            <?php } ?>
</table>
