<?php
namespace Twlan;
?>
<h3><?php l('buildingPlaceNavi.units'); ?></h3>
<script type="text/javascript">
//<![CDATA[
    $(function(){
        JToggler.init('#units_home input[type="checkbox"]');
    });
//]]>
</script>

<form action="game.php?village=<?php echo $vid; ?>&amp;mode=units&amp;action=command_other&amp;screen=place" method="post">
<table id="units_home" class="vis" width="100%">
<tbody>
<tr>
<th><?php l('game.report.origin'); ?></th>
    <?php foreach($this->world->units->getAll() as $unit) { ?>
    <th style="text-align:center" width="40">
        <img src="graphic/unit/unit_<?php echo $unit->id; ?>.png" title="<?php echo $unit->getLocalizedId(); ?>" alt="" class="">
    </th>
    <?php } ?>
</tr>
<?php $u_sum = array(); foreach($ownArmies as $ownArmy) { ?>
<tr>
    <td><?php echo $ownArmy->id_village_from == $ownArmy->id_village_to ?
        ll('buildingPlace.fromThisVillage') :
        '<input name="id_'.$ownArmy->id_army.'" type="checkbox">'.
        '<a href="game.php?village='.$vid.'&amp;id='.$ownArmy->id_village_from.'&amp;screen=info_village">'.
        $ownArmy->getFromVillage()->getDisplayName().'</a>'
    ?></td>
    <?php $ownArmyUnits = $ownArmy->getUnits(); foreach($this->world->units->getAllIds() as $unit) {
        if (!isset($u_sum[$unit])) $u_sum[$unit] = 0;
        $u_sum[$unit] += $ownArmyUnits[$unit];
    ?>
    <td style="text-align:center" class="unit-item<?php if(!$ownArmyUnits[$unit]) echo ' hidden'; ?>">
        <?php echo $ownArmyUnits[$unit]; ?>
    </td>
    <?php } ?>
</tr>
<?php } ?>
<tr>
    <th><?php l('buildingPlace.total'); ?></th>
    <?php foreach($this->world->units->getAllIds() as $unit) { ?>
    <th style="text-align:center" class="unit-item<?php if(!$u_sum[$unit]) echo ' hidden'; ?>"><?php echo $u_sum[$unit]; ?></th>
    <?php } ?>
</tr>
</tbody>
</table>
<?php if(count($ownArmies) > 1) { ?>
<table align="left">
    <tbody>
        <tr><td><input class="btn" type="submit" name="back" value="<?php l('buildingPlace.sendback'); ?>"></td></tr>
    </tbody>
</table>
<?php } ?>

</form>
<br style="clear:both;">

<h3><?php l('buildingPlace.troopsOtherVillages'); ?></h3>
<script type="text/javascript">
//<![CDATA[
    $(function(){
        JToggler.init('#units_away input[type="checkbox"]');
    });
//]]>
</script>

<form action="game.php?village=<?php echo $vid; ?>&amp;action=withdraw_selected_units&amp;mode=units&amp;screen=place" method="post">
    <table id="units_away" class="vis groupcols">
        <tbody>
            <tr>
            <th></th>
            <th width="320"><?php l('game.village'); ?></th>
            <?php foreach($this->world->units->getAll() as $unit) { ?>
            <th style="text-align:center" width="auto">
                <img src="graphic/unit/unit_<?php echo $unit->id; ?>.png" title="<?php echo $unit->getLocalizedId(); ?>" alt="" class="">
            </th>
            <?php } ?>
            <th><?php l('buildingPlace.withdraw'); ?></th>
            </tr>
            <?php foreach($foreignArmies as $foreignArmy) { 
                $toVillage = $foreignArmy->getToVillage();
            ?>
            <tr>
                <td><input type="checkbox" name="withdraw_unit[]" value="<?php echo $foreignArmy->id_army; ?>"></td>
                <td>
                    <span class="village_anchor contexted" data-player="<?php echo $toVillage->id_user_owner; ?>" data-id="<?php echo $foreignArmy->id_army; ?>">
                        <a href="game.php?village=<?php echo $toVillage->id_village; ?>&amp;id=<?php echo $foreignArmy->id_army; ?>&amp;screen=info_village">
                            <?php echo $toVillage->getDisplayName(); ?>
                        </a>
                        <a class="ctx" href="#"></a>
                    </span>
                </td>
                <?php $foreignArmyUnits = $foreignArmy->getUnits(); foreach($this->world->units->getAllIds() as $unit) { ?>
                <td style="text-align:center" class="unit-item<?php if(!$foreignArmyUnits[$unit]) echo ' hidden'; ?>">
                    <?php echo $foreignArmyUnits[$unit]; ?>
                </td>
                <?php } ?>
                <td>
                    <a href="game.php?village=<?php echo $vid; ?>&amp;mode=units&amp;try=back&amp;unit_id=<?php echo $foreignArmy->id_army; ?>&amp;screen=place">
                        <?php l('buildingPlace.some'); ?>
                    </a> -
                    <a href="game.php?village=<?php echo $vid; ?>&amp;mode=units&amp;action=all_back&amp;unit_id=<?php echo $foreignArmy->id_army; ?>&amp;screen=place">
                        <?php l('buildingPlace.all'); ?>
                    </a>
                </td>
            </tr>
            <?php } ?>
            <tr></tr>
            <tr>
                <th colspan="<?php echo count($this->world->units->getAll()) + 2; ?>">
                    <input type="checkbox" id="select_all" class="selectAll" onchange="selectAll(this.form, this.checked)">
                    <label for="select_all"><?php l('game.report.selectAll'); ?></label>
                </th>
                <th>
                    <input class="btn" type="submit" value="<?php l('buildingPlace.withdraw'); ?>">
                </th>
            </tr>
        </tbody>
    </table>
</form>
