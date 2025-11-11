<?php
namespace TWLan;
use \TWLan\Model\World\Ally\Permission;
?>
<div id="ally_content">
	<h3><?php l('game.ally.members.modifyPermissions', array('player' => $player->getName())); ?></h3>
	<p><?php l('game.ally.members.permissionDesc'); ?></p>
	<form method="post"
		action="game.php?village=<?php echo $vid; ?>&amp;screen=ally&amp;mode=rights&amp;action=edit_rights&amp;player=<?php echo $player->id_user; ?>">

		<label><h5>
				<input type="checkbox" 
				    <?php if($tribe->hasRole($player, Permission::role("FOUND"))) { ?>
				        checked="checked" 
				    <?php $founder = TRUE; } else { $founder = FALSE; } ?>
				    onclick="set_found_right()"
					id="player[found]" name="player[found]"> <span
					title="<?php l('game.ally.members.founder'); ?>" class="icon ally founder"></span>
				<?php l('game.ally.members.founder'); ?>
			</h5></label>
		<p><?php l('game.ally.members.founderDesc'); ?></p>

		<label><h5>
				<input type="checkbox" 
				    <?php if($tribe->hasRole($player, Permission::role("LEAD"))) { ?>
				        checked="checked" 
				    <?php $leader = TRUE; } else { $leader = FALSE; } if($founder) { ?>
				        checked="checked"
				        disabled="disabled"
				    <?php } ?>
					onclick="set_lead_right()" id="player[lead]" name="player[lead]"> <span
					title="<?php l('game.ally.members.lead'); ?>" class="icon ally lead"></span>
				<?php l('game.ally.members.lead'); ?>
			</h5></label>
		<p><?php l('game.ally.members.leadDesc'); ?></p>

		<?php foreach(Permission::$roles as $role) { 
			if ($role == "FOUND" || $role == "LEAD") continue;
		?>
		<label><h5>
				<input type="checkbox" 
				    <?php if($tribe->hasRole($player, Permission::role($role))) { ?>
				        checked="checked" 
				    <?php } if($founder || $leader) { ?>
				        checked="checked"
				        disabled="disabled"
				    <?php } ?>
					id="player[<?php echo $role; ?>]" name="player[<?php echo $role; ?>]"> 
					<span title="<?php l('game.ally.members.'.strtolower($role)); ?>" class="icon ally <?php echo strtolower($role); ?>"></span>
					<?php l('game.ally.members.'.strtolower($role)); ?>
			</h5></label>
		<p><?php l('game.ally.members.'.strtolower($role).'Desc'); ?></p>
		<?php } ?>

		<h3><?php l('game.ally.members.title'); ?></h3>
		<p>
			<?php l('game.ally.members.titleInally'); ?>: 
			<input type="text" value="<?php echo $tribe->getTitle($player); ?>" maxlength="24" name="player[title]">
		</p>
		<label><h5>
				<input type="checkbox" <?php if($tribe->getPermissions($player)[0]->title_outside) { ?> checked="checked" <?php } ?>
				    id="player[external_title]"
					name="player[external_title]"><?php l('game.ally.members.externalTitle'); ?>
			</h5></label>
		<p>
			<input class="btn" type="submit" value="<?php l('game.ok'); ?>">
		</p>
	</form>

	<script type="text/javascript">
//&lt;![CDATA[
	/**
	 * @jQuery
	 */
	function set_found_right() {
	  check_and_disable('#player\\[lead\\]', $('#player\\[found\\]').is(':checked'));
	  set_lead_right();
	}

	/**
	 * @jQuery
	 */
	function set_lead_right() {
	  var checked = $('#player\\[lead\\]').is(':checked');
	  check_and_disable('#player\\[invite\\]', checked);
	  check_and_disable('#player\\[diplomacy\\]', checked);
	  check_and_disable('#player\\[mass_mail\\]', checked);
	  check_and_disable('#player\\[forum_mod\\]', checked);
	  check_and_disable('#player\\[internal_forum\\]', checked);
	  check_and_disable('#player\\[trusted_member\\]', checked);
	}

	/**
	 * @jQuery
	 */
	function check_and_disable(name, check) {
	  $(name).attr('disabled', check);
	  if(check == true) {
		$(name).attr('checked', check);
	  }
	}

	/**
	 * @jQuery
	 */
	function toggle_form_action(name, action) {
		$('#' + name).action = action;
	}
//]]&gt;
</script>
</div>