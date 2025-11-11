<?php
namespace TWLan;
?>
<div id="ally_content">
	<table width="100%">
		<tbody>
			<tr>
				<td width="45%" valign="top">
					<table width="400" class="vis">
						<tbody>
							<tr>
								<th colspan="3"><?php l('game.ally.invite.invitations'); ?></th>
							</tr>
							<?php foreach($tribe->getInvitations() as $invite) { ?>
							<tr>
							    <td><a href="game.php?village=<?php echo $vid;?>&amp;screen=info_player&amp;id=<?php echo $invite->getUser()->get('id_user'); ?>"><?php echo $invite->getUser()->get('name'); ?></a></td>
							    <td><?php echo BaseTools::onTime($invite->get('ins_date')); ?></td>
							    <td>
                                    <a class="btn" href="game.php?village=<?php echo $vid; ?>&amp;screen=ally&amp;action=cancel_invitation&amp;id=<?php echo $invite->getUser()->get('id_user'); ?>">
                                        <?php echo Yii::app()->lang->get('game.ally.invite.redraw'); ?>
                                    </a>
                                </td>
							</tr>
							<?php } ?>
						</tbody>
					</table> <br>
					<form method="post"
						action="game.php?village=<?php echo $vid; ?>&amp;screen=ally&amp;mode=invite&amp;action=invite">
						<table width="400" class="vis">
							<tbody>
								<tr>
									<th colspan="3"><?php l('game.ally.invite.invite'); ?></th>
								</tr>
								<tr>
									<td><?php l('game.ally.invite.name'); ?>:</td>
									<td><input type="text" value="" name="name" class="input-text"></td>
									<td><input class="btn" type="submit" value="<?php l('game.ok'); ?>"></td>
								</tr>
							</tbody>
						</table>
					</form>
				</td>
				<td width="55%" valign="top"></td>
			</tr>
		</tbody>
	</table>
</div>