<?php namespace Twlan; 
use Twlan\framework\Time;
?>
<?php if(count($units) > 0) { ?>
<form action="game.php?village=<?php echo $vid; ?>&amp;action=train&amp;mode=<?php echo $isTrain ? 'train' : 'decommission'; ?>&amp;screen=<?php echo $screen; ?>" id="train_form" method="post">
    <table class="vis" style="width: 100%">
        <tbody>
            <tr>
                <th style="width: 25%"><?php l('game.unit'); ?></th>
                <th style="width: 40%"><?php l('buildingSmith.costs'); ?></th>
                <th><?php l('game.inVillage'); ?></th>
                <th style="width: 150px"><?php l('game.unit.recruit'); ?></th>
            </tr>

            <?php foreach($units as $unit) { ?>
                <tr class="row_a">
                    <td class="nowrap">
                        <a href="#" class="unit_link" onclick="return UnitPopup.open(event, '<?php echo $unit->id; ?>')">
                            <img src="graphic/unit/recruit/<?php echo $unit->id; ?>.png" style="vertical-align: middle" alt="">
                            <?php echo $unit->getLocalizedId(); ?>
                        </a>
                    </td>
                <td>
                    <div class="recruit_req">
                        <?php foreach($unit->getRecruitCosts('population') as $name => $value) { ?>
                        <span>
                            <span class="icon header <?php echo $name; ?>"> </span>
                            <span id="<?php echo $unit->id; ?>_0_cost_<?php echo $name; ?>">
                                <?php echo $name == 'time' ? Time::date(
                                    $isTrain ? $unit->getRecruitTime($unit->getRecruitBuilding(TRUE)->getLevel($village)) :
                                    $unit->getDecommissionTime($unit->getRecruitBuilding(TRUE)->getLevel($village))
                                ) : $value; ?>
                            </span>
                        </span>
                        <?php } ?>
                    </div>
                </td>
                <?php
                    //use unit_popup's data
                    $elem = array();
                    $elem['available'] = $unitAmountData['available'][$unit->id];
                    $elem['all_count'] = $unitAmountData['all'][$unit->id];
                ?>
                <td style="text-align: center"><?php echo $elem['available'].'/'.$elem['all_count']; ?></td>
                    <td>
                        <span id="<?php echo $unit->id; ?>_0_interaction">
                            <input name="<?php echo $unit->id; ?>" class="recruit_unit" id="<?php echo $unit->id; ?>_0" type="text" style="width: 50px; color: black;" maxlength="5" tabindex="1">
                            <a id="<?php echo $unit->id; ?>_0_a" href="javascript:unit_build_block.set_max('<?php echo $unit->id; ?>')">
                                (<?php echo $isTrain ? floor($unit->getMaxRecruitableAmount($village)) : $village->getOwnArmy()->getUnit($unit->id); ?>)
                            </a>
                        </span>
                        <span id="<?php echo $unit->id; ?>_0_afford_hint" class="inactive" style="text-align: center; font-size: 11px;">

                        </span>
                    </td>
                </tr>
            <?php } ?>
            <tr>
                <td colspan="3"></td>
                <td>
                    <input class="btn btn-recruit" style="float: inherit" type="submit" value="<?php echo $isTrain ? ll('game.unit.recruit') : ll('game.unit.decommission'); ?>
                        " tabindex="4">
                </td>
            </tr>
        </tbody>
    </table>
</form>
<?php } ?>
<br>
<?php if(count($not_available_units) > 0) { ?>
<table class="vis" style="width: 100%">
    <tbody>
        <tr>
            <th style="width: 25%"><?php l('game.unit.notAvailable'); ?></th>
            <th><?php l('buildingSmith.costs'); ?></th>
        </tr>
        <?php foreach($not_available_units as $unit) { ?>
        <tr style="line-height: 30px">
            <td>
                <a href="#" class="unit_link" onclick="return UnitPopup.open(event, '<?php echo $unit->id; ?>')">
                    <img src="graphic/unit/recruit/grey/<?php echo $unit->id; ?>.png" style="opacity: 0.7" alt="">
                    <?php echo $unit->getLocalizedId(); ?>
                </a>
            </td>
            <td>
                <div class="unmet_req float_left" style="width: 390px">
                    <?php foreach($unit->getRequirements() as $name => $value) { ?>
                        <?php $building = $this->world->buildings->get($name); ?>
                        <span>
                            <span>
                                <img src="graphic/buildings/mid/<?php echo $building->getImage($value); ?>" style="vertical-align: middle" alt="">
                                <span><?php echo $building->getLocalizedId(); ?> (<?php l('building.level', array('x' => $value)); ?>)</span>
                            </span>
                        </span>
                    <?php } ?>
                </div>
                <span class="float_right" style="margin-right: 5px"><img src="graphic/overview/research.png?79f5f" style="vertical-align: middle" alt="">&nbsp;<a href="game.php?village=<?php echo $vid; ?>&amp;screen=smith" style="float: right"><?php l('buildingSmith.research'); ?></a></span>
            </td>
        </tr>
        <?php } ?>
    </tbody>
</table>
<?php } ?>
<?php require('train_js.php'); ?>
            