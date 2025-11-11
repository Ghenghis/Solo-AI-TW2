<?php 
namespace Twlan; 
use Twlan\framework\Time;
use Twlan\framework\Text;
?>
<form id="command-confirm-form" action="game.php?village=<?php echo $vid; ?>&amp;action=command&amp;screen=place" method="post" onsubmit="this.submit.disabled=true;">
	<input type="hidden" name="<?php if($isAttack) echo 'attack'; else echo 'support'; ?>" value="true">

	<h2><?php $description = ll('buildingPlace.'.($isAttack ? 'confirmAttack' : 'confirmSupport')
    , array('name' => Text::formatAll($target_village->name))); echo $description; ?></h2>


	<input type="hidden" name="ch" value="13b1cd38b7f677813d3bddb0505afce64d3ce4d9">
	<input type="hidden" name="x" value="<?php echo $target_village->x; ?>">
	<input type="hidden" name="y" value="<?php echo $target_village->y; ?>">
	<input type="hidden" name="action_id" value="4165">

    <table class="vis" width="300">
        <tbody>
            <tr>
                <th colspan="2"><span id="default_name_span" style=
                "display: inline;"><span id="default_name"><?php echo $description; ?></span> <a href=
                "#" onclick=
                "editToggle('default_name_span', 'edit_name')"><img alt=
                "umbenennen" src="graphic/rename.png?1" title=
                "umbenennen"></a></span> <span id="edit_name" style=
                "display: none;"><input id="new_attack_name" onkeypress=
                "if( (event.keyCode || event.which) != 13) return true; $('#attack_name_btn').click(); return false;"
                type="text"><input id="attack_name_btn" onclick=
                "renameAttack('new_attack_name','default_name', 'attack_name');editToggle('edit_name', 'default_name_span');"
                type="button" value="Ok"></span> <input id="attack_name" name=
                "attack_name" type="hidden" value="<?php echo $description; ?>"></th>
            </tr>

            <tr>
                <td><?php l('buildingPlace.target'); ?>:</td>

                <td>
                	<span class="village_anchor contexted" data-id="<?php echo $target_village->id_village; ?>" data-player="0"><a href="game.php?village=<?php echo $vid; ?>&amp;id=<?php echo $target_village->id_village; ?>&amp;screen=info_village">
	                	<?php echo Text::formatAll($target_village->name); ?> (<?php echo $target_village->x; ?>|<?php echo $target_village->y; ?>) 
	                		<?php echo $target_village->getContinent(); ?>
	                	</a>
	                	<a class="ctx" href="#"></a>
            		</span>
        		</td>
            </tr>

            <tr>
                <td><?php echo l('buildingPlace.duration'); ?>:</td>
                <td><?php echo Time::countDown($target_army->finish); ?></td>
            </tr>

            <tr>
                <td><?php echo l('buildingPlace.arrival'); ?>:</td>

                <td id="date_arrival"><span class="relative_time" data-duration="<?php echo Time::countDown($target_army->finish, false); ?>"></span></td>
            </tr>

            <?php if (isset($target_army->morale)) { ?> 
            <tr>
                <td><?php echo l('buildingPlace.moral'); ?>:</td>
                <td style="font-weight: bold"><?php echo round($target_army->morale * 100); ?>%</td>
            </tr>
            <?php } ?>

            <?php if (isset($target_army->maxBounty)) { ?>
            <tr>
                <td colspan="2">
                	<span class="icon header ressources" title="<?php l('game.unit.capacity'); ?>"></span>
                	<?php echo Text::formatInt($target_army->maxBounty, "<span class=\"grey\">.</span>"); ?>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
    <br>

    <table class="vis">
        <tbody>
            <tr>
            	<?php foreach($units as $unit) { ?>
                <th width="50"><img alt="" class="" src="graphic/unit/unit_<?php echo $unit->id; ?>.png"
                title="<?php echo $unit->getLocalizedId(); ?>"></th>
                <?php } ?>
            </tr>

            <tr>
            	<?php foreach($units as $unit) { ?>
                <td class="unit-item<?php  $t = $target_army->getUnits(); if($t[$unit->id]==0) echo " hidden"; ?>"><?php echo $t[$unit->id]; ?></td>
                <?php } ?>
            </tr>
        </tbody>
    </table>

	<br>

	<?php foreach($units as $unit) { ?>
	<input type="hidden" name="<?php echo $unit->id; ?>" value="<?php $t = $target_army->getUnits(); echo $t[$unit->id]; ?>">
	<?php } ?>
	<script type="text/javascript">
	//<![CDATA[

		$(document).ready(function() {
			$('#troop_confirm_go').focus();
		});

	//]]>
	</script>
    <?php if($isAttack) { ?>
	<input id="troop_confirm_go" class="btn btn-attack" name="submit" type="submit" onload="this.disabled=false;" value="<?php l('buildingPlace.attack'); ?>">
    <?php } else { ?>
    <input id="troop_confirm_go" class="btn btn-support" name="submit" type="submit" onload="this.disabled=false;" value="<?php l('buildingPlace.support'); ?>">
    <?php } ?>
</form>