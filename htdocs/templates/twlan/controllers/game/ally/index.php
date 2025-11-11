<?php
namespace Twlan;
?>
<h2><?php l('game.ally.tribe'); ?></h2>
<p><?php l('game.ally.enter'); ?></p>

<table width="100%">
	<tbody>
		<tr>
			<td width="45%" valign="top">

				<table width="100%" class="vis">
					<tbody>
						<tr>
							<th colspan="3"><?php l('game.ally.invitations'); ?></th>
						</tr>
						<?php foreach($user->getInvitations() as $invitation) { ?>
						<tr>
							<td><a
								href="game.php?village=<?php echo $vid; ?>&amp;screen=info_ally&amp;id=<?php echo $invitation->id_ally; ?>"><?php echo $invitation->ally->name; ?></a></td>
							<td align="center"><a
								href="game.php?village=<?php echo $vid; ?>&amp;screen=ally&amp;action=accept&amp;id=<?php echo $invitation->id_ally; ?>"><?php l('game.ally.accept'); ?></a></td>
							<td align="center"><a
								href="game.php?village=<?php echo $vid; ?>&amp;screen=ally&amp;action=reject&amp;id=<?php echo $invitation->id_ally; ?>"><?php l('game.ally.reject'); ?></a></td>
						</tr>
						<?php } ?>
					</tbody>
				</table> <br>
				<form method="post"
					action="game.php?village=<?php echo $vid; ?>&amp;screen=ally&amp;action=create">
					<table width="100%" class="vis">
						<tbody>
							<tr>
							    <?php if(isset($error)) { ?> <font color="red"><?php echo $error; ?></font><?php } ?>
								<th colspan="2"><?php l('game.ally.doFound'); ?></th>
							</tr>
							<tr>
								<td><?php l('game.ally.nameOfTribe'); ?>:</td>
								<td><input type="text" name="name"></td>
							</tr>
							<tr>
								<td><?php l('game.ally.expression'); ?>:<br>(<?php l('game.ally.max', array('max' => 6)); ?>)
								</td>
								<td><input type="text" maxlength="6" name="tag"></td>
							</tr>
							<tr>
								<td colspan="2"><input type="submit" style="font-size: 10pt;"
									value="<?php l('game.ally.doFound'); ?>"></td>
							</tr>
						</tbody>
					</table>
				</form>

			</td>
			<td></td>
		</tr>
	</tbody>
</table>
