<?php
namespace TWLan;
use TWLan\framework\Text;
use TWLan\Model\World\Ally\Permission;
?>
<form id="form_rights" method="post"
	action="game.php?village=<?php echo $vid; ?>&amp;screen=ally&amp;mode=members&amp;action=mod">

	<table class="vis">
		<tbody>
			<tr>
				<th width="280" class="nowrap"><a
					href="game.php?village=<?php echo $vid; ?>&amp;screen=ally&amp;mode=members&amp;order=name&amp;dir=1"><?php l('game.name'); ?></a></th>
				<th width="40" class="nowrap"><a
					href="game.php?village=<?php echo $vid; ?>&amp;screen=ally&amp;mode=members&amp;order=rank&amp;dir=1"><?php l('game.rank'); ?></a></th>
				<th width="80" class="nowrap"><a
					href="game.php?village=<?php echo $vid; ?>&amp;screen=ally&amp;mode=members&amp;order=points&amp;dir=1"><?php l('game.points'); ?></a></th>
				<th width="60" class="nowrap"><a
					href="game.php?village=<?php echo $vid; ?>&amp;screen=ally&amp;mode=members&amp;order=rank&amp;dir=1"><?php l('game.globalRank'); ?></a></th>
				<th width="40" class="nowrap"><a
					href="game.php?village=<?php echo $vid; ?>&amp;screen=ally&amp;mode=members&amp;order=villages&amp;dir=1"><?php l('game.villages'); ?></a></th>
				
				<th><a
					href="game.php?village=<?php echo $vid; ?>&amp;screen=ally&amp;mode=members&amp;order=found&amp;dir=1"
					class="nowrap"><span title="<?php l('game.ally.members.founder'); ?>" class="icon ally found"></span></a></th>
				<th><a
					href="game.php?village=<?php echo $vid; ?>&amp;screen=ally&amp;mode=members&amp;order=lead&amp;dir=1"
					class="nowrap"><span title="<?php l('game.ally.members.lead'); ?>" class="icon ally lead"></span></a></th>
				<th><a
					href="game.php?village=<?php echo $vid; ?>&amp;screen=ally&amp;mode=members&amp;order=invite&amp;dir=1"
					class="nowrap"><span title="<?php l('game.ally.members.invite'); ?>" class="icon ally invite"></span></a></th>
				<th><a
					href="game.php?village=<?php echo $vid; ?>&amp;screen=ally&amp;mode=members&amp;order=diplomacy&amp;dir=1"
					class="nowrap"><span title="<?php l('game.ally.members.diplomacy'); ?>" class="icon ally diplomacy"></span></a></th>
				<th><a
					href="game.php?village=<?php echo $vid; ?>&amp;screen=ally&amp;mode=members&amp;order=mass_mail&amp;dir=1"
					class="nowrap"><span title="<?php l('game.ally.members.massMail'); ?>"
						class="icon ally mass_mail"></span></a></th>
				<th><a
					href="game.php?village=<?php echo $vid; ?>&amp;screen=ally&amp;mode=members&amp;order=forum_mod&amp;dir=1"
					class="nowrap"><span title="<?php l('game.ally.members.forumMod'); ?>" class="icon ally forum_mod"></span>
					</a>
				</th>
				<th><a
					href="game.php?village=<?php echo $vid; ?>&amp;screen=ally&amp;mode=members&amp;order=internal_forum&amp;dir=1"
					class="nowrap"><span title="<?php l('game.ally.members.internalForum'); ?>"
						class="icon ally internal_forum"></span></a></th>
				<th><a
					href="game.php?village=<?php echo $vid; ?>&amp;screen=ally&amp;mode=members&amp;order=trusted_member&amp;dir=1"
					class="nowrap"><span title="<?php l('game.ally.members.trustedMember'); ?>"
						class="icon ally trusted_member"></span></a></th>
			</tr>

			<?php $selected = TRUE; foreach($tribe->getMembers() as $member) { ?>
			<tr class="row_a <?php if($selected) { ?>selected <?php } $selected = !$selected; ?>">

				<td class="lit-item"><input type="hidden" value="<?php echo $member->id_user; ?>"
					name="player_id[<?php echo $member->id_user; ?>][id]"> <input type="radio"
					class="show_toggle" value="<?php echo $member->id_user; ?>" name="player"> <img class=""
					alt="" title=""
					src="graphic/stat/green.png"> 
					<a href="game.php?village=<?php echo $vid;?>&amp;screen=info_player&amp;id=<?php echo $member->id_user; ?>"><?php echo $member->getName(); ?></a>
					    <?php if(strlen($tribe->getTitle($member)) > 0) { ?>(<?php echo Text::formatAll($tribe->getTitle($member)); ?>)
					<?php } ?>
				</td>
				<td class="lit-item"><?php echo Text::formatInt($member->getRank(), '<span class="grey">.</span>'); ?></td>
				<td class="lit-item"><?php echo $member->getPoints(ll('game.decimalDelimeter')); ?></td>
				<td class="lit-item"><?php echo Text::formatInt($tribe->getRankOfUser($member), '<span class="grey">.</span>'); ?>
				</td>
				<td class="lit-item"><?php echo $member->cached_villages; ?></td>

				<td class="lit-item">
					<input type="checkbox" class="hide_toggle" 
					    <?php $image = 'green'; if($tribe->getPermissions($member)[0]->hasRole(Permission::role("FOUND"))) { ?>
					        checked="checked" 
					    <?php $founder = TRUE; } else { $founder = FALSE; $image = 'grey'; } ?>
						onclick="set_found_right(<?php echo $member->id_user; ?>)" id="player_id[<?php echo $member->id_user; ?>][found]"
						name="player_id[<?php echo $member->id_user; ?>][found]"
					>
					<div class="show_toggle">
						<img alt="<?php l('game.yes'); ?>" src="graphic/dots/<?php echo $image; ?>.png">
					</div> 
				</td>
				
				<td class="lit-item">
					<input type="checkbox" class="hide_toggle" 
					    <?php $image = 'green'; if($tribe->getPermissions($member)[0]->hasRole(Permission::role("LEAD"))) { ?>
					        checked="checked" 
					    <?php $leader = TRUE; } else { $leader = FALSE; $image = 'grey'; } if($founder) { $image = 'green'; ?>
					        checked="checked"
					        disabled="disabled"
					    <?php } ?>
						onclick="set_lead_right(<?php echo $member->id_user; ?>)"
						id="player_id[<?php echo $member->id_user; ?>][lead]" 
						name="player_id[<?php echo $member->id_user; ?>][lead]"
					>
					<div class="show_toggle">
						<img alt="<?php l('game.yes'); ?>" src="graphic/dots/<?php echo $image; ?>.png">
					</div> 
				</td>

				<?php foreach(Permission::$roles as $role) { if($role == "FOUND" || $role == "LEAD") continue; ?>
				<td class="lit-item">
				   
					<input type="checkbox" class="hide_toggle"
					    <?php $image = 'green'; if($tribe->getPermissions($member)[0]->hasRole(Permission::role($role))) { ?>
					        checked="checked"
					    <?php } else $image = 'grey'; if($leader || $founder) { $image = 'green'; ?>
					        checked="checked"
					        disabled="disabled" 
					    <?php } ?>
					    id="player_id[<?php echo $member->id_user; ?>][<?php echo strtolower($role); ?>]"
					    name="player_id[<?php echo $member->id_user; ?>][<?php echo strtolower($role); ?>]"
					>
					<div class="show_toggle">
						<img alt="<?php l('game.yes'); ?>" src="graphic/dots/<?php echo $image; ?>.png">
					</div> 
				</td>
				<?php } ?>
			</tr>
			<?php } ?>
			<tr>
			    <?php if($tribe->hasPermission($user, 'manage_members')) { ?>
				<td class="no_bg">
				    <div class="show_toggle">
						<select name="ally_action">
						    <option value=""><?php l('game.ally.members.chooseAction'); ?></option>
							<option value="rights"><?php l('game.ally.members.rights'); ?></option>
							<option value="kick"><?php l('game.ally.members.kick'); ?></option>
						</select>
						<input class="btn" type="submit" value="<?php l('game.ok'); ?>">
					</div>
					<input type="submit" class="hide_toggle btn" value="<?php l('game.ally.members.saveRights'); ?>">
					<a class="hide_toggle btn" href="game.php?village=<?php echo $vid; ?>&amp;screen=ally&amp;mode=members"
                        onclick="toggle_visibility_by_class('hide_toggle','none'); toggle_visibility_by_class('show_toggle', ''); return false;"
                    ><?php l('game.cancel'); ?></a>
				</td>
				<td class="no_bg align_right" colspan="11"><a class="show_toggle btn"
					onclick="toggle_visibility_by_class('hide_toggle','inline'); toggle_visibility_by_class('show_toggle'); toggle_form_action('form_rights', 'game.php?village=<?php echo $vid; ?>&amp;screen=ally&amp;mode=members&amp;action=edit_rights');"
					href="#">» <?php l('game.ally.members.editRights'); ?></a></td>
			    <?php } ?>
			</tr>
		</tbody>
	</table>
