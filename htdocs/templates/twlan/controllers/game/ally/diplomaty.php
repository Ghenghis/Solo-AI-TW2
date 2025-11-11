<?php 
namespace TWLan;
use TWLan\Model\World\Ally\Contract;
?>
<div id="ally_content">

	<p>
		<?php l('game.ally.diplomaty.head'); ?>
	</p>

	<table width="100%" class="vis" id="partners">
		<tbody>
		    <?php foreach(Contract::$types as $type => $_) { ?>
			<tr>
				<th colspan="2"><?php l('game.ally.diplomaty.'.$type); ?></th>
			</tr>
			<?php if(isset($contracts[$type])) { foreach($contracts[$type] as $i) { ?>
			<tr>
				<td>
				    <a href="game.php?village=<?php echo $vid; ?>&amp;screen=info_ally&amp;id=<?php echo $i->getDestination()->get('id_ally'); ?>"><?php echo $i->getDestination()->get('tag'); ?></a>
				</td>
				<td>
				    <a class="btn" href="game.php?village=<?php echo $vid; ?>&amp;screen=ally&amp;mode=contracts&amp;action=cancel_contract&amp;id=<?php echo $i->getDestination()->get('id_ally'); ?>">
				        <?php echo Yii::app()->lang->get('game.ally.diplomaty.end'); ?>
				    </a>
				</td>
			</tr>
			<?php } } ?>
			<tr>
				<td style="height: 12px; background: none;" colspan="2"></td>
			</tr>
			<?php } ?>
		</tbody>
	</table>

	<br style="clear: both;">

	<h3><?php l('game.ally.diplomaty.new'); ?></h3>
	<form method="post"
		action="game.php?village=<?php echo $vid; ?>&amp;screen=ally&amp;mode=contracts&amp;action=add_contract">
		<label for="tag"><?php l('game.ally.diplomaty.tag'); ?>:</label>
		<input type="text" maxlength="30" style="width: 60px" name="tag"> <select
			name="type">
			<?php foreach(Contract::$types as $type => $_) { ?>
			<option value="<?php echo $type; ?>"><?php l('game.ally.diplomaty.'.$type); ?></option>
			<?php } ?>
		</select>
		<button class="btn"><?php l('game.ok'); ?></button>
	</form>
</div>