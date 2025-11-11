<?php
namespace Twlan;
?>
<div id="ally_content">
	<table width="100%">
		<tbody>
			<tr>
				<td width="*" valign="top">
				    <?php if($sites > 1) { ?>
					<table width="100%" class="vis">
						<tbody>
							<tr>
								<td align="center">
								<?php for($c = 1; $c <= $sites; $c++) {
								    if($site == $c)
								    {
								    ?>
								       <strong>&gt;<?php echo $c; ?>&lt;</strong> 
								    <?php } else { ?>
								        <a href="game.php?village=<?php echo $vid; ?>&amp;screen=ally&amp;mode=overview&amp;start=<?php echo ($c * 10) - 10; ?>">
								        [<?php echo $c; ?>]
								        </a>
								    <?php } ?> 
								<?php } ?>
								</td>
							</tr>
						</tbody>
					</table>
					<?php } ?>
					<table width="100%" class="vis">
						<tbody>
							<tr>
								<th><?php l('game.ally.date'); ?></th>
								<th><?php l('game.ally.event'); ?></th>
							</tr>

                            <?php foreach($items as $item) { ?>
                            <tr>
                                <td width="80"><?php echo date('d.m', $item->time_ins); ?><br><?php echo date('H:i', $item->time_ins); ?>
                                </td>
                                <td><?php echo \Twlan\Model\World\Ally\Event::message($item, $vid); ?></td>
                            </tr>
                            <?php } ?>
						</tbody>
					</table>

				</td>
				<td width="370" valign="top">
					<table width="100%" class="vis">
						<tbody>
							<tr>
								<td><a class="evt-confirm btn"
								    data-confirm-msg="<?php l('game.ally.confirmLeave'); ?>"
									href="game.php?village=<?php echo $vid; ?>&amp;screen=ally&amp;action=exit&amp"><?php l('game.ally.leave'); ?></a></td>
							</tr>
						</tbody>
					</table> <script type="text/javascript">
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
						action="game.php?village=<?php echo $vid; ?>&amp;screen=ally&amp;action=edit_intern&amp">
						<table width="100%" class="vis">

							<tbody style="display: none;" id="tribe_announcement_edit">
								<tr>
									<th width="100%" colspan="2"><?php l('game.ally.announcement'); ?></th>
								</tr>
								<tr align="center" id="bb_row">
									<td colspan="2">
										<?php $this->viewPartial("bbcode"); ?>
									</td>
								</tr>
								<tr id="edit_row">
									<td colspan="2">
										<textarea rows="15"
											style="width: 100%; height: 150px;" name="message"
											id="message" class="ie8scrollfix"
											><?php echo $tribe->getAnnouncement(false); ?>
										</textarea>
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
									<th width="100%" colspan="2"><?php l('game.ally.announcement'); ?></th>
								</tr>
								<tr align="center" id="show_row">
									<td colspan="2"><?php 
										echo $tribe->getAnnouncement(true); 
									?>
								    </td>
								</tr>
							</tbody>
						</table>
					</form> <?php if($tribe->hasPermission($this->user, 'announcement')) { ?><a class="btn" onclick="javascript:bbEdit(); return false;" href="#"
					id="tribe_announcement_edit_link"><?php l('game.edit'); ?></a><br> <?php } ?>
				</td>
			</tr>
		</tbody>
	</table>
</div>