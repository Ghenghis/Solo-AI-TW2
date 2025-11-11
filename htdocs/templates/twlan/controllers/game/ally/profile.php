<?php
namespace TWLan;
use TWLan\framework\Text;
?>
<div id="ally_content">
	<table>
		<tbody>
			<tr>
				<td valign="top">
					<table width="100%" class="vis">
						<tbody>
							<tr>
								<th colspan="2"><?php l('game.ally.profile.properties'); ?></th>
							</tr>
							<tr>
								<td width="100"><?php l('game.ally.nameOfTribe'); ?></td>
								<td><?php echo Text::formatAll($tribe->name); ?></td>
							</tr>
							<tr>
								<td><?php l('game.ally.expression'); ?></td>
								<td><?php echo $tribe->tag; ?></td>
							</tr>
							<tr>
								<td><?php l('game.ally.profile.memberCount'); ?></td>
								<td><?php echo $tribe->countMembers();?></td>
							</tr>
							<tr>
								<td><?php l('game.ally.profile.pointsBest40'); /* Irrelevant for TWLan, just count all points */ ?></td>
								<td><?php
								    /*$points = 0;

								    foreach(Yii::app()->db->createCommand()
                                        ->select('id_user')
								        ->from($user->getWorld().User::$tableSuffix)
								        ->where('id_ally=:ally', array(':ally' => $tribe->get('id_ally')))
								        ->order('points DESC')
                                        ->limit(40)
                                        ->queryAll()
                                        as $i)
                                    {
                                        $points += User::loadById($i['id_user'], $user->getWorld())->getPoints();
                                    }
                                    echo BaseTools::formatInt($points, Yii::app()->lang->get('game.delimeter'));
                                    */
								echo $tribe->getPointsTop40(ll('game.decimalDelimeter')); ?></td>
							</tr>
							<tr>
								<td><?php l('game.ally.profile.pointsOverall'); ?>:</td>
								<td><?php echo $tribe->getPoints(ll('game.decimalDelimeter')); ?></td>
							</tr>
							<tr>
								<td><?php l('game.ally.profile.pointsAverage'); ?>:</td>
								<td><?php echo $tribe->getPointsPerUser(ll('game.decimalDelimeter')); ?></td>
							</tr>
							<tr>
								<td><?php l('game.rank'); ?></td>
								<td><?php echo $tribe->getRank(); ?></td>
							</tr>
							<tr>
								<td><?php l('game.ally.profile.defeatedEnemies'); ?>:</td>
								<td class="tooltip" id="kill_info"><?php echo $tribe->getKills(); ?></td>
							</tr>
							<tr>
								<td align="center" colspan="2">
									<a href="game.php?village=<?php echo $vid;?>&amp;screen=info_member&amp;id=<?php echo $tribe->id_ally; ?>">
									<?php l('game.ally.profile.members'); ?>
									</a>
								</td>
							</tr>

							<tr>
								<td align="center" class="no_bg" colspan="2"><br>
								<hr></td>
							</tr>
						</tbody>
					</table>

				</td>
				<td valign="top">

					<table width="300" class="vis">
						<tbody>
							<tr>
								<th><?php l('game.ally.description'); ?></th>
							</tr>
							<tr>
    							 <td align="center"><?php echo $tribe->getDescription(true); ?></td>
							</tr>
						</tbody>
					</table>

				</td>
			</tr>
		</tbody>
	</table>
</div>