</form>

<br>

<table class="vis">
	<tbody>
		<tr>
			<th><?php l('game.ally.members.status'); ?></th>
		</tr>
		<tr>
			<td><img alt=""
				src="graphic/stat/green.png"><?php l('game.ally.members.active'); ?></td>
		</tr>
		<tr>
			<td><img alt=""
				src="graphic/stat/birthday.png"><?php l('game.birthday'); ?></td>
		</tr>
		<tr>
			<td><img alt=""
				src="graphic/stat/banned.png"><?php l('game.banned'); ?>
			</td>
		</tr>
	</tbody>
</table>

<script type="text/javascript">
//&lt;![CDATA[
function set_found_right(memberid) {
  check_and_disable('#player_id\\['+memberid+'\\]\\[lead\\]', $('#player_id\\['+memberid+'\\]\\[found\\]').is(':checked'));
  set_lead_right(memberid);
}
function set_lead_right(memberid) {
  var checked = $('#player_id\\['+memberid+'\\]\\[lead\\]').is(':checked');
  check_and_disable('#player_id\\['+memberid+'\\]\\[invite\\]', checked);
  check_and_disable('#player_id\\['+memberid+'\\]\\[diplomacy\\]', checked);
  check_and_disable('#player_id\\['+memberid+'\\]\\[mass_mail\\]', checked);
  check_and_disable('#player_id\\['+memberid+'\\]\\[forum_mod\\]', checked);
  check_and_disable('#player_id\\['+memberid+'\\]\\[internal_forum\\]', checked);
  check_and_disable('#player_id\\['+memberid+'\\]\\[trusted_member\\]', checked);
}

function check_and_disable(name, check) {
  $(name).attr('disabled', check);
  if(check == true) {
    $(name).attr('checked', check);
  }
}

function toggle_form_action(name, action) {
	$('#' + name).attr('action', action);
}
//]]&gt;
</script>