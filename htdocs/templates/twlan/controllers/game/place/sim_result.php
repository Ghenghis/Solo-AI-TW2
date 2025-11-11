<?php
namespace Twlan;
?>
<table class="vis">
    <tr>
        <td colspan="2"></td>
        <?php foreach($this->world->units->getAll() as $unit) { ?>
        <th width="35"><img alt="" class="" src="graphic/unit/unit_<?php echo $unit->id; ?>.png" title="<?php echo $unit->getLocalizedId(); ?>"></th>
        <?php } ?>
    </tr>

    <?php foreach(array('attackingArmy', 'defendingArmy') as $type) { ?>
    <tr>
        <td rowspan="2"><?php l('buildingPlace.'.$type); ?></td>

        <td><?php l('buildingPlace.units'); ?>:</td>

        <?php foreach($result[$type]['before'] as $value) { ?>
        <td class='unit-item<?php if($value ==0) echo ' hidden'; ?>'><?php echo $value; ?></td>
        <?php } ?>
    </tr>

    <tr>
        <td><?php l('buildingPlace.losses'); ?>:</td>
        <?php foreach($result[$type]['after'] as $key => $value) { $amount = $result[$type]['before'][$key] - $value; ?>
        <td class='unit-item<?php if($amount == 0) echo ' hidden'; ?>'><?php echo $amount; ?></td>
        <?php } ?>
    </tr>
    <?php } ?>

    <tr>
        <td style="display:none"></td>
    </tr>
</table>

<table>
    <?php if(isset($result['wall'])) { ?>
    <tr>
        <th><?php l('buildingPlace.ramDamage'); ?>:</th>
        <td><?php l('buildingPlace.damagedWall', array('before' => $result['wall']['before'], 'after' => $result['wall']['after'])); ?></td>
    </tr>
    <?php } ?>

    <?php if(isset($result['building'])) { ?>
    <tr>
        <th><?php l('buildingPlace.catapultDamage'); ?>:</th>
        <td><?php l('buildingPlace.damagedBuilding', array('before' => $result['building']['before'], 'after' => $result['building']['after'])); ?></td>
    </tr>
    <?php } ?>
</table>
