<div id="ally_content">
	<table cellspacing="0">
		<tbody>
			<tr>
				<td valign="top">

					<form method="post"
						action="game.php?village=<?php echo $vid; ?>&amp;screen=ally&amp;mode=properties&amp;action=change">
						<table width="100%" class="vis">
							<tbody>
								<tr>
									<th colspan="2"><?php l('game.ally.profile.properties'); ?></th>
								</tr>
								<tr>
									<td><?php l('game.ally.nameOfTribe'); ?></td>
									<td><input type="text" value="<?php echo $tribe->name; ?>" name="name"></td>
								</tr>
								<tr>
									<td width="140"><?php l('game.ally.expression'); ?>(<?php l('game.ally.max', array('x' => 6)); ?>):</td>
									<td><input type="text" value="<?php echo $tribe->tag; ?>" maxlength="6" name="tag"></td>
								</tr>
								<tr>
									<td width="140"><?php l('game.ally.profile.startpage'); ?>:</td>
									<td><input type="text" value="<?php echo $tribe->homepage; ?>" size="50" maxlength="128"
										name="homepage"></td>
								</tr>
								<tr>
									<td width="140"><?php l('game.ally.profile.irc'); ?>:</td>
									<td><input type="text" value="<?php echo $tribe->irc; ?>" size="50" maxlength="128"
										name="irc-channel"></td>
								</tr>
								<tr>
									<td colspan="2"><input class="btn" type="submit" value="<?php l('game.save'); ?>"></td>
								</tr>
							</tbody>
						</table>
					</form>

					<form method="post"
						action="game.php?village=<?php echo $vid; ?>&amp;screen=ally&amp;mode=properties&amp;action=change_recruitment">
						<table width="100%" class="vis">
							<tbody>
								<tr>
									<th colspan="2"><?php l('game.ally.navi.invite'); ?></th>
								</tr>

								<tr>
									<td width="140"><?php l('game.ally.application'); ?></td>
									<td><input type="checkbox" <?php if($tribe->allowapply) { ?>checked="checked" <?php } ?> id="applications_enabled"
										name="applications_enabled" value="<?php echo $tribe->allowapply; ?>"> <label
										for="applications_enabled"><?php l('game.ally.mayApply'); ?></label></td>
								</tr>
								<tr>
									<td width="140" valign="top"><?php l('game.ally.applyTemplate'); ?>:</td>
									<td>
										<textarea name="application_template" cols="40" rows="5"><?php 
											if(isset($tribe->recruitpattern)) echo $tribe->recruitpattern; 
										?>
										</textarea>
									</td>
								</tr>
								<tr>
									<td colspan="2"><input class="btn" type="submit" value="<?php l('game.save'); ?>"></td>
								</tr>
							</tbody>
						</table>
					</form>
					<table width="100%" class="vis">
						<tbody>
							<tr>
								<th><?php l('game.ally.disband'); ?></th>
							</tr>
							<tr>
								<td><a
									data-confirm-msg="<?php l('game.ally.disbandConfirm'); ?>"
									class="evt-confirm btn"
									href="game.php?village=<?php echo $vid; ?>&amp;screen=ally&amp;mode=properties&amp;action=close&amp"><?php l('game.ally.disband'); ?></a></td>
							</tr>
						</tbody>
					</table>

				</td>
				<td width="360" valign="top"><script type="text/javascript">
//&lt;![CDATA[
	function bbEdit() {
		$('#tribe_announcement_edit').toggle();
		$('#tribe_announcement_edit_link').toggle();
		$('#tribe_announcement_show').toggle();

		BBCodes.placePopups();	
	}
//]]&gt;
</script>
					<form name="edit_profile" method="post"
						action="game.php?village=<?php echo $vid; ?>&amp;screen=ally&amp;mode=properties&amp;action=change_desc">
						<table width="100%" class="vis">

							<tbody style="display: none;" id="tribe_announcement_edit">
								<tr>
									<th width="100%" colspan="2"><?php l('game.ally.description'); ?></th>
								</tr>
								<tr align="center" id="bb_row">
								</tr>
								<tr id="edit_row">
									<td colspan="2">
									    <textarea rows="15"
											style="width: 100%; height: 150px;" name="desc_text"
											id="desc_text" class="ie8scrollfix"><?php echo $tribe->getDescription(FALSE); ?></textarea>
									</td>
								</tr>
								<tr id="submit_row">
									<td><input type="submit" value="<?php l('game.save'); ?>" name="edit"> <input
										type="submit" value="<?php l('game.preview'); ?>" name="preview"></td>
									<td align="right"><a target="_blank"
										href="http://help.die-staemme.de/wiki/BB-Codes">BB-Codes</a></td>
								</tr>
							</tbody>

							<tbody id="tribe_announcement_show">
								<tr>
									<th width="100%" colspan="2"><?php l('game.ally.description'); ?></th>
								</tr>
								<tr align="center" id="show_row">
									<td><?php echo $tribe->getDescription(TRUE); ?></td>
								</tr>
							</tbody>
						</table>
					</form> <a class="btn" onclick="javascript:bbEdit(); return false;" href="#"
					id="tribe_announcement_edit_link"><?php l('game.edit'); ?></a><br> <script
						type="text/javascript">
//&lt;![CDATA[
	//]]&gt;
</script><br>
			        <form action="/game.php?village=<?php echo $vid; ?>&amp;mode=properties&amp;action=change_image&amp;screen=ally" enctype="multipart/form-data" method="post">
                        <table class="vis">
                        <tbody><tr><th><?php l('game.ally.coatOfArms'); ?>:</th></tr>
                            <tr>
                                <td>
                                    <input name="image" type="file" size="40" accept="image/*" maxlength="1048576"><br>
                                    <span class="small">max. 300x200, max. 256kByte, (jpg, jpeg, png, gif)</span><br>
                                    <input type="submit" class="btn" value="<?php l('game.ok'); ?>">
                                </td>
                            </tr>
                        </tbody>
                        </table>
                    </form>
                </td>
			</tr>
		</tbody>
	</table>
</div>